<?php

namespace App\Services\Fiscal;

use Illuminate\Support\Carbon;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use RuntimeException;

/**
 * Monta, assina e valida o XML de uma NFC-e (modelo 65) simulada.
 *
 * O XML segue o layout oficial 4.00 e é validado contra o XSD, mas nunca é
 * transmitido para a SEFAZ: não tem protocolo de autorização nem validade fiscal.
 */
class NfceXmlBuilder
{
    /**
     * Texto que a SEFAZ exige na descrição do primeiro item em homologação.
     */
    public const HOMOLOGATION_ITEM = 'NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL';

    private const MODEL = 65;

    private const STATE_CODES = ['SP' => 35];

    private const TIMEZONE = 'America/Sao_Paulo';

    public function __construct(
        private readonly array $config,
        private readonly TestCertificate $certificate,
    ) {}

    /**
     * @param  array{
     *     number: int,
     *     issued_at: \DateTimeInterface,
     *     code?: string,
     *     consumer_cpf?: string|null,
     *     items: list<array{code: string, description: string, ncm: string, cfop: string, csosn: string, cest?: string|null, unit: string, quantity: float|int, unit_price: float}>,
     *     payments: list<array{method: string, amount: float}>,
     *     change?: float,
     * }  $invoice
     */
    public function build(array $invoice): string
    {
        $make = new Make;
        $emitter = $this->config['emitter'];

        $make->taginfNFe((object) ['versao' => '4.00']);
        $make->tagide((object) [
            'cUF' => $this->stateCode(),
            // Sem código informado, a biblioteca sorteia um válido.
            'cNF' => $invoice['code'] ?? null,
            'natOp' => 'VENDA',
            'mod' => self::MODEL,
            'serie' => $this->config['series'],
            'nNF' => $invoice['number'],
            'dhEmi' => Carbon::instance($invoice['issued_at'])->setTimezone(self::TIMEZONE)->format('Y-m-d\TH:i:sP'),
            'tpNF' => 1,
            'idDest' => 1,
            'cMunFG' => $emitter['address']['city_code'],
            'tpImp' => 4,
            'tpEmis' => 1,
            'tpAmb' => $this->config['environment'],
            'finNFe' => 1,
            'indFinal' => 1,
            'indPres' => 1,
            'procEmi' => 0,
            'verProc' => 'PI2-GRUPO6-1.0',
        ]);

        $make->tagEmit((object) [
            'CNPJ' => $emitter['cnpj'],
            'xNome' => $emitter['legal_name'],
            'xFant' => $emitter['trade_name'],
            'IE' => $emitter['state_registration'],
            'CRT' => $emitter['tax_regime'],
        ]);
        $make->tagenderEmit((object) [
            'xLgr' => $emitter['address']['street'],
            'nro' => $emitter['address']['number'],
            'xBairro' => $emitter['address']['district'],
            'cMun' => $emitter['address']['city_code'],
            'xMun' => $emitter['address']['city'],
            'UF' => $emitter['address']['state'],
            'CEP' => $emitter['address']['zip_code'],
            'cPais' => '1058',
            'xPais' => 'BRASIL',
            'fone' => $emitter['phone'],
        ]);

        if (! empty($invoice['consumer_cpf'])) {
            $make->tagdest((object) ['CPF' => $invoice['consumer_cpf'], 'indIEDest' => 9]);
        }

        foreach ($invoice['items'] as $index => $item) {
            $this->addItem($make, $index + 1, $item);
        }

        $make->tagICMSTot((object) []);
        $make->tagtransp((object) ['modFrete' => 9]);

        $make->tagpag((object) ['vTroco' => $invoice['change'] ?? 0]);
        foreach ($invoice['payments'] as $payment) {
            $make->tagdetPag((object) ['tPag' => $payment['method'], 'vPag' => $payment['amount']]);
        }

        $make->taginfAdic((object) [
            'infCpl' => 'DOCUMENTO SIMULADO - NAO TRANSMITIDO A SEFAZ - SEM VALOR FISCAL',
        ]);

        $xml = $make->montaNFe();

        if ($make->getErrors() !== []) {
            throw new RuntimeException('NFC-e inválida: '.implode('; ', $make->getErrors()));
        }

        return $this->tools()->signNFe($xml);
    }

    private function addItem(Make $make, int $number, array $item): void
    {
        $isHomologation = $this->config['environment'] === 2;
        $total = round($item['quantity'] * $item['unit_price'], 2);

        $make->tagprod((object) [
            'item' => $number,
            'cProd' => $item['code'],
            'cEAN' => 'SEM GTIN',
            'xProd' => $number === 1 && $isHomologation ? self::HOMOLOGATION_ITEM : $item['description'],
            'NCM' => $item['ncm'],
            'CEST' => $item['cest'] ?? null,
            'CFOP' => $item['cfop'],
            'uCom' => $item['unit'],
            'qCom' => $item['quantity'],
            'vUnCom' => $item['unit_price'],
            'vProd' => $total,
            'cEANTrib' => 'SEM GTIN',
            'uTrib' => $item['unit'],
            'qTrib' => $item['quantity'],
            'vUnTrib' => $item['unit_price'],
            'indTot' => 1,
        ]);

        $make->tagimposto((object) ['item' => $number]);
        $make->tagICMSSN((object) ['item' => $number, 'orig' => 0, 'CSOSN' => $item['csosn']]);

        // CST 49 (outras operações de saída): empresa do Simples não destaca PIS/COFINS na nota.
        $make->tagPIS((object) ['item' => $number, 'CST' => '49', 'vBC' => 0, 'pPIS' => 0, 'vPIS' => 0]);
        $make->tagCOFINS((object) ['item' => $number, 'CST' => '49', 'vBC' => 0, 'pCOFINS' => 0, 'vCOFINS' => 0]);
    }

    private function tools(): Tools
    {
        $emitter = $this->config['emitter'];

        return new Tools(json_encode([
            'atualizacao' => now()->format('Y-m-d H:i:s'),
            'tpAmb' => $this->config['environment'],
            'razaosocial' => $emitter['legal_name'],
            'cnpj' => $emitter['cnpj'],
            'siglaUF' => $emitter['address']['state'],
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'CSC' => $this->config['csc']['token'],
            'CSCid' => $this->config['csc']['id'],
        ]), $this->certificate->load());
    }

    private function stateCode(): int
    {
        $state = $this->config['emitter']['address']['state'];

        return self::STATE_CODES[$state]
            ?? throw new RuntimeException("UF do emitente não suportada: {$state}");
    }
}

<?php

namespace App\Services\Fiscal;

use NFePHP\Common\Certificate;
use RuntimeException;

/**
 * Certificado autoassinado usado para assinar a NFC-e simulada.
 *
 * Não é um e-CNPJ da ICP-Brasil: a SEFAZ recusaria qualquer documento
 * assinado com ele. Existe para que o XML tenha a assinatura no formato
 * oficial e passe na validação do XSD.
 */
class TestCertificate
{
    public function __construct(
        private readonly string $path,
        private readonly string $password,
    ) {}

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function load(): Certificate
    {
        if (! $this->exists()) {
            throw new RuntimeException(
                'Certificado de teste não encontrado. Rode: php artisan fiscal:test-certificate'
            );
        }

        return Certificate::readPfx(file_get_contents($this->path), $this->password);
    }

    public function generate(string $legalName, string $cnpj): void
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $request = openssl_csr_new([
            'countryName' => 'BR',
            'organizationName' => 'SIMULACAO - SEM VALOR FISCAL',
            'commonName' => $legalName.':'.$cnpj,
        ], $key, ['digest_alg' => 'sha256']);

        $certificate = openssl_csr_sign($request, null, $key, 3650, ['digest_alg' => 'sha256']);

        if (! openssl_pkcs12_export($certificate, $pfx, $key, $this->password)) {
            throw new RuntimeException('Não foi possível gerar o certificado de teste.');
        }

        if (! is_dir(dirname($this->path))) {
            mkdir(dirname($this->path), 0775, true);
        }

        file_put_contents($this->path, $pfx);
    }
}

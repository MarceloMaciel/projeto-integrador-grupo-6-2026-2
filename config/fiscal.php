<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NFC-e simulada
    |--------------------------------------------------------------------------
    |
    | O sistema gera a NFC-e (modelo 65) no layout oficial, mas nunca a
    | transmite para a SEFAZ. Por isso o ambiente é sempre homologação
    | (tpAmb = 2) e os documentos não têm validade fiscal.
    |
    */

    'environment' => 2,

    'series' => (int) env('FISCAL_SERIES', 1),

    /*
    |--------------------------------------------------------------------------
    | Emitente
    |--------------------------------------------------------------------------
    |
    | Os valores padrão são de uma empresa fictícia. Os dados reais do
    | restaurante entram apenas pelo .env de cada máquina, nunca pelo git.
    |
    */

    'emitter' => [
        'cnpj' => env('FISCAL_CNPJ', '11222333000181'),
        'state_registration' => env('FISCAL_IE', '123456789012'),
        'legal_name' => env('FISCAL_LEGAL_NAME', 'RESTAURANTE FICTICIO LTDA'),
        'trade_name' => env('FISCAL_TRADE_NAME', 'RESTAURANTE FICTICIO'),
        // 1 = Simples Nacional
        'tax_regime' => (int) env('FISCAL_TAX_REGIME', 1),
        'phone' => env('FISCAL_PHONE'),
        'address' => [
            'street' => env('FISCAL_ADDRESS_STREET', 'RUA DE EXEMPLO'),
            'number' => env('FISCAL_ADDRESS_NUMBER', '100'),
            'district' => env('FISCAL_ADDRESS_DISTRICT', 'CENTRO'),
            'city' => env('FISCAL_ADDRESS_CITY', 'MOGI DAS CRUZES'),
            // Código do município na tabela do IBGE
            'city_code' => env('FISCAL_ADDRESS_CITY_CODE', '3530607'),
            'state' => env('FISCAL_ADDRESS_STATE', 'SP'),
            'zip_code' => env('FISCAL_ADDRESS_ZIP_CODE', '08700000'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSC (Código de Segurança do Contribuinte)
    |--------------------------------------------------------------------------
    |
    | Usado no cálculo do QR Code. Na emissão real é fornecido pela SEFAZ;
    | aqui é fictício, então a consulta do QR Code não encontra a nota.
    |
    */

    'csc' => [
        'id' => env('FISCAL_CSC_ID', '000001'),
        'token' => env('FISCAL_CSC', 'CSC-FICTICIO-DE-SIMULACAO'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificado de teste
    |--------------------------------------------------------------------------
    |
    | Certificado autoassinado, gerado por `php artisan fiscal:test-certificate`.
    | Serve só para assinar o XML simulado; não é o e-CNPJ do restaurante.
    |
    */

    'certificate' => [
        'path' => storage_path('app/private/fiscal/test-certificate.pfx'),
        'password' => env('FISCAL_CERTIFICATE_PASSWORD', 'simulacao'),
    ],

];

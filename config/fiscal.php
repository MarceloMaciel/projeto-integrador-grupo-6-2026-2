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
    | Perfis fiscais dos produtos
    |--------------------------------------------------------------------------
    |
    | Códigos de partida para um restaurante do Simples Nacional em SP, ainda
    | a validar com o contador. O perfil "prepared" é o padrão das colunas de
    | `products`; os demais são as bebidas revendidas.
    |
    | Bebidas com substituição tributária: CFOP 5405 e CSOSN 500, com CEST.
    | O CEST depende da embalagem e vem do Anexo IV (segmento 03) do
    | Convênio ICMS 142/2018. Suco/néctar em lata não consta desse anexo,
    | por isso é revenda comum, sem ST.
    |
    */

    'product_profiles' => [
        'prepared' => ['ncm' => '21069090', 'cfop' => '5101', 'csosn' => '102', 'cest' => null, 'unit' => 'UN'],
        // 03.010.01 Refrigerante em embalagem PET
        'soft_drink_pet' => ['ncm' => '22021000', 'cfop' => '5405', 'csosn' => '500', 'cest' => '0301001', 'unit' => 'UN'],
        // 03.010.02 Refrigerante em lata
        'soft_drink_can' => ['ncm' => '22021000', 'cfop' => '5405', 'csosn' => '500', 'cest' => '0301002', 'unit' => 'UN'],
        // 03.021.00 Cerveja em garrafa de vidro retornável
        'beer_returnable_bottle' => ['ncm' => '22030000', 'cfop' => '5405', 'csosn' => '500', 'cest' => '0302100', 'unit' => 'UN'],
        // 03.021.03 Cerveja em lata
        'beer_can' => ['ncm' => '22030000', 'cfop' => '5405', 'csosn' => '500', 'cest' => '0302103', 'unit' => 'UN'],
        // 03.005.04 Água mineral em demais embalagens descartáveis
        'water_bottle' => ['ncm' => '22011000', 'cfop' => '5405', 'csosn' => '500', 'cest' => '0300504', 'unit' => 'UN'],
        'juice_can' => ['ncm' => '22029900', 'cfop' => '5102', 'csosn' => '102', 'cest' => null, 'unit' => 'UN'],
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

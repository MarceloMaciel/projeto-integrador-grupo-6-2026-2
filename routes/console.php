<?php

use App\Services\Fiscal\TestCertificate;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fiscal:test-certificate {--force : Substitui o certificado existente}', function (TestCertificate $certificate) {
    if ($certificate->exists() && ! $this->option('force')) {
        $this->info('Certificado de teste já existe.');

        return;
    }

    $certificate->generate(config('fiscal.emitter.legal_name'), config('fiscal.emitter.cnpj'));
    $this->info('Certificado de teste gerado (autoassinado, sem valor fiscal).');
})->purpose('Gera o certificado autoassinado usado para assinar a NFC-e simulada');

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->isValid((string) $value)) {
            $fail('Informe um CPF válido.');
        }
    }

    private function isValid(string $cpf): bool
    {
        // Sequências repetidas (111.111.111-11) passam no cálculo, mas não existem.
        if (! preg_match('/^\d{11}$/', $cpf) || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        foreach ([9, 10] as $length) {
            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $cpf[$i] * ($length + 1 - $i);
            }

            if ((int) $cpf[$length] !== ($sum * 10) % 11 % 10) {
                return false;
            }
        }

        return true;
    }
}

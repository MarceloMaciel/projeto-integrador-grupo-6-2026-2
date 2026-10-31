<?php

/*
 * Traduções das regras de validação em uso no sistema.
 * Qualquer regra não listada aqui cai automaticamente no idioma de
 * reserva (APP_FALLBACK_LOCALE), então basta acrescentar as novas
 * regras conforme as telas do sistema forem criadas.
 */

return [
    'array' => 'O campo :attribute é inválido.',
    'boolean' => 'O campo :attribute é inválido.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'current_password' => 'A senha informada está incorreta.',
    'decimal' => 'O campo :attribute deve ter no máximo 2 casas decimais.',
    'email' => 'Informe um e-mail válido.',
    'exists' => 'O valor informado em :attribute não existe.',
    'gt' => [
        'numeric' => 'O campo :attribute deve ser maior que :value.',
    ],
    'in' => 'O valor informado em :attribute é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'lowercase' => 'O campo :attribute deve conter apenas letras minúsculas.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
    ],
    'min' => [
        'array' => 'Informe ao menos :min item em :attribute.',
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
        'numeric' => 'O campo :attribute deve ser no mínimo :min.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'prohibited_unless' => 'O campo :attribute só pode ser preenchido quando :other for :values.',
    'regex' => 'O formato do campo :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'required_if' => 'O campo :attribute é obrigatório quando :other for :value.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este :attribute já está em uso.',

    'attributes' => [
        'email' => 'e-mail',
        'name' => 'nome',
        'password' => 'senha',
        'current_password' => 'senha atual',
    ],
];

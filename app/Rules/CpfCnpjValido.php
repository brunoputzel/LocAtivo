<?php

namespace App\Rules;

use App\Enums\TipoCliente;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfCnpjValido implements ValidationRule
{
    public function __construct(private readonly ?TipoCliente $tipo) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $documento = preg_replace('/\D/', '', (string) $value);

        if (! $this->tipo) {
            return;
        }

        if (strlen($documento) !== $this->tipo->tamanhoDocumento()) {
            $fail("Documento deve ter {$this->tipo->tamanhoDocumento()} dígitos para o tipo {$this->tipo->value}.");

            return;
        }

        $valido = $this->tipo === TipoCliente::PF
            ? $this->cpfValido($documento)
            : $this->cnpjValido($documento);

        if (! $valido) {
            $fail($this->tipo === TipoCliente::PF ? 'CPF inválido.' : 'CNPJ inválido.');
        }
    }

    private function cpfValido(string $cpf): bool
    {
        // sequências tipo 111.111.111-11 batem no dígito verificador mas nunca
        // foram emitidas - a Receita rejeita esses CPFs
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($posicao = 9; $posicao <= 10; $posicao++) {
            $soma = 0;

            for ($i = 0; $i < $posicao; $i++) {
                $soma += (int) $cpf[$i] * (($posicao + 1) - $i);
            }

            $digitoVerificador = ((10 * $soma) % 11) % 10;

            if ((int) $cpf[$posicao] !== $digitoVerificador) {
                return false;
            }
        }

        return true;
    }

    private function cnpjValido(string $cnpj): bool
    {
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $pesosPrimeiroDigito = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $pesosSegundoDigito = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        foreach ([$pesosPrimeiroDigito, $pesosSegundoDigito] as $pesos) {
            $posicao = count($pesos);
            $soma = 0;

            for ($i = 0; $i < $posicao; $i++) {
                $soma += (int) $cnpj[$i] * $pesos[$i];
            }

            $resto = $soma % 11;
            $digitoVerificador = $resto < 2 ? 0 : 11 - $resto;

            if ((int) $cnpj[$posicao] !== $digitoVerificador) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace App\Traits;

trait ErrorHandlerTrait
{
    /**
     * Trata exceções e retorna um array padronizado de erro
     */
    protected function handleError(\Throwable $e): array
    {
        return [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
        ];
    }
}
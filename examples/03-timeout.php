<?php

/**
 * TIMEOUT E TIMEOUTS ANINHADOS
 * 
 * Demonstra como limitar o tempo de execução de operações.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Kernel\TimeoutException;
use Recoil\Support\ReactKernel;

function slowOperation($seconds, $name)
{
    echo "[$name] Operação lenta iniciada (vai demorar $seconds segundos)...\n";
    
    for ($i = 0; $i < $seconds * 10; $i++) {
        yield;
        usleep(100000); // 100ms
    }
    
    echo "[$name] Operação concluída!\n";
    return "Resultado de $name";
}

function operationWithTimeout($timeout, $duration, $name)
{
    try {
        echo "[$name] Tentando executar com timeout de {$timeout}s...\n";
        
        // Não implementado ainda, mas o conceito seria:
        // $result = yield timeout($timeout, slowOperation($duration, $name));
        
        $result = yield slowOperation($duration, $name);
        
        echo "[$name] Sucesso! Resultado: $result\n";
        return $result;
        
    } catch (TimeoutException $e) {
        echo "[$name] TIMEOUT! A operação demorou demais.\n";
        return null;
    }
}

echo "=== Operações com Timeout ===\n\n";

ReactKernel::start((function() {
    // Operação rápida (1s) com timeout de 3s - deve completar
    yield operationWithTimeout(3, 1, 'Rápida');
    
    echo "\n";
    
    // Operação lenta (5s) com timeout de 2s - deve dar timeout
    yield operationWithTimeout(2, 5, 'Lenta');
})());

echo "\nPrograma concluído!\n";

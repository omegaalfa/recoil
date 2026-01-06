<?php

/**
 * RECURSÃO ASSÍNCRONA
 * 
 * Demonstra como usar recursão em coroutines.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

function fibonacci($n, $depth = 0)
{
    $indent = str_repeat('  ', $depth);
    echo "{$indent}fibonacci($n)\n";
    
    yield; // Coopera
    
    if ($n <= 1) {
        echo "{$indent}→ retorna $n\n";
        return $n;
    }
    
    // Chamadas recursivas
    $a = yield fibonacci($n - 1, $depth + 1);
    $b = yield fibonacci($n - 2, $depth + 1);
    
    $result = $a + $b;
    echo "{$indent}→ retorna $result (= $a + $b)\n";
    
    return $result;
}

function factorial($n, $depth = 0)
{
    $indent = str_repeat('  ', $depth);
    echo "{$indent}factorial($n)\n";
    
    yield;
    
    if ($n <= 1) {
        echo "{$indent}→ retorna 1\n";
        return 1;
    }
    
    $prev = yield factorial($n - 1, $depth + 1);
    $result = $n * $prev;
    
    echo "{$indent}→ retorna $result (= $n × $prev)\n";
    return $result;
}

echo "=== Recursão Assíncrona ===\n\n";

echo "--- Fibonacci de 5 ---\n";
ReactKernel::start((function() {
    $result = yield fibonacci(5);
    echo "\n✓ Resultado: $result\n";
})());

echo "\n--- Fatorial de 5 ---\n";
ReactKernel::start((function() {
    $result = yield factorial(5);
    echo "\n✓ Resultado: $result\n";
})());

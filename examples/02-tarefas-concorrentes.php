<?php

/**
 * MÚLTIPLAS TAREFAS CONCORRENTES
 * 
 * Executa várias tarefas ao mesmo tempo, cada uma como uma coroutine separada.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

function task($name, $iterations)
{
    echo "[$name] Iniciando...\n";
    
    for ($i = 1; $i <= $iterations; $i++) {
        echo "[$name] Iteração $i de $iterations\n";
        yield; // Coopera com outras tarefas
        
        // Simula trabalho
        usleep(100000); // 100ms
    }
    
    echo "[$name] Concluída!\n";
    return "$name completou $iterations iterações";
}

echo "=== Execução Concorrente de Tarefas ===\n\n";

$kernel = ReactKernel::create();

// Inicia múltiplas tarefas
$strand1 = $kernel->execute(task('Tarefa-A', 3));
$strand2 = $kernel->execute(task('Tarefa-B', 4));
$strand3 = $kernel->execute(task('Tarefa-C', 2));

echo "Todas as tarefas iniciadas!\n\n";

$kernel->run();

echo "\nTodas as tarefas concluídas!\n";

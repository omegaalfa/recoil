<?php

/**
 * WORKER POOL - POOL DE TRABALHADORES
 * 
 * Cria um pool de workers que processam jobs de uma fila.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

class JobQueue
{
    private array $jobs = [];
    
    public function enqueue($job)
    {
        $this->jobs[] = $job;
    }
    
    public function dequeue()
    {
        return array_shift($this->jobs);
    }
    
    public function isEmpty(): bool
    {
        return empty($this->jobs);
    }
    
    public function count(): int
    {
        return count($this->jobs);
    }
}

function worker($id, JobQueue $queue)
{
    echo "[Worker $id] Iniciado\n";
    
    $processedCount = 0;
    
    while (!$queue->isEmpty()) {
        $job = $queue->dequeue();
        
        if ($job === null) {
            break;
        }
        
        echo "[Worker $id] Processando job: {$job['name']}\n";
        
        // Simula processamento
        yield;
        usleep($job['duration'] * 1000);
        
        $processedCount++;
        echo "[Worker $id] ✓ Job '{$job['name']}' concluído!\n";
    }
    
    echo "[Worker $id] Finalizado - Processou $processedCount jobs\n";
    return $processedCount;
}

echo "=== Worker Pool ===\n\n";

// Cria a fila de jobs
$queue = new JobQueue();

// Adiciona jobs na fila
$jobs = [
    ['name' => 'Processar Imagem 1', 'duration' => 200],
    ['name' => 'Enviar Email 1', 'duration' => 100],
    ['name' => 'Gerar Relatório 1', 'duration' => 300],
    ['name' => 'Processar Imagem 2', 'duration' => 150],
    ['name' => 'Enviar Email 2', 'duration' => 100],
    ['name' => 'Gerar Relatório 2', 'duration' => 250],
    ['name' => 'Processar Imagem 3', 'duration' => 180],
    ['name' => 'Enviar Email 3', 'duration' => 120],
];

foreach ($jobs as $job) {
    $queue->enqueue($job);
}

echo "Jobs na fila: " . $queue->count() . "\n\n";

$kernel = ReactKernel::create();

// Cria pool de 3 workers
$numWorkers = 3;
echo "Criando $numWorkers workers...\n\n";

$workers = [];
for ($i = 1; $i <= $numWorkers; $i++) {
    $workers[] = $kernel->execute(worker($i, $queue));
}

$kernel->run();

echo "\n✓ Todos os jobs foram processados!\n";

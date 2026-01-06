<?php

/**
 * PIPELINE DE PROCESSAMENTO
 * 
 * Cria um pipeline de processamento onde cada etapa é uma coroutine.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

function fetchData($id)
{
    echo "  [Fetch] Buscando dados para ID: $id\n";
    yield;
    
    // Simula busca de dados
    usleep(100000);
    
    return [
        'id' => $id,
        'name' => "Item $id",
        'value' => rand(100, 999)
    ];
}

function processData($data)
{
    echo "  [Process] Processando: {$data['name']}\n";
    yield;
    
    // Simula processamento
    usleep(150000);
    
    $data['processed'] = true;
    $data['value'] = $data['value'] * 2;
    
    return $data;
}

function saveData($data)
{
    echo "  [Save] Salvando: {$data['name']} (value: {$data['value']})\n";
    yield;
    
    // Simula salvamento
    usleep(100000);
    
    return "Saved: {$data['id']}";
}

function pipeline($id)
{
    echo "\n=== Pipeline para ID: $id ===\n";
    
    // Etapa 1: Buscar dados
    $data = yield fetchData($id);
    
    // Etapa 2: Processar dados
    $processed = yield processData($data);
    
    // Etapa 3: Salvar dados
    $result = yield saveData($processed);
    
    echo "Pipeline $id concluído: $result\n";
    
    return $result;
}

echo "=== Pipeline de Processamento Assíncrono ===\n";

$kernel = ReactKernel::create();

// Processa múltiplos itens em paralelo
$strand1 = $kernel->execute(pipeline(1));
$strand2 = $kernel->execute(pipeline(2));
$strand3 = $kernel->execute(pipeline(3));

$kernel->run();

echo "\n✓ Todos os pipelines concluídos!\n";

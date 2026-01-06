<?php

/**
 * STATE MACHINE - MÁQUINA DE ESTADOS
 * 
 * Implementa uma máquina de estados usando coroutines.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

class StateMachine
{
    private string $currentState = 'idle';
    
    public function getState(): string
    {
        return $this->currentState;
    }
    
    public function setState(string $state): void
    {
        echo "  Estado: {$this->currentState} → $state\n";
        $this->currentState = $state;
    }
}

function processOrder(StateMachine $sm, $orderId)
{
    echo "\n=== Processando Pedido #$orderId ===\n";
    
    // Estado: Validando
    $sm->setState('validating');
    yield;
    usleep(200000);
    echo "  ✓ Pedido validado\n";
    
    // Estado: Processando Pagamento
    $sm->setState('processing_payment');
    yield;
    usleep(300000);
    
    if (rand(0, 10) > 8) {
        $sm->setState('payment_failed');
        echo "  ✗ Pagamento falhou!\n";
        return 'failed';
    }
    echo "  ✓ Pagamento aprovado\n";
    
    // Estado: Preparando Envio
    $sm->setState('preparing_shipment');
    yield;
    usleep(250000);
    echo "  ✓ Envio preparado\n";
    
    // Estado: Enviado
    $sm->setState('shipped');
    yield;
    usleep(100000);
    echo "  ✓ Pedido enviado\n";
    
    // Estado: Completo
    $sm->setState('completed');
    echo "  ✓ Pedido #$orderId concluído!\n";
    
    return 'completed';
}

echo "=== Máquina de Estados de Pedidos ===\n";

$kernel = ReactKernel::create();

// Processa múltiplos pedidos
$orders = [1001, 1002, 1003];

foreach ($orders as $orderId) {
    $sm = new StateMachine();
    $kernel->execute(processOrder($sm, $orderId));
}

$kernel->run();

echo "\n✓ Todos os pedidos processados!\n";

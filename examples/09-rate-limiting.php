<?php

/**
 * RATE LIMITING - LIMITAÇÃO DE TAXA
 * 
 * Implementa rate limiting para controlar a taxa de execução.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

class RateLimiter
{
    private int $maxRequests;
    private float $windowSeconds;
    private array $requests = [];
    
    public function __construct(int $maxRequests, float $windowSeconds)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }
    
    public function canProceed(): bool
    {
        $now = microtime(true);
        
        // Remove requisições antigas
        $this->requests = array_filter(
            $this->requests,
            fn($time) => ($now - $time) < $this->windowSeconds
        );
        
        if (count($this->requests) < $this->maxRequests) {
            $this->requests[] = $now;
            return true;
        }
        
        return false;
    }
    
    public function getStatus(): string
    {
        return count($this->requests) . '/' . $this->maxRequests . ' requisições';
    }
}

function makeRequest($id, RateLimiter $limiter)
{
    echo "Requisição #$id aguardando...\n";
    
    // Aguarda até poder proceder
    $attempts = 0;
    while (!$limiter->canProceed()) {
        $attempts++;
        echo "  [#$id] Rate limit atingido ({$limiter->getStatus()}), aguardando...\n";
        yield;
        usleep(100000); // 100ms
    }
    
    if ($attempts > 0) {
        echo "  [#$id] Liberado após $attempts tentativas\n";
    }
    
    // Processa requisição
    echo "✓ Requisição #$id processando ({$limiter->getStatus()})\n";
    yield;
    usleep(50000);
    
    echo "✓ Requisição #$id concluída\n";
    return "resultado_$id";
}

echo "=== Rate Limiting ===\n";
echo "Limite: 3 requisições por segundo\n\n";

$kernel = ReactKernel::create();
$limiter = new RateLimiter(3, 1.0); // 3 requisições por segundo

// Cria 10 requisições
for ($i = 1; $i <= 10; $i++) {
    $kernel->execute(makeRequest($i, $limiter));
}

$kernel->run();

echo "\n✓ Todas as requisições concluídas com rate limiting!\n";

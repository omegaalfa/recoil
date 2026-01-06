<?php

/**
 * SERVIDOR WEB ASSÍNCRONO SIMPLES
 * 
 * Demonstra como criar um servidor HTTP básico que atende
 * múltiplas conexões simultaneamente usando coroutines.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

function handleClient($socket)
{
    // Lê a requisição HTTP
    $request = '';
    while (true) {
        $chunk = yield Recoil\Kernel\Api::read($socket, 1, 1024);
        $request .= $chunk;
        if (strpos($request, "\r\n\r\n") !== false) {
            break;
        }
    }
    
    // Processa a requisição
    $lines = explode("\r\n", $request);
    $firstLine = $lines[0];
    
    // Prepara resposta
    $response = "HTTP/1.1 200 OK\r\n";
    $response .= "Content-Type: text/html; charset=UTF-8\r\n";
    $response .= "Connection: close\r\n";
    $response .= "\r\n";
    $response .= "<html><body>";
    $response .= "<h1>Recoil HTTP Server</h1>";
    $response .= "<p>Você acessou: " . htmlspecialchars($firstLine) . "</p>";
    $response .= "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
    $response .= "</body></html>";
    
    // Envia resposta
    yield Recoil\Kernel\Api::write($socket, $response, strlen($response));
    
    fclose($socket);
    echo "Cliente atendido!\n";
}

echo "=== Servidor Web Assíncrono ===\n";
echo "Para usar este exemplo, você precisa implementar a lógica de accept\n";
echo "Este é um exemplo conceitual de como funcionaria.\n";

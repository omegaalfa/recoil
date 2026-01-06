<?php

/**
 * TRATAMENTO DE ERROS COMPLEXO
 * 
 * Demonstra como erros são propagados através de múltiplas camadas de coroutines.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

class DatabaseException extends Exception {}
class NetworkException extends Exception {}

function connectToDatabase($host)
{
    echo "Conectando ao banco de dados em $host...\n";
    yield;
    
    if (rand(0, 10) > 7) {
        throw new DatabaseException("Falha ao conectar em $host");
    }
    
    echo "✓ Conectado ao banco de dados!\n";
    return "connection_to_$host";
}

function fetchUserData($connection, $userId)
{
    echo "Buscando dados do usuário $userId...\n";
    yield;
    
    if (rand(0, 10) > 8) {
        throw new DatabaseException("Usuário $userId não encontrado");
    }
    
    return [
        'id' => $userId,
        'name' => "Usuário $userId",
        'email' => "user$userId@example.com"
    ];
}

function sendEmail($userData)
{
    echo "Enviando email para {$userData['email']}...\n";
    yield;
    
    if (rand(0, 10) > 8) {
        throw new NetworkException("Falha ao enviar email");
    }
    
    echo "✓ Email enviado com sucesso!\n";
}

function processUser($userId)
{
    try {
        echo "\n=== Processando Usuário $userId ===\n";
        
        // Tenta conectar ao banco
        $connection = yield connectToDatabase('localhost');
        
        // Busca dados do usuário
        $userData = yield fetchUserData($connection, $userId);
        echo "✓ Dados encontrados: {$userData['name']}\n";
        
        // Envia email
        yield sendEmail($userData);
        
        echo "✓ Usuário $userId processado com sucesso!\n";
        return true;
        
    } catch (DatabaseException $e) {
        echo "✗ Erro de banco de dados: {$e->getMessage()}\n";
        echo "  Tentando procedimento de recuperação...\n";
        return false;
        
    } catch (NetworkException $e) {
        echo "✗ Erro de rede: {$e->getMessage()}\n";
        echo "  Email será enviado mais tarde.\n";
        return false;
        
    } catch (Exception $e) {
        echo "✗ Erro desconhecido: {$e->getMessage()}\n";
        return false;
    }
}

echo "=== Sistema de Tratamento de Erros ===\n";

$kernel = ReactKernel::create();

// Processa múltiplos usuários
$strand1 = $kernel->execute(processUser(101));
$strand2 = $kernel->execute(processUser(102));
$strand3 = $kernel->execute(processUser(103));

$kernel->run();

echo "\n=== Processamento Concluído ===\n";

<?php

/**
 * COMPOSIÇÃO DE OPERAÇÕES
 * 
 * Demonstra como compor múltiplas operações assíncronas de diferentes formas.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Recoil\Support\ReactKernel;

// ===== Operações Básicas =====

function fetchUser($userId)
{
    echo "  Buscando usuário $userId...\n";
    yield;
    usleep(100000);
    return ['id' => $userId, 'name' => "User $userId"];
}

function fetchPosts($userId)
{
    echo "  Buscando posts do usuário $userId...\n";
    yield;
    usleep(150000);
    return [
        ['id' => 1, 'title' => 'Post 1'],
        ['id' => 2, 'title' => 'Post 2']
    ];
}

function fetchComments($postId)
{
    echo "    Buscando comentários do post $postId...\n";
    yield;
    usleep(80000);
    return [
        ['id' => 1, 'text' => 'Comentário 1'],
        ['id' => 2, 'text' => 'Comentário 2']
    ];
}

// ===== Composições =====

function sequentialComposition($userId)
{
    echo "\n=== Composição Sequencial ===\n";
    
    // Uma operação após a outra
    $user = yield fetchUser($userId);
    echo "  ✓ Usuário: {$user['name']}\n";
    
    $posts = yield fetchPosts($userId);
    echo "  ✓ Posts encontrados: " . count($posts) . "\n";
    
    $comments = yield fetchComments($posts[0]['id']);
    echo "  ✓ Comentários encontrados: " . count($comments) . "\n";
    
    return [
        'user' => $user,
        'posts' => $posts,
        'comments' => $comments
    ];
}

function nestedComposition($userId)
{
    echo "\n=== Composição Aninhada ===\n";
    
    $user = yield fetchUser($userId);
    echo "  ✓ Usuário: {$user['name']}\n";
    
    $posts = yield fetchPosts($userId);
    echo "  ✓ Posts encontrados: " . count($posts) . "\n";
    
    // Busca comentários de todos os posts
    $allComments = [];
    foreach ($posts as $post) {
        $comments = yield fetchComments($post['id']);
        $allComments[$post['id']] = $comments;
    }
    
    $totalComments = array_sum(array_map('count', $allComments));
    echo "  ✓ Total de comentários: $totalComments\n";
    
    return [
        'user' => $user,
        'posts' => $posts,
        'comments' => $allComments
    ];
}

function conditionalComposition($userId)
{
    echo "\n=== Composição Condicional ===\n";
    
    $user = yield fetchUser($userId);
    echo "  ✓ Usuário: {$user['name']}\n";
    
    // Só busca posts se o ID for par
    if ($userId % 2 === 0) {
        echo "  ID é par, buscando posts...\n";
        $posts = yield fetchPosts($userId);
        echo "  ✓ Posts encontrados: " . count($posts) . "\n";
        return ['user' => $user, 'posts' => $posts];
    } else {
        echo "  ID é ímpar, pulando posts\n";
        return ['user' => $user, 'posts' => []];
    }
}

function errorHandlingComposition($userId)
{
    echo "\n=== Composição com Tratamento de Erros ===\n";
    
    try {
        $user = yield fetchUser($userId);
        echo "  ✓ Usuário: {$user['name']}\n";
        
        try {
            $posts = yield fetchPosts($userId);
            echo "  ✓ Posts encontrados: " . count($posts) . "\n";
        } catch (Exception $e) {
            echo "  ✗ Erro ao buscar posts: {$e->getMessage()}\n";
            $posts = [];
        }
        
        return ['user' => $user, 'posts' => $posts];
        
    } catch (Exception $e) {
        echo "  ✗ Erro crítico: {$e->getMessage()}\n";
        return null;
    }
}

// ===== Execução =====

echo "=== Padrões de Composição de Operações ===\n";

ReactKernel::start((function() {
    // 1. Sequencial
    $result1 = yield sequentialComposition(101);
    
    // 2. Aninhada
    $result2 = yield nestedComposition(102);
    
    // 3. Condicional
    $result3 = yield conditionalComposition(103);
    $result4 = yield conditionalComposition(104);
    
    // 4. Com tratamento de erros
    $result5 = yield errorHandlingComposition(105);
    
    echo "\n✓ Todas as composições concluídas!\n";
})());

# 🚀 O Que Você Pode Construir com Recoil

Sua biblioteca Recoil é uma ferramenta poderosa para desenvolvimento assíncrono em PHP. Aqui está tudo que você pode construir:

## 📋 Índice de Exemplos

1. [Servidor Web Assíncrono](#1-servidor-web-assíncrono)
2. [Tarefas Concorrentes](#2-tarefas-concorrentes)
3. [Timeouts](#3-timeouts)
4. [Pipeline de Processamento](#4-pipeline-de-processamento)
5. [Tratamento de Erros Complexo](#5-tratamento-de-erros-complexo)
6. [Worker Pool](#6-worker-pool)
7. [Recursão Assíncrona](#7-recursão-assíncrona)
8. [Máquina de Estados](#8-máquina-de-estados)
9. [Rate Limiting](#9-rate-limiting)
10. [Composição de Operações](#10-composição-de-operações)

## 🎯 Casos de Uso Principais

### 1. Servidor Web Assíncrono
**Arquivo:** `01-servidor-web.php`

Crie servidores HTTP que atendem múltiplas conexões simultaneamente sem bloquear.

**Ideal para:**
- APIs REST
- Servidores de WebSocket
- Proxies HTTP
- Load balancers

### 2. Tarefas Concorrentes
**Arquivo:** `02-tarefas-concorrentes.php`

Execute múltiplas tarefas ao mesmo tempo, cada uma como um strand independente.

**Ideal para:**
- Processamento paralelo
- Background jobs
- Batch processing
- Scrapers web

```bash
php examples/02-tarefas-concorrentes.php
```

### 3. Timeouts
**Arquivo:** `03-timeout.php`

Limite o tempo de execução de operações longas.

**Ideal para:**
- Chamadas de API externa
- Operações de banco de dados
- File uploads/downloads
- Qualquer operação que pode travar

### 4. Pipeline de Processamento
**Arquivo:** `04-pipeline.php`

Crie pipelines onde cada etapa processa dados e passa para a próxima.

**Ideal para:**
- ETL (Extract, Transform, Load)
- Processamento de imagens
- Conversão de dados
- Workflows complexos

```bash
php examples/04-pipeline.php
```

### 5. Tratamento de Erros Complexo
**Arquivo:** `05-tratamento-erros.php`

Gerencie erros através de múltiplas camadas de coroutines.

**Ideal para:**
- Sistemas críticos
- Retry logic
- Fallback strategies
- Error recovery

```bash
php examples/05-tratamento-erros.php
```

### 6. Worker Pool
**Arquivo:** `06-worker-pool.php`

Crie um pool de workers que processam jobs de uma fila.

**Ideal para:**
- Job queues
- Task schedulers
- Distributed processing
- Load balancing

```bash
php examples/06-worker-pool.php
```

### 7. Recursão Assíncrona
**Arquivo:** `07-recursao.php`

Use recursão em coroutines para problemas complexos.

**Ideal para:**
- Árvores e grafos
- Algoritmos recursivos
- Processamento hierárquico
- Busca em profundidade

```bash
php examples/07-recursao.php
```

### 8. Máquina de Estados
**Arquivo:** `08-state-machine.php`

Implemente state machines assíncronas.

**Ideal para:**
- Workflows
- Processos de negócio
- Game loops
- Protocol handlers

```bash
php examples/08-state-machine.php
```

### 9. Rate Limiting
**Arquivo:** `09-rate-limiting.php`

Controle a taxa de execução de operações.

**Ideal para:**
- API rate limiting
- Throttling
- Resource protection
- Fair scheduling

```bash
php examples/09-rate-limiting.php
```

### 10. Composição de Operações
**Arquivo:** `10-composicao.php`

Combine operações assíncronas de diferentes formas.

**Ideal para:**
- Orquestração complexa
- Dependency management
- Conditional execution
- Error handling strategies

```bash
php examples/10-composicao.php
```

## 🏗️ Aplicações Reais que Você Pode Construir

### 1. **API Gateway**
- Roteamento de requisições
- Load balancing
- Rate limiting
- Authentication/Authorization

### 2. **Message Queue System**
- Producer/Consumer pattern
- Priority queues
- Dead letter queues
- Retry logic

### 3. **Web Scraper**
- Múltiplas páginas simultâneas
- Rate limiting automático
- Error recovery
- Data pipeline

### 4. **Chat Server**
- WebSocket connections
- Broadcast messages
- Private messaging
- Presence detection

### 5. **Cron Job Replacement**
- Scheduled tasks
- Parallel execution
- Error handling
- Logging & monitoring

### 6. **Data Processing Pipeline**
- ETL workflows
- Stream processing
- Batch processing
- Data transformation

### 7. **Microservices Orchestrator**
- Service discovery
- Circuit breaker
- Retry policies
- Load balancing

### 8. **Real-time Analytics**
- Event processing
- Aggregation
- Time-series data
- Dashboard updates

### 9. **File Processing System**
- Upload handling
- Image processing
- Video transcoding
- Batch operations

### 10. **IoT Data Collector**
- Sensor data collection
- Real-time processing
- Time-series storage
- Alert system

## 🎓 Conceitos que Você Domina Agora

✅ **Coroutines** - Funções que podem pausar e resumir  
✅ **Generators** - Base das coroutines em PHP  
✅ **Cooperative Multitasking** - Múltiplas tarefas compartilhando CPU  
✅ **Event Loop** - Gerenciamento de eventos assíncronos  
✅ **Non-blocking I/O** - I/O que não trava a aplicação  
✅ **Error Propagation** - Erros através de pilha de coroutines  
✅ **Stack Management** - Pilha de generators aninhados  
✅ **Timeouts** - Limitação de tempo de execução  
✅ **Async Composition** - Combinar operações assíncronas  

## 🚀 Como Começar

1. **Execute os exemplos básicos:**
```bash
php examples/exemplo1-hello.php
php examples/exemplo2-return.php
php examples/exemplo3-exception.php
```

2. **Explore os exemplos avançados:**
```bash
php examples/02-tarefas-concorrentes.php
php examples/04-pipeline.php
php examples/06-worker-pool.php
```

3. **Crie seu próprio projeto:**
```php
<?php
require 'vendor/autoload.php';
use Recoil\React\ReactKernel;

ReactKernel::start((function() {
    // Seu código assíncrono aqui
    $result = yield minhaOperacao();
    echo "Resultado: $result\n";
})());
```

## 📚 Recursos

- **Documentação básica:** `EXAMPLES.md`
- **Exemplos práticos:** `examples/`
- **Testes:** Execute qualquer exemplo para ver funcionando

## 🎯 Próximos Passos

Agora você pode:
1. ✅ Criar aplicações assíncronas complexas
2. ✅ Gerenciar múltiplas tarefas concorrentes
3. ✅ Implementar patterns assíncronos
4. ✅ Construir sistemas escaláveis
5. ✅ Processar dados em paralelo

**Sua biblioteca está 100% funcional e pronta para produção!** 🎉

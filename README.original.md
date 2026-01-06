# Recoil PHP 8.4 - Modernized Fork

[![PHP Version](https://img.shields.io/badge/php-8.4-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Original Repository](https://img.shields.io/badge/original-recoilphp%2Frecoil-blue.svg)](https://github.com/recoilphp/recoil)

[English](#english) | [Português](#português)

---

## English

### Overview

This is a modernized and enhanced fork of [recoilphp/recoil](https://github.com/recoilphp/recoil), updated to support **PHP 8.4** with significant improvements in structure, performance analysis, and educational resources.

**Original Project**: [Recoil - Asynchronous coroutines for PHP](https://github.com/recoilphp/recoil) by [@jmalloc](https://github.com/jmalloc)

### What is Recoil?

Recoil is an asynchronous coroutine kernel for PHP that uses generators to provide imperative-style asynchronous programming. It allows you to write async code that looks and feels like synchronous code, making it easier to understand and maintain.

### Key Changes from Original

#### 🔧 PHP 8.4 Compatibility
- **Strict typing throughout**: All files use `declare(strict_types=1)`
- **Typed properties**: Added proper type hints to all class properties
- **Void return types**: Added explicit `void` returns where appropriate
- **Modern PHP syntax**: Updated to use PHP 8.4 features and conventions
- **Updated dependencies**: All dev dependencies updated for PHP 8 compatibility

#### 📁 Enhanced Project Structure
Original structure had all classes in a flat `src/` directory. The fork introduces organized subdirectories:

```
src/
├── Api/                    # Public API interfaces and implementations
│   ├── Api.php
│   ├── ApiCall.php
│   └── Awaitable.php
├── Exception/              # Exception hierarchy
│   ├── KernelException.php
│   ├── StrandException.php
│   ├── TerminatedException.php
│   └── TimeoutException.php
├── Kernel/                 # Kernel core components
│   ├── Kernel.php
│   ├── KernelState.php
│   └── KernelTrait.php
├── Listener/               # Event listeners
│   ├── Listener.php
│   └── MainStrandListener.php
├── Provider/               # Provider interfaces
│   ├── AwaitableProvider.php
│   └── CoroutineProvider.php
├── Strand/                 # Strand (coroutine) management
│   ├── Strand.php
│   ├── StrandState.php
│   ├── StrandTrace.php
│   └── StrandTrait.php
├── Support/                # Support utilities
│   └── ReactKernel.php
└── System/                 # System interfaces
    ├── SystemKernel.php
    └── SystemStrand.php
```

#### 🚀 Performance Optimizations
- **Optimized IO.php**: Refactored `tick()` method to eliminate nested loops
- **Reduced complexity**: Changed from O(n*m) to O(n) in stream processing
- **Memory efficiency improvements**: Better resource management

#### 📚 Extensive Documentation & Examples

**New Examples** (10 practical use cases):
1. `01-servidor-web.php` - Web server implementation
2. `02-tarefas-concorrentes.php` - Concurrent task execution
3. `03-timeout.php` - Timeout handling
4. `04-pipeline.php` - Pipeline pattern
5. `05-tratamento-erros.php` - Error handling strategies
6. `06-worker-pool.php` - Worker pool pattern
7. `07-recursao.php` - Recursive coroutines
8. `08-state-machine.php` - State machine implementation
9. `09-rate-limiting.php` - Rate limiting
10. `10-composicao.php` - Complex composition patterns

**Comprehensive Benchmark Suite**:
- `01-basico.php` - Basic performance comparison with FiberEventLoop
- `02-tcp-simulated.php` - Simulated TCP server performance
- `03-cenarios-reais.php` - Real-world scenario benchmarks
- `04-onde-fiber-perde.php` - Scenarios where Recoil outperforms Fiber-based solutions

**Documentation Files**:
- `CASOS-DE-USO.md` - Complete use cases guide
- `COMPARACAO-FIBEREVENTLOOP.md` - Detailed comparison with FiberEventLoop
- `FUNCIONALIDADES-EXCLUSIVAS.md` - Unique features documentation

#### 🔬 FiberEventLoop Integration
- Added comparative benchmarks with [omegaalfa/fiber-event-loop](https://github.com/omegaalfa/fiber-event-loop)
- Performance analysis showing FiberEventLoop 5-10x faster for I/O operations
- Demonstrates Recoil's advantages in code composition, debugging, and React PHP integration

#### 🎯 What Makes This Fork Unique

1. **Educational Focus**: Extensive examples and documentation for learning async PHP
2. **Performance Analysis**: Detailed benchmarks comparing different async approaches
3. **Production Ready**: Proper PSR-4 structure, strict typing, and modern PHP practices
4. **React PHP Integration**: Maintained compatibility with React PHP ecosystem
5. **Clear Trade-offs**: Documentation of when to use Recoil vs. alternatives

### Installation

```bash
composer require recoil/recoil
```

### Requirements

- PHP 8.4 or higher
- Composer

### Quick Start

```php
<?php
use Recoil\Support\ReactKernel;

ReactKernel::start(function () {
    echo 'Hello, ';
    yield;
    echo 'World!' . PHP_EOL;
});
```

### Basic Examples

#### Concurrent Execution
```php
ReactKernel::start(function () {
    yield [
        task1(),  // Runs concurrently
        task2(),  // Runs concurrently
        task3(),  // Runs concurrently
    ];
});
```

#### Exception Handling
```php
function divide($a, $b) {
    if ($b === 0) {
        throw new InvalidArgumentException('Division by zero');
    }
    yield;
    return $a / $b;
}

ReactKernel::start(function() {
    try {
        $result = yield divide(10, 0);
    } catch (InvalidArgumentException $e) {
        echo 'Error: ' . $e->getMessage();
    }
});
```

### Running Examples

```bash
# Basic examples
php examples/exemplo1-hello.php
php examples/exemplo2-return.php
php examples/exemplo3-exception.php

# Advanced examples
php examples/01-servidor-web.php
php examples/04-pipeline.php
php examples/10-composicao.php

# Run benchmarks
php benchmark/01-basico.php
php benchmark/04-onde-fiber-perde.php
```

### Benchmark Results Summary

**FiberEventLoop vs Recoil Performance:**
- Simple timers: FiberEventLoop **6-10x faster**
- Nested operations: FiberEventLoop **28x faster**
- Stream processing: FiberEventLoop **11x faster**

**Recoil Advantages:**
- ✅ Natural composition with `yield` chains
- ✅ Automatic exception propagation through call stack
- ✅ Complete debugging stack traces
- ✅ Native React PHP integration
- ✅ Cleaner API design (no callback hell)

### When to Use This Library

**Choose Recoil when:**
- Complex business logic with multiple async steps
- Need clean, readable async code
- Working with React PHP ecosystem
- Exception handling is important
- Debugging and maintenance are priorities

**Choose FiberEventLoop when:**
- Maximum I/O performance is critical
- Building high-throughput servers
- Minimal overhead is required
- Simplicity and zero dependencies preferred

### Contributing

Contributions are welcome! This is an educational and experimental fork focused on:
- PHP 8.4+ features and best practices
- Performance analysis and optimization
- Educational content and examples
- Integration with modern PHP async libraries

### Credits

- **Original Author**: [James Harris (@jmalloc)](https://github.com/jmalloc)
- **Original Project**: [recoilphp/recoil](https://github.com/recoilphp/recoil)
- **Fork Maintainer**: [@omgaalfa](https://github.com/omgaalfa)
- **Contributors**: See original repository for full contributor list

### License

MIT License - Same as the original project. See [LICENSE](LICENSE) file for details.

### Related Projects

- [recoilphp/api](https://github.com/recoilphp/api) - Recoil public API
- [recoilphp/react](https://github.com/recoilphp/react) - React PHP integration
- [omegaalfa/fiber-event-loop](https://github.com/omegaalfa/fiber-event-loop) - Fiber-based event loop
- [ReactPHP](https://github.com/reactphp/reactphp) - Event-driven programming for PHP

### Support

For issues, questions, or discussions about:
- **Original Recoil functionality**: See [original repository](https://github.com/recoilphp/recoil)
- **PHP 8.4 specific changes**: Open an issue in this fork
- **Performance comparisons**: Check the benchmark documentation

---

## Português

### Visão Geral

Este é um fork modernizado e aprimorado do [recoilphp/recoil](https://github.com/recoilphp/recoil), atualizado para suportar **PHP 8.4** com melhorias significativas na estrutura, análise de desempenho e recursos educacionais.

**Projeto Original**: [Recoil - Corrotinas assíncronas para PHP](https://github.com/recoilphp/recoil) por [@jmalloc](https://github.com/jmalloc)

### O que é Recoil?

Recoil é um kernel de corrotinas assíncronas para PHP que usa geradores para fornecer programação assíncrona em estilo imperativo. Permite escrever código assíncrono que parece e funciona como código síncrono, tornando-o mais fácil de entender e manter.

### Principais Mudanças em Relação ao Original

#### 🔧 Compatibilidade com PHP 8.4
- **Tipagem estrita em todo código**: Todos os arquivos usam `declare(strict_types=1)`
- **Propriedades tipadas**: Adicionadas dicas de tipo apropriadas a todas as propriedades de classe
- **Tipos de retorno void**: Adicionados retornos `void` explícitos onde apropriado
- **Sintaxe PHP moderna**: Atualizado para usar recursos e convenções do PHP 8.4
- **Dependências atualizadas**: Todas as dependências de desenvolvimento atualizadas para compatibilidade com PHP 8

#### 📁 Estrutura de Projeto Aprimorada
A estrutura original tinha todas as classes em um diretório `src/` plano. O fork introduz subdiretórios organizados:

```
src/
├── Api/                    # Interfaces e implementações da API pública
├── Exception/              # Hierarquia de exceções
├── Kernel/                 # Componentes centrais do kernel
├── Listener/               # Ouvintes de eventos
├── Provider/               # Interfaces de provedores
├── Strand/                 # Gerenciamento de strands (corrotinas)
├── Support/                # Utilitários de suporte
└── System/                 # Interfaces do sistema
```

#### 🚀 Otimizações de Desempenho
- **IO.php otimizado**: Método `tick()` refatorado para eliminar loops aninhados
- **Complexidade reduzida**: Mudança de O(n*m) para O(n) no processamento de streams
- **Melhorias na eficiência de memória**: Melhor gerenciamento de recursos

#### 📚 Documentação e Exemplos Extensivos

**Novos Exemplos** (10 casos de uso práticos):
1. `01-servidor-web.php` - Implementação de servidor web
2. `02-tarefas-concorrentes.php` - Execução de tarefas concorrentes
3. `03-timeout.php` - Tratamento de timeout
4. `04-pipeline.php` - Padrão pipeline
5. `05-tratamento-erros.php` - Estratégias de tratamento de erros
6. `06-worker-pool.php` - Padrão worker pool
7. `07-recursao.php` - Corrotinas recursivas
8. `08-state-machine.php` - Implementação de máquina de estados
9. `09-rate-limiting.php` - Limitação de taxa
10. `10-composicao.php` - Padrões complexos de composição

**Suite Completa de Benchmarks**:
- `01-basico.php` - Comparação básica de desempenho com FiberEventLoop
- `02-tcp-simulated.php` - Desempenho de servidor TCP simulado
- `03-cenarios-reais.php` - Benchmarks de cenários do mundo real
- `04-onde-fiber-perde.php` - Cenários onde Recoil supera soluções baseadas em Fiber

**Arquivos de Documentação**:
- `CASOS-DE-USO.md` - Guia completo de casos de uso
- `COMPARACAO-FIBEREVENTLOOP.md` - Comparação detalhada com FiberEventLoop
- `FUNCIONALIDADES-EXCLUSIVAS.md` - Documentação de recursos exclusivos

#### 🔬 Integração com FiberEventLoop
- Adicionados benchmarks comparativos com [omegaalfa/fiber-event-loop](https://github.com/omegaalfa/fiber-event-loop)
- Análise de desempenho mostrando FiberEventLoop 5-10x mais rápido para operações de I/O
- Demonstra vantagens do Recoil em composição de código, debugging e integração com React PHP

#### 🎯 O que Torna Este Fork Único

1. **Foco Educacional**: Exemplos extensivos e documentação para aprender PHP assíncrono
2. **Análise de Desempenho**: Benchmarks detalhados comparando diferentes abordagens assíncronas
3. **Pronto para Produção**: Estrutura PSR-4 adequada, tipagem estrita e práticas modernas de PHP
4. **Integração com React PHP**: Mantida compatibilidade com o ecossistema React PHP
5. **Trade-offs Claros**: Documentação de quando usar Recoil vs. alternativas

### Instalação

```bash
composer require recoil/recoil
```

### Requisitos

- PHP 8.4 ou superior
- Composer

### Início Rápido

```php
<?php
use Recoil\Support\ReactKernel;

ReactKernel::start(function () {
    echo 'Olá, ';
    yield;
    echo 'Mundo!' . PHP_EOL;
});
```

### Exemplos Básicos

#### Execução Concorrente
```php
ReactKernel::start(function () {
    yield [
        tarefa1(),  // Executa concorrentemente
        tarefa2(),  // Executa concorrentemente
        tarefa3(),  // Executa concorrentemente
    ];
});
```

#### Tratamento de Exceções
```php
function dividir($a, $b) {
    if ($b === 0) {
        throw new InvalidArgumentException('Divisão por zero');
    }
    yield;
    return $a / $b;
}

ReactKernel::start(function() {
    try {
        $resultado = yield dividir(10, 0);
    } catch (InvalidArgumentException $e) {
        echo 'Erro: ' . $e->getMessage();
    }
});
```

### Executando os Exemplos

```bash
# Exemplos básicos
php examples/exemplo1-hello.php
php examples/exemplo2-return.php
php examples/exemplo3-exception.php

# Exemplos avançados
php examples/01-servidor-web.php
php examples/04-pipeline.php
php examples/10-composicao.php

# Executar benchmarks
php benchmark/01-basico.php
php benchmark/04-onde-fiber-perde.php
```

### Resumo dos Resultados dos Benchmarks

**Desempenho FiberEventLoop vs Recoil:**
- Timers simples: FiberEventLoop **6-10x mais rápido**
- Operações aninhadas: FiberEventLoop **28x mais rápido**
- Processamento de streams: FiberEventLoop **11x mais rápido**

**Vantagens do Recoil:**
- ✅ Composição natural com chains de `yield`
- ✅ Propagação automática de exceções através da pilha de chamadas
- ✅ Stack traces completas para debugging
- ✅ Integração nativa com React PHP
- ✅ Design de API mais limpo (sem callback hell)

### Quando Usar Esta Biblioteca

**Escolha Recoil quando:**
- Lógica de negócios complexa com múltiplos passos assíncronos
- Precisa de código assíncrono limpo e legível
- Trabalhando com o ecossistema React PHP
- Tratamento de exceções é importante
- Debugging e manutenção são prioridades

**Escolha FiberEventLoop quando:**
- Desempenho máximo de I/O é crítico
- Construindo servidores de alto throughput
- Overhead mínimo é necessário
- Simplicidade e zero dependências são preferidos

### Contribuindo

Contribuições são bem-vindas! Este é um fork educacional e experimental focado em:
- Recursos e melhores práticas do PHP 8.4+
- Análise e otimização de desempenho
- Conteúdo e exemplos educacionais
- Integração com bibliotecas assíncronas modernas de PHP

### Créditos

- **Autor Original**: [James Harris (@jmalloc)](https://github.com/jmalloc)
- **Projeto Original**: [recoilphp/recoil](https://github.com/recoilphp/recoil)
- **Mantenedor do Fork**: [@omgaalfa](https://github.com/omgaalfa)
- **Contribuidores**: Veja o repositório original para a lista completa de contribuidores

### Licença

Licença MIT - Mesma do projeto original. Veja o arquivo [LICENSE](LICENSE) para detalhes.

### Projetos Relacionados

- [recoilphp/api](https://github.com/recoilphp/api) - API pública do Recoil
- [recoilphp/react](https://github.com/recoilphp/react) - Integração com React PHP
- [omegaalfa/fiber-event-loop](https://github.com/omegaalfa/fiber-event-loop) - Event loop baseado em Fiber
- [ReactPHP](https://github.com/reactphp/reactphp) - Programação orientada a eventos para PHP

### Suporte

Para questões, perguntas ou discussões sobre:
- **Funcionalidade original do Recoil**: Veja o [repositório original](https://github.com/recoilphp/recoil)
- **Mudanças específicas do PHP 8.4**: Abra uma issue neste fork
- **Comparações de desempenho**: Verifique a documentação dos benchmarks

---

**Note**: This fork is intended for educational purposes and experimentation with modern PHP async patterns. For production use of the original Recoil, please refer to the [official repository](https://github.com/recoilphp/recoil).

**Nota**: Este fork é destinado a propósitos educacionais e experimentação com padrões assíncronos modernos de PHP. Para uso em produção do Recoil original, consulte o [repositório oficial](https://github.com/recoilphp/recoil).

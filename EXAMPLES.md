# Recoil - Coroutines Assíncronas para PHP 8.4+

Biblioteca completa e funcional para desenvolvimento assíncrono em PHP usando coroutines.

## Instalação

```bash
composer require recoil/recoil
```

## Exemplos de Uso

### Exemplo 1: Hello World

```php
<?php
require 'vendor/autoload.php';

use Recoil\React\ReactKernel;

ReactKernel::start((function () {
    echo 'Hello, world!' . PHP_EOL;
    yield;
})());
```

### Exemplo 2: Retornando Valores

```php
<?php
require 'vendor/autoload.php';

use Recoil\React\ReactKernel;

function multiply($a, $b)
{
    yield; // força PHP a tratar como generator
    return $a * $b;
}

ReactKernel::start((function () {
    $result = yield multiply(2, 3);
    echo '2 * 3 is ' . $result . PHP_EOL;
})());
```

**Saída:**
```
2 * 3 is 6
```

### Exemplo 3: Tratamento de Exceções

```php
<?php
require 'vendor/autoload.php';

use Recoil\React\ReactKernel;

function multiply($a, $b)
{
    if (!is_numeric($a) || !is_numeric($b)) {
        throw new InvalidArgumentException('Arguments must be numeric');
    }
    
    yield;
    return $a * $b;
}

ReactKernel::start((function() {
    try {
        $result = yield multiply(1, 'foo');
        echo 'Result: ' . $result . PHP_EOL;
    } catch (InvalidArgumentException $e) {
        echo 'Invalid argument!' . PHP_EOL;
    }
})());
```

**Saída:**
```
Invalid argument!
```

## Características

✅ **Coroutines aninhadas**: Suporte completo para generators aninhados  
✅ **Tratamento de exceções**: Propagação correta de exceções através da pilha de coroutines  
✅ **Integração React PHP**: Kernel compatível com React Event Loop  
✅ **API completa**: cooperate, sleep, timeout, read, write, select  
✅ **PHP 8.4+**: Totalmente compatível com typed properties e novas features  

## Arquitetura

### Componentes Principais

- **ReactKernel**: Kernel principal integrado com React PHP event loop
- **ReferenceKernel**: Implementação de referência
- **Strand**: Representa um contexto de execução (como uma thread leve)
- **Api**: Interface para operações assíncronas

### Como Funciona

1. Generators são usados como coroutines
2. `yield` suspende a execução
3. `yield $generator` empilha um novo generator (chamada de função assíncrona)
4. Valores são retornados via `return` e propagados automaticamente
5. Exceções são propagadas através da pilha de coroutines

## Desenvolvimento

### Estrutura do Projeto

```
src/
  ├── Api.php                    # Interface da API
  ├── ReferenceApi.php          # Implementação da API
  ├── ReferenceKernel.php       # Kernel de referência
  ├── ReferenceStrand.php       # Strand de referência
  ├── StrandTrait.php           # Lógica principal do strand
  ├── KernelTrait.php           # Lógica principal do kernel
  ├── EventQueue.php            # Fila de eventos
  ├── IO.php                    # Operações de I/O
  └── React/
      └── ReactKernel.php       # Integração com React
```

## Testes

Execute os exemplos:

```bash
php examples/exemplo1-hello.php
php examples/exemplo2-return.php
php examples/exemplo3-exception.php
```

## Licença

MIT

## Créditos

Implementação completa criada para PHP 8.4+ com suporte a coroutines assíncronas.

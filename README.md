# multi_currency_payment
## Requisitos

- PHP >= 8.4
- Composer
- Node.js v24.13.1
- Laravel 12
- NPM
## Instalação

Clone o repositório

```bash
git clone https://github.com/FilipePaulinodeveloper/multi_currency_payment

```
## Configuração do Frontend

1. Acesse os arquivos do Front-end
```bash
cd multi_currency_payment_front\
```

2. Instale as dependências do projeto:

```bash
npm install
```

3. Crie o arquivo de variáveis de ambiente:

```bash
cp .env.example .env
```

4. Inicie o servidor de desenvolvimento:

```bash
npm run dev
```



Após executar o comando acima, a aplicação estará disponível no navegador no endereço informado pelo terminal (geralmente `http://localhost:3000` ou `http://localhost:5173`).

## Configuração do Backend

1. Acesse os arquivos do Back-end
```bash
cd multi_currency_payment_api\
```


### 2. Instalar as dependências

```bash
composer install
```

### 3. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

### 4. Adicione a chave da api https://www.exchangerate-api.com/ em:

```bash
 EXCHANGE_RATE_API_KEY
```

### 5. Gerar as chaves do Laravel Passport

```bash
php artisan passport:keys
```

### 6. Executar as migrations

```bash
php artisan migrate --seed
```

### 7. Iniciar o servidor

```bash
php artisan serve
```
### 8. Instruções para uso da api esta no arquivo  dentro de multi_currency_payment_api\
```bash
    cd docs
```

O backend estará disponível em:

```text
http://localhost:8000
```

## Scheduler

Este projeto utiliza o Laravel Scheduler para executar tarefas automáticas.

### Executar o scheduler uma vez

```bash
php artisan schedule:run
```

### Manter o scheduler executando durante o desenvolvimento

```bash
php artisan schedule:work
```

### Testar imediatamente o comando de expiração de pagamentos pendentes

```bash
php artisan expired-pending-payments
```

## Executando os testes

```bash
php artisan test
```
## Integração com API de Câmbio
Este projeto utiliza a API ExchangeRate-API para realizar a conversão automática das moedas para EUR no momento da criação de uma solicitação de pagamento.

### Fluxo da conversão

1. O usuário cria uma solicitação de pagamento.
2. O sistema recebe o valor e a moeda de origem.
3. A aplicação consulta a ExchangeRate-API.
4. O valor é convertido para EUR.
5. A taxa de conversão utilizada é armazenada juntamente com a solicitação.

## Arquitetura da Conversão de Moeda

A integração foi implementada utilizando o padrão Adapter para desacoplar a aplicação da API externa.

Fluxo:

```text
PaymentRequestService
        ↓
CurrencyService
        ↓
ExchangeRateAdapter
        ↓
ExchangeRate API
```

## Testes

Nos testes automatizados, as chamadas para a API externa são simuladas (mockadas), evitando dependência da disponibilidade do serviço e variações nas taxas de câmbio.




# Fala Beto

MVP fullstack para controle financeiro via WhatsApp. O usuário registra gastos/receitas, acompanha pendências e recebe resumos. Construído com Laravel 12, Livewire 3 e Blade.

## Stack

- Laravel 12 + Livewire 3 + Blade
- Tailwind CSS
- MySQL (produção) e SQLite (testes)
- Filament v3 (admin)
- OpenAI via HTTP (NLU)

## Principais recursos

- Landing moderna com planos dinâmicos do banco
- Cadastro e login por email ou telefone
- Confirmação de WhatsApp (mock)
- Checkout mock com criação de assinatura e trial
- Catálogo de planos + itens de plano (features)
- Dashboard financeiro com transações e dívidas
- Webhook WhatsApp com interpretação por LLM
- Logs de auditoria e rate limit por mensagens/dia
- Admin panel com métricas e gestão de contratos

## Requisitos

- PHP 8.4+
- Composer
- Node.js + npm
- MySQL (ou SQLite para testes)

## Setup rápido

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
```

Configure o banco no `.env` e rode:

```bash
php artisan migrate --seed
php artisan serve
```

## Variáveis de ambiente importantes

```env
APP_URL=http://127.0.0.1:8000

# Banco
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

# Admin (Filament)
ADMIN_EMAIL=admin@falabeto.test
ADMIN_PASSWORD=secret

# OpenAI (NLU)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4.1-mini
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_TIMEOUT=20

# WhatsApp Webhook (mock)
WHATSAPP_WEBHOOK_SECRET=changeme
LOG_MESSAGE_BODY=false

# Stripe (stub)
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

## Rotas principais

- `/` Landing
- `/register` Cadastro
- `/login` Login
- `/whatsapp/verify` Confirmação WhatsApp (mock)
- `/plans` Escolha de plano
- `/success` Pós-checkout
- `/dashboard` Área do cliente
- `/admin` Admin (Filament)

## Webhook WhatsApp (mock)

Endpoint:

```txt
POST /api/webhooks/whatsapp
```

Header obrigatório:

```txt
X-WEBHOOK-SECRET: <WHATSAPP_WEBHOOK_SECRET>
```

Payload mínimo:

```json
{
  "phone": "+5511999999999",
  "text": "gastei 12 no mercado",
  "timestamp": "2026-01-31T12:00:00-03:00"
}
```

Resposta:

```json
{
  "reply_text": "Anotado! Mercado R$ 12...",
  "action": "create_transaction",
  "data": {}
}
```

## Testes

Os testes usam SQLite em memória (configurado no `phpunit.xml`).

```bash
./vendor/bin/pest
```

## Admin (Filament)

- Login em `/admin`
- Guard separado para `admins`
- CRUD de planos com itens do plano (relation manager)
- Gestão de assinaturas e métricas

## Observações

- Campos textuais sensíveis são criptografados.
- Logs de mensagens respeitam `LOG_MESSAGE_BODY`.
- Webhook faz fast-path antes de chamar LLM.


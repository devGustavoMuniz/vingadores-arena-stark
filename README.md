# Arena Stark

Sistema de venda de ingressos (tiqueteria) com foco em desempenho via cache,
usando Redis para controle de concorrência de estoque e Laravel + Inertia +
React na aplicação.

[![CI](https://github.com/devGustavoMuniz/vingadores-arena-stark/actions/workflows/ci.yml/badge.svg)](https://github.com/devGustavoMuniz/vingadores-arena-stark/actions/workflows/ci.yml)

## Problema

Overselling (venda duplicada do mesmo ingresso) em cenários de alta
concorrência quando um evento popular abre vendas, além de sobrecarga do
banco de dados causada por consultas repetidas ao catálogo de eventos.

## Como rodar localmente

### Pré-requisitos

- Docker e Docker Compose instalados

### Passos

```bash
# 1. Clone o repositório e entre na pasta
git clone https://github.com/devGustavoMuniz/vingadores-arena-stark.git
cd vingadores-arena-stark

# 2. Copie o arquivo de ambiente
cp arena-stark/.env.example arena-stark/.env

# 3. Suba os containers (MySQL + Redis + App + Nginx + Node)
#    O primeiro start demora: o app roda `composer install` e o node roda `npm ci`
docker compose up -d

# 4. Gere a chave da aplicação
docker compose exec app php artisan key:generate

# 5. Execute as migrations e seeders (idempotentes: podem rodar mais de uma vez)
#    Aguarde o app terminar o `composer install` (acompanhe com `docker compose logs -f app`)
docker compose exec app php artisan migrate --seed

# 6. Acesse a aplicação
# http://localhost:8000
```

### Rodar os testes

Os testes de Inventory, Orders e Notifications usam um Redis real em
`127.0.0.1:6379` (o mesmo serviço do CI). Localmente, suba só o Redis:

```bash
docker compose up -d redis
cd arena-stark
php artisan test
```

## Arquitetura (Sprint 1)

Monólito modular em Laravel, com três contextos de domínio em `arena-stark/app/`
([ADR-0001](docs/adr/0001-monolito-modular-contextos-dominio.md)):

| Contexto | Responsabilidade |
|---|---|
| `Inventory` | Eventos, ingressos e controle de estoque (cache-aside e reserva atômica no Redis) |
| `Orders` | Fluxo de compra: reserva, confirmação e pedido |
| `Notifications` | Publicação de eventos de pedido confirmado no Redis Streams |

### Fluxo ponta a ponta: compra de ingresso

1. `GET /events` e `GET /events/{id}`: o comprador consulta os eventos (Inertia/React).
2. `POST /orders/reserve`: o `InventoryService` decrementa o estoque no Redis com
   um script Lua atômico e cria uma reserva com TTL de 10 minutos.
3. `POST /orders/confirm/{token}`: o `OrderService` consome a reserva e, em uma
   transação, registra a venda no `Inventory` (ingresso vendido + baixa de
   estoque no MySQL) e cria o pedido.
4. O `NotificationService` publica `order.confirmed` no stream
   `notifications:order-confirmed` (Redis Streams).
5. `GET /orders/success/{order}`: página de sucesso da compra.

Um consumidor do stream (futuro microsserviço de notificações) e a etapa de
pagamento ficam para as próximas sprints.

## Documentação

- [Visão de produto](docs/visao-produto.md)
- [Equipe](docs/equipe.md)
- [ADRs](docs/adr/)
  - [ADR-0000](docs/adr/0000-registro-de-decisoes.md) — Registro de decisões arquiteturais
  - [ADR-0001](docs/adr/0001-monolito-modular-contextos-dominio.md) — Monólito modular com contextos de domínio
  - [ADR-0002](docs/adr/0002-mysql-banco-de-dados-principal.md) — MySQL como banco de dados principal
  - [ADR-0003](docs/adr/0003-redis-cache-estoque-atomico.md) — Redis para cache e controle atômico de estoque


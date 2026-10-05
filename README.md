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
docker compose up -d

# 4. Gere a chave da aplicação
docker compose exec app php artisan key:generate

# 5. Execute as migrations e seeders
docker compose exec app php artisan migrate --seed

# 6. Acesse a aplicação
# http://localhost:8000
```

### Rodar os testes (requer Redis local ou via Docker)

```bash
cd arena-stark
php artisan test
```

## Documentação

- [Visão de produto](docs/visao-produto.md)
- [Equipe](docs/equipe.md)
- [ADRs](docs/adr/)
  - [ADR-0000](docs/adr/0000-registro-de-decisoes.md) — Registro de decisões arquiteturais
  - [ADR-0001](docs/adr/0001-monolito-modular-contextos-dominio.md) — Monólito modular com contextos de domínio
  - [ADR-0002](docs/adr/0002-mysql-banco-de-dados-principal.md) — MySQL como banco de dados principal
  - [ADR-0003](docs/adr/0003-redis-cache-estoque-atomico.md) — Redis para cache e controle atômico de estoque


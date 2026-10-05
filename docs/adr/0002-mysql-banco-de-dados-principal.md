# 0002. MySQL como Banco de Dados Principal

- Status: aceito
- Data: 2026-10-05

## Contexto

O projeto precisa de um banco de dados relacional para persistir Eventos, Ingressos e Pedidos.
As opções consideradas foram MySQL, PostgreSQL e SQLite.

O CI já usa SQLite para testes (por ser zero-config), mas o ambiente Docker de desenvolvimento
e produção precisam de um banco robusto que suporte concorrência real.

## Decisão

Adotar **MySQL 8.4** como banco de dados principal para os ambientes de desenvolvimento
(Docker Compose) e produção.

SQLite **continua sendo usado apenas no CI** (`php artisan test`) por ser mais rápido
e não exigir serviço externo no runner do GitHub Actions.

## Justificativa da escolha (MySQL sobre PostgreSQL)

- O time tem mais experiência prévia com MySQL.
- Laravel tem suporte maduro para ambos — a troca futura seria de baixo custo.
- O workload do Arena Stark (leituras de catálogo + escritas transacionais de pedidos)
  não exige features específicas do PostgreSQL (como JSONB nativo avançado ou CTEs
  recursivos) neste estágio.

## Consequências

- O `.env.example` oferece configuração comentada para MySQL (DB_HOST, DB_PORT etc.)
  e SQLite como padrão para o CI.
- O `docker-compose.yml` sobe MySQL 8.4 com healthcheck.
- Qualquer futura migração para PostgreSQL exige apenas trocar o driver e revisar
  tipos de coluna específicos de banco.

# 0000. Registro de decisões arquiteturais (ADR)

- Status: aceito
- Data: 2026-09-03

## Contexto

O projeto vai evoluir de uma aplicação Laravel monolítica para uma
arquitetura de microsserviços (Inventory, Order, Notification), com Redis
cumprindo papéis distintos (cache-aside, controle atômico de estoque,
reservas com TTL, filas via Redis Streams). Decisões técnicas tomadas sem
registro tendem a se perder, dificultam o onboarding de novos integrantes e
geram retrabalho de discussão quando a mesma dúvida reaparece.

## Decisão

Adotar Architecture Decision Records (ADR) para toda decisão técnica
relevante do projeto.

- Os ADRs ficam numerados sequencialmente em `docs/adr/`, no formato
  `NNNN-titulo-curto.md`.
- Cada ADR tem um dos seguintes status: **proposto**, **aceito** ou
  **substituído** (indicando, quando aplicável, qual ADR o substitui).
- Uma decisão **merece** virar ADR quando envolve, por exemplo: escolha de
  banco de dados, escolha de padrão de cache, escolha entre fila vs
  pub/sub, ou mudança de stack.

## Consequências

Toda decisão relevante precisa ser documentada em um ADR antes de ser
mergeada. Pull requests que introduzem uma decisão arquitetural sem ADR
associado podem ser rejeitados em code review.

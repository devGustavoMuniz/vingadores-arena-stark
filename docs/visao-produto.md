# Visão de produto — Arena Stark

## Problema

Overselling (venda duplicada do mesmo ingresso) em cenários de alta
concorrência quando um evento popular abre vendas, além de sobrecarga do
banco de dados causada por consultas repetidas ao catálogo de eventos
(listagens, detalhes, disponibilidade).

## Para quem

- **Comprador final**: precisa de uma experiência de compra rápida e
  confiável, sem erro de "ingresso já vendido" depois de finalizar o
  checkout.
- **Organizador do evento / plataforma**: precisa evitar prejuízo com vendas
  duplicadas e evitar queda do sistema sob pico de acesso.

## Transação que atravessa contextos

Usuário solicita compra → Order Service aciona Inventory Service → Inventory
Service faz decremento atômico de estoque no Redis e cria reserva temporária
com TTL → Order Service aciona Payment Service → se aprovado, Order Service
persiste o pedido no banco e publica evento de confirmação (Redis Streams) →
Notification Service consome o evento e notifica o usuário → Inventory
Service confirma baixa definitiva do estoque.

Essa transação atravessa: aplicação (API) → cache/controle de concorrência
(Redis) → banco de dados (persistência) → fila assíncrona (Redis Streams) →
outro serviço consumidor.

## Fora de escopo (nesta primeira entrega)

Nesta primeira entrega (desafio-01) o objetivo é estruturar o repositório e
o processo — README, licença, documentação de equipe, visão de produto, ADR
fundador e pipeline de CI. A lógica de negócio (cache-aside, controle
atômico de estoque, reservas com TTL, filas com Redis Streams e a separação
em microsserviços) será implementada nos desafios seguintes.

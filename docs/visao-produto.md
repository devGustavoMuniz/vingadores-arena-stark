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
com TTL → usuário confirma a compra → Order Service registra a venda no
Inventory Service (baixa definitiva do estoque) e persiste o pedido no banco →
Order Service publica evento de confirmação (Redis Streams) → Notification
Service consome o evento e notifica o usuário.

Visão final: entre a reserva e a confirmação haverá uma etapa de pagamento
(Payment Service), ainda não implementada.

Essa transação atravessa: aplicação (API) → cache/controle de concorrência
(Redis) → banco de dados (persistência) → fila assíncrona (Redis Streams) →
outro serviço consumidor.

## Escopo por entrega

**Desafio 01 (fundação do processo):** README, licença, equipe, visão de
produto, ADR fundador e pipeline de CI.

**Sprint 1 (fundação técnica):** monólito modular com três contextos
(Inventory, Orders, Notifications), fluxo de compra ponta a ponta (listar
evento, reservar, confirmar, pedido, notificação), testes automatizados,
Docker Compose (MySQL, Redis, app, Nginx, Node), CI e ADRs 0000 a 0003.

**Fora de escopo até aqui:** etapa de pagamento, consumidor do stream de
notificações (envio real ao usuário), expiração/liberação automática de
reservas e a separação em microsserviços.

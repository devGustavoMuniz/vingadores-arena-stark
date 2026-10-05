# 0003. Redis para Cache de Estoque e Controle Atômico via Lua

- Status: aceito
- Data: 2026-10-05

## Contexto

O principal problema de negócio do Arena Stark é **overselling**: vender o mesmo ingresso
para dois compradores simultâneos quando um evento popular abre vendas.

Soluções tradicionais com banco relacional (SELECT + UPDATE dentro de uma transação)
sofrem de contenção de lock em alta concorrência, podendo degradar o throughput do sistema.

## Decisão

Usar **Redis** para dois propósitos distintos no contexto Inventory:

### 1. Cache-Aside de Estoque

A quantidade de ingressos disponíveis (`available_tickets`) é lida com frequência (cada
listagem de evento, cada page view de detalhe). Para evitar consultas repetidas ao MySQL,
o `InventoryService` implementa o padrão **cache-aside**:

```
GET inventory:stock:{event_id}
  → cache hit → retorna valor Redis
  → cache miss → lê MySQL → escreve Redis (TTL 5 min) → retorna
```

### 2. Reserva Atômica com Script Lua

A reserva de um ingresso é feita via **script Lua executado atomicamente no Redis**,
garantindo que o decremento e a verificação de estoque sejam uma operação indivisível:

```lua
local stock = redis.call('GET', KEYS[1])
if tonumber(stock) <= 0 then return 0 end
return redis.call('DECR', KEYS[1])
```

Após reserva bem-sucedida, um **token de reserva** é persistido no Redis com TTL de
**10 minutos** (`inventory:reservation:{token}`). O usuário deve confirmar a compra
dentro desse prazo, caso contrário o estoque é liberado.

### 3. Redis Streams para Notificações

O `NotificationService` publica eventos de confirmação de pedido no stream
`notifications:order-confirmed`. Isso desacopla o contexto Orders do contexto Notifications
e prepara a base para a extração futura do serviço de notificações.

## Consequências

- **Positivo**: Elimina overselling em cenários de alta concorrência (operação atômica).
- **Positivo**: Reduz carga no MySQL para leituras de catálogo (cache-aside).
- **Positivo**: Redis Streams prepara a base para a arquitetura event-driven.
- **Negativo**: Introduz Redis como dependência obrigatória (mitigado pelo Docker Compose).
- **Negativo**: Requer estratégia de reconvergência entre Redis e MySQL (TTL expirado →
  estoque não foi confirmado → necessário job de cleanup periódico — **fora do escopo do Sprint 1**).
- O Redis é configurado com `appendonly yes` para persistência mínima no ambiente Docker.

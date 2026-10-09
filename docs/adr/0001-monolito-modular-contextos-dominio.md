# 0001. Monólito Modular com Contextos de Domínio

- Status: aceito
- Data: 2026-10-05

## Contexto

O sistema Arena Stark precisa evoluir para microsserviços no futuro, mas implementar
microsserviços desde o início traz complexidade operacional desnecessária para a fase de
Sprint 1 (fundação). O time é pequeno (4 pessoas) e precisa validar o domínio rapidamente.

A visão de produto define três contextos claros: **Inventory** (eventos e estoque),
**Orders** (pedidos de compra) e **Notifications** (notificações assíncronas).

## Decisão

Adotar **Monólito Modular** como arquitetura inicial, onde cada contexto de domínio
vive em seu próprio namespace dentro do monólito Laravel:

```
app/
├── Inventory/          # Eventos, ingressos, controle de estoque
│   ├── Http/Controllers/
│   ├── Models/
│   └── Services/
├── Orders/             # Pedidos, reservas, confirmações
│   ├── Http/Controllers/
│   ├── Http/Requests/
│   ├── Models/
│   └── Services/
└── Notifications/      # Publicação de eventos via Redis Streams
    └── Services/
```

Regras de comunicação entre contextos:

- Um contexto **não acessa diretamente** os Models de outro contexto.
- A comunicação entre contextos se dá via **Services** injetados ou via **Redis Streams**.
- O `OrderService` pode invocar o `InventoryService`, mas não acessa `Inventory\Models`
  diretamente além das referências de FK necessárias para o Eloquent (relações do
  model `Order`). A persistência do ingresso vendido e a baixa de estoque no banco
  ficam no `InventoryService::registerSale()`, que o `OrderService` chama dentro da
  sua transação.

## Consequências

- **Positivo**: Permite extrair um contexto para microsserviço independente futuramente
  sem refatoração total — os limites já estão definidos.
- **Positivo**: Menor overhead operacional no Sprint 1 (sem orquestração de serviços).
- **Negativo**: Requer disciplina da equipe para não "vazar" dependências entre contextos.
- **Negativo**: Ao crescer, o monólito pode se tornar difícil de escalar horizontalmente
  sem a separação. Este risco é mitigado pelas fronteiras claras de módulo.

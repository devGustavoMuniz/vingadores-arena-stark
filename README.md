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

Em construção — ver [docs/visao-produto.md](docs/visao-produto.md).

## Documentação

- [Visão de produto](docs/visao-produto.md)
- [Equipe](docs/equipe.md)
- [ADRs](docs/adr/)

# Design Patterns Utilizados

Referencia: https://refactoring.guru/pt-br/design-patterns/php

## Strategy Pattern

**Onde:** Motor de avaliacao

- `GradeCalculator` com strategies: NumericCalculator, ConceptCalculator, DescriptiveCalculator
- `AverageFormula` com strategies: ArithmeticAverage, WeightedAverage, CustomAverage
- Permite trocar a logica de calculo sem alterar o codigo consumidor

## Observer Pattern

**Onde:** Audit trail e notificacoes

- Eloquent observers para capturar alteracoes em entidades sensiveis
- Eventos de dominio disparados em use cases (EnrollmentCreated, GradeUpdated, PeriodClosed)
- Listeners desacoplados reagem aos eventos (gravar audit, enviar notificacao)

## Repository Pattern

**Onde:** Todas as entidades de dominio

- Interface no Domain layer
- Implementacao Eloquent no Infrastructure layer
- Desacopla dominio do ORM

## State Pattern

**Onde:** Workflows com status

- AcademicYear: planning → active → closing → closed
- PeriodClosing: pendente → em_validacao → aprovado → fechado
- Enrollment: ativa → transferida → cancelada → concluida

## Template Method

**Onde:** Geracao de documentos PDF

- Classe base define o fluxo de geracao (header, body, footer)
- Subclasses implementam o conteudo especifico (boletim, historico, ata)

## Specification Pattern

**Onde:** Validacoes complexas de dominio

- Regras de fechamento (todas notas lancadas? frequencia completa?)
- Regras de matricula (aluno ja enturmado nesse turno?)
- Composicao de specifications (AND, OR, NOT)

## Builder Pattern

**Onde:** Queries complexas de relatorios

- Construcao de relatorios com filtros dinamicos
- Exportacoes com selecao de campos

## Regra Geral

- NAO criar patterns prematuros
- Aplicar apenas quando houver necessidade real (3+ variacoes, complexidade justificavel)
- Preferir simplicidade: 3 linhas repetidas > abstracao prematura

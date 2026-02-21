# Ciclo de Vida da Matricula

## Conceito

A matricula e o vinculo formal entre o aluno e a unidade escolar para um ano letivo especifico.
Cada aluno possui uma matricula por escola/ano letivo. A matricula e a entidade central que conecta
o aluno a escola, turma, frequencia, avaliacoes e documentos.

## Status e Transicoes

```
                  ┌──────────────┐
                  │    active     │◄────────────────────┐
                  └──────┬───────┘                      │
                         │                              │
          ┌──────────────┼──────────────┐               │
          ▼              ▼              ▼               │
   ┌─────────────┐ ┌───────────┐ ┌───────────┐         │
   │ transferred │ │ cancelled │ │ abandoned │         │
   └─────────────┘ └─────┬─────┘ └───────────┘         │
                         │                              │
                         └──────── reativar ────────────┘

                  ┌─────────────┐
                  │  completed  │  (fim do ano letivo)
                  └─────────────┘
```

| Status | Significado | Transicoes possiveis |
|--------|-------------|---------------------|
| `active` | Aluno regularmente matriculado | cancelled, transferred, abandoned, completed |
| `cancelled` | Matricula cancelada (erro administrativo, desistencia antes de frequentar) | active (reativacao) |
| `transferred` | Aluno transferido para outra escola | -- (terminal, gera nova matricula na escola destino) |
| `abandoned` | Abandono escolar confirmado apos busca ativa | -- (terminal) |
| `completed` | Ano letivo concluido (aprovado ou retido) | -- (terminal) |

## Tipos de Matricula

| Tipo | Codigo | Quando usar |
|------|--------|-------------|
| Matricula Nova | `new_enrollment` | Primeiro ingresso na rede ou na escola |
| Rematricula | `re_enrollment` | Renovacao automatica para o proximo ano letivo (mesma escola) |
| Transferencia Recebida | `transfer_received` | Aluno vindo de outra escola (interna ou externa a rede) |

## Regras de Negocio

### Cancelamento
- Apenas matriculas `active` podem ser canceladas
- Ao cancelar, todas as enturmacoes ativas sao canceladas automaticamente (status → cancelled, end_date → hoje)
- Define `exit_date` como a data atual
- Caso de uso: erro de digitacao, duplicidade, desistencia antes do inicio das aulas

### Reativacao
- Apenas matriculas `cancelled` podem ser reativadas
- Ao reativar, o status volta para `active` e `exit_date` e limpo
- As enturmacoes anteriormente canceladas NAO sao reativadas automaticamente (o usuario deve enturmar manualmente se necessario, pois as vagas podem ter sido preenchidas)
- Caso de uso: cancelamento indevido, aluno que retorna rapidamente

### Unicidade
- Um aluno pode ter apenas uma matricula ativa por escola/ano letivo
- A validacao ocorre no momento da criacao (CreateEnrollmentUseCase)

### Numero da Matricula
- Gerado automaticamente no formato: ANO + ID sequencial (ex: 2026-00042)
- Unico por ano letivo
- Serve como identificador para comunicacao com responsaveis

## Dados da Matricula

| Campo | Tipo | Obrigatorio | Descricao |
|-------|------|-------------|-----------|
| student_id | FK | Sim | Aluno vinculado |
| academic_year_id | FK | Sim | Ano letivo |
| school_id | FK | Sim | Escola |
| enrollment_number | string | Sim | Numero unico gerado |
| enrollment_type | enum | Sim | new_enrollment, re_enrollment, transfer_received |
| status | enum | Sim | active, cancelled, transferred, abandoned, completed |
| enrollment_date | date | Sim | Data efetiva da matricula |
| exit_date | date | Nao | Data de saida (cancelamento, transferencia, abandono) |

## Fluxo Operacional

1. **Secretaria** cadastra o aluno (modulo People)
2. **Secretaria** cria a matricula vinculando aluno + escola + ano letivo
3. **Secretaria** enturma o aluno (vincula a uma turma)
4. **Professor** registra frequencia e avaliacoes
5. **Ao final do ano**, matricula e finalizada como `completed`
6. **Rematricula** automatica gera nova matricula para o proximo ano

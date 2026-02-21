# Enturmacao (Class Assignment)

## Conceito

Enturmacao e o ato de vincular um aluno matriculado a uma turma (class_group) especifica.
Um aluno so pode frequentar aulas, ter frequencia registrada e receber avaliacoes se estiver enturmado.
A enturmacao e o elo entre a matricula e a turma.

## Relacao com Matricula

```
Matricula (1) ──── (N) Enturmacao ──── (1) Turma
```

Uma matricula pode ter multiplas enturmacoes ao longo do ano letivo (ex: transferencia entre turmas),
mas apenas UMA enturmacao ativa por turno simultaneamente.

## Status da Enturmacao

| Status | Significado |
|--------|-------------|
| `active` | Aluno frequentando normalmente esta turma |
| `transferred` | Aluno foi transferido para outra turma ou escola |
| `cancelled` | Enturmacao cancelada (erro, cancelamento da matricula) |

## Regras de Negocio

### Criacao
- A matricula deve estar ativa para enturmar
- Verifica capacidade maxima da turma (max_students) — gera **warning** se excede, mas nao bloqueia
- Se ja existe enturmacao ativa, a anterior e encerrada automaticamente (status → transferred, end_date → hoje)
- Cria movimentacao de tipo `matricula_inicial` se for a primeira enturmacao

### Unicidade por Turno
- Aluno pode ter apenas uma enturmacao ativa por turno
- Excepcao: aluno integral pode ter enturmacoes em turnos diferentes (manha + tarde)
- Validacao ocorre no AssignToClassUseCase

### Edicao
- Turma, data inicio, data fim e status podem ser editados
- Caso de uso: correcao de erro na turma atribuida, ajuste de data

### Exclusao
- Exclusao fisica (hard delete) — usada quando a enturmacao foi criada por erro
- Diferente de cancelamento (que e uma alteracao de status)

### Ao Cancelar Matricula
- Todas as enturmacoes ativas sao canceladas automaticamente
- end_date e definido como a data atual

### Ao Reativar Matricula
- Enturmacoes canceladas NAO sao reativadas automaticamente
- O usuario deve criar uma nova enturmacao manualmente

## Controle de Vagas

| Campo da Turma | Descricao |
|----------------|-----------|
| max_students | Capacidade maxima da turma |
| active_students_count | Contagem calculada de enturmacoes ativas |

- O sistema exibe no formato "X/Y vagas" ao enturmar
- Exceder o maximo gera warning (aviso), nao erro — a decisao e da gestao escolar
- Razao: situacoes reais (demanda judicial, falta de vaga na rede) exigem flexibilidade

## Dados da Enturmacao

| Campo | Tipo | Obrigatorio | Descricao |
|-------|------|-------------|-----------|
| enrollment_id | FK | Sim | Matricula vinculada |
| class_group_id | FK | Sim | Turma vinculada |
| status | enum | Sim | active, transferred, cancelled |
| start_date | date | Sim | Data de inicio na turma |
| end_date | date | Nao | Data de saida da turma |

## Impacto em Outros Modulos

| Modulo | Dependencia |
|--------|-------------|
| Frequencia | So registra presenca/falta de alunos com enturmacao ativa na turma |
| Avaliacao | Notas vinculadas ao aluno via enturmacao ativa |
| Diario de Classe | Lista de alunos da turma vem das enturmacoes ativas |
| Fechamento | Verifica completude de lancamentos para todos os alunos enturmados |

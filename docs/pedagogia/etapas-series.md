# Etapas e Series Educacionais

## Estrutura da Educacao Basica Brasileira

```
Educacao Basica
├── Educacao Infantil (0 a 5 anos)
│   ├── Creche (0 a 3 anos)
│   │   ├── Bercario I  — 0 a 11 meses
│   │   ├── Bercario II — 1 ano a 1 ano e 11 meses
│   │   ├── Maternal I  — 2 anos a 2 anos e 11 meses
│   │   └── Maternal II — 3 anos a 3 anos e 11 meses
│   └── Pre-escola (4 a 5 anos) — OBRIGATORIA
│       ├── Pre I  — 4 anos a 4 anos e 11 meses
│       └── Pre II — 5 anos a 5 anos e 11 meses
│
└── Ensino Fundamental (6 a 14 anos) — 9 anos
    ├── Anos Iniciais (1o ao 5o ano)
    │   ├── 1o ano — 6 anos (ingresso)
    │   ├── 2o ano — 7 anos
    │   ├── 3o ano — 8 anos
    │   ├── 4o ano — 9 anos
    │   └── 5o ano — 10 anos
    └── Anos Finais (6o ao 9o ano)
        ├── 6o ano — 11 anos
        ├── 7o ano — 12 anos
        ├── 8o ano — 13 anos
        └── 9o ano — 14 anos
```

## Responsabilidade Municipal (Jandira-SP)

A rede municipal atende:
- Educacao Infantil completa (creche + pre-escola)
- Ensino Fundamental completo (1o ao 9o ano)
- **NAO** atende Ensino Medio (responsabilidade estadual)

## Data de Corte

**Regra vigente (STF, 2018 + Resolucao CNE/CEB 2/2018):**

A crianca deve completar a idade ate **31 de marco** do ano de ingresso.

| Etapa | Idade de ingresso | Data de corte |
|-------|-------------------|---------------|
| Bercario I | 0 anos | Completa 0 ate 31/03 |
| Bercario II | 1 ano | Completa 1 ate 31/03 |
| Maternal I | 2 anos | Completa 2 ate 31/03 |
| Maternal II | 3 anos | Completa 3 ate 31/03 |
| Pre I | 4 anos | Completa 4 ate 31/03 |
| Pre II | 5 anos | Completa 5 ate 31/03 |
| 1o ano EF | 6 anos | Completa 6 ate 31/03 |

**Exemplo:** Crianca nascida em 15/04/2020 completa 6 anos em 15/04/2026.
Como a data e posterior a 31/03/2026, esta crianca so ingressa no 1o ano em 2027.

**Implicacao no sistema:** A validacao de data de corte deve ser configuravel (o municipio pode
ter regra complementar via CME), mas o padrao e 31/03.

## Progressao entre Etapas

### Educacao Infantil
- **Nao ha retencao** (LDB Art. 31)
- Progressao automatica por idade
- Ao completar Pre II, o aluno e encaminhado para o 1o ano do Ensino Fundamental

### Ensino Fundamental — Bloco Pedagogico (1o ao 3o ano)
- 1o, 2o e 3o ano formam o ciclo de alfabetizacao
- **Progressao continuada** dentro do bloco (sem retencao)
- Retencao possivel apenas ao final do 3o ano (e por frequencia a qualquer momento)

### Ensino Fundamental — 4o ao 9o ano
- Avaliacao por componente curricular
- Retencao possivel ao final de cada ano por:
  - Aproveitamento insuficiente (abaixo da media)
  - Frequencia abaixo de 75%
- Conselho de Classe pode aprovar aluno abaixo da media (com justificativa)

## Modelo de Docencia por Etapa

### Educacao Infantil + Anos Iniciais (1o ao 5o ano) — Professor Polivalente

- **Um unico professor** leciona todas (ou quase todas) as disciplinas da turma
- Esse professor e chamado de **professor polivalente** ou **professor regente**
- O mesmo professor ministra Portugues, Matematica, Ciencias, Historia, Geografia etc.
- Pode haver professores especialistas para disciplinas especificas (Ed. Fisica, Arte, Ingles), mas o professor principal e um so
- No sistema: uma turma dos anos iniciais tera **poucas atribuicoes** (teacher_assignments), normalmente 1 professor regente com multiplos componentes curriculares. Na Ed. Infantil usa-se campos de experiencia ao inves de componentes curriculares

### Anos Finais (6o ao 9o ano) — Professor Especialista

- **Um professor por disciplina/componente curricular**
- Cada professor e especialista na sua area (Matematica, Portugues, Ciencias, Historia, Geografia, Ingles, Ed. Fisica, Arte etc.)
- O professor circula entre varias turmas lecionando sua disciplina
- No sistema: uma turma dos anos finais tera **muitas atribuicoes** (teacher_assignments), uma por componente curricular, cada uma com um professor diferente

### Ensino Medio (referencia — nao atendido pela rede municipal)

- Mesmo modelo dos anos finais: um professor por disciplina
- Nao se aplica ao sistema atual (responsabilidade estadual)

### Implicacoes no Sistema

| Aspecto | Anos Iniciais (1o-5o) | Anos Finais (6o-9o) |
|---------|----------------------|---------------------|
| Professor por turma | 1 regente (+ especialistas) | 1 por disciplina |
| Atribuicoes por turma | Poucas (1-3) | Muitas (8-12) |
| Chamada | Professor regente faz para todas as aulas | Cada professor faz para sua aula |
| Diario de classe | Professor regente registra multiplas disciplinas | Cada professor registra a sua |
| "Minhas Aulas" | Professor regente ve poucas turmas, cada uma com varias disciplinas | Professor especialista ve varias turmas, cada uma com 1 disciplina |
| Avaliacao | Professor regente avalia em todos os componentes | Cada professor avalia so no seu componente |

Essa distincao e padrao nacional (LDB, Resolucoes CNE) e impacta diretamente a experiencia do professor no sistema.

## Rematricula

- Realizada antes do inicio do ano letivo seguinte
- **Prioridade:** alunos ja matriculados na escola tem preferencia sobre novos alunos
- Automatizada pelo sistema (tipo `re_enrollment`)
- Aluno permanece na mesma escola salvo solicitacao de transferencia

## Georreferenciamento (alocacao por endereco)

Para matriculas novas, a SME utiliza georreferenciamento para alocar o aluno
na escola mais proxima de sua residencia. O sistema deve suportar:
- Cadastro de endereco do aluno
- Vinculo entre escola e area de abrangencia
- Sugestao de escola baseada no endereco (futuro)

## Turmas por Etapa

| Etapa | Nomenclatura tipica | Capacidade padrao |
|-------|---------------------|-------------------|
| Bercario I | BI-A, BI-B | 7-10 alunos |
| Bercario II | BII-A, BII-B | 10-12 alunos |
| Maternal I | MI-A, MI-B | 12-15 alunos |
| Maternal II | MII-A, MII-B | 15-20 alunos |
| Pre I | PI-A, PI-B | 20-25 alunos |
| Pre II | PII-A, PII-B | 20-25 alunos |
| 1o ao 5o ano | 1A, 1B, 2A, 2B... | 25-30 alunos |
| 6o ao 9o ano | 6A, 6B, 7A, 7B... | 30-35 alunos |

A capacidade maxima depende da infraestrutura da escola e de regulamentacao municipal.
O sistema usa o campo `max_students` da turma, configuravel pela gestao.

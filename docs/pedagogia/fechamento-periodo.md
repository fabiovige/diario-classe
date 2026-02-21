# Fechamento de Periodo

## Conceito

O fechamento de periodo e o processo formal de consolidar notas e frequencia de um bimestre/trimestre/semestre,
validar a completude dos lancamentos e oficializar os resultados. Apos o fechamento, os dados se tornam imutaveis
(somente alteraveis via retificacao formal).

## Periodos Avaliativos

| Tipo | Quantidade | Duracao aproximada |
|------|------------|-------------------|
| Bimestre | 4 por ano | ~50 dias letivos cada |
| Trimestre | 3 por ano | ~67 dias letivos cada |
| Semestre | 2 por ano | ~100 dias letivos cada |

O tipo de periodo e configuravel por escola/ano letivo via `assessment_periods`.
O padrao na rede municipal de Jandira e **bimestral**.

## Workflow de Fechamento

```
pending ──► in_validation ──► approved ──► closed
                │                              │
                ▼                              ▼
           (rejeitado →                   (retificacao
            volta para                     formal com
            pending)                       justificativa)
```

| Status | Responsavel | Acao |
|--------|-------------|------|
| `pending` | Professor | Lanca notas e frequencia. Pode editar livremente |
| `in_validation` | Professor submete | Solicita validacao do coordenador/diretor |
| `approved` | Coordenador/Diretor | Valida que tudo esta correto. Pode rejeitar (volta a pending) |
| `closed` | Diretor | Fecha oficialmente o periodo. Dados se tornam imutaveis |

## Checagem de Completude

Antes de submeter para validacao, o sistema executa verificacoes automaticas:

| Verificacao | Regra |
|-------------|-------|
| Frequencia completa | Todos os dias letivos do periodo tem frequencia lancada para a turma |
| Notas completas | Todos os alunos enturmados tem nota lancada em todos os componentes |
| Medias calculadas | Media do periodo calculada para todos os alunos |
| Instrumentos | Todos os instrumentos avaliativos previstos tem registros |

Se alguma verificacao falhar, a submissao e bloqueada e o sistema lista as pendencias.

## Imutabilidade

Apos o status `closed`:
- Notas NAO podem ser alteradas
- Frequencia NAO pode ser alterada
- Enturmacoes do periodo NAO podem ser modificadas
- Qualquer alteracao requer retificacao formal

## Retificacao

| Aspecto | Regra |
|---------|-------|
| Quem solicita | Professor ou coordenador |
| Justificativa | Obrigatoria (texto livre) |
| Aprovacao | Diretor ou admin deve aprovar a retificacao |
| Registro | Audit trail completo (quem solicitou, quem aprovou, valores anteriores, novos valores) |
| Tipos | Correcao de nota, correcao de frequencia, inclusao de aluno |

## Resultado Final (apos 4o bimestre)

Apos o fechamento do ultimo periodo avaliativo, o sistema calcula o resultado final:

### Calculo da Media Anual

```
media_anual = (B1 + B2 + B3 + B4) / 4        (aritmetica simples)
media_anual = (B1*P1 + B2*P2 + B3*P3 + B4*P4) / (P1+P2+P3+P4)  (ponderada)
```

### Criterios de Aprovacao

| Criterio | Regra |
|----------|-------|
| Aproveitamento | Media anual >= media minima configurada (ex: 6.0) |
| Frequencia | >= 75% das horas letivas totais |
| Ambos devem ser atendidos | Aluno pode ser retido por falta mesmo com nota alta |

### Resultado Final

| Resultado | Codigo | Condicao |
|-----------|--------|----------|
| Aprovado | `approved` | Media >= minimo E frequencia >= 75% |
| Retido | `retained` | Media < minimo |
| Retido por Falta | `retained_by_absence` | Frequencia < 75% |
| Aprovado pelo Conselho | `council_approved` | Media < minimo mas conselho aprovou |

### Excecoes
- **Bloco pedagogico (1o ao 3o ano):** sem retencao por nota, apenas por frequencia
- **Educacao Infantil:** sem retencao de nenhum tipo
- **Conselho de Classe:** pode aprovar aluno com media insuficiente mediante justificativa formal

## Fluxo Temporal no Ano Letivo

```
Fev ─────── Abr ─────── Jun ─────── Set ─────── Nov ─────── Dez
 │           │           │           │           │           │
 ▼           ▼           ▼           ▼           ▼           ▼
Inicio    Fechamento  Fechamento  Fechamento  Fechamento  Resultado
aulas     1o Bim      2o Bim      3o Bim      4o Bim      Final
                                                           + Conselho
                                                           + Rematricula
```

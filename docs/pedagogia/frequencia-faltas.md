# Frequencia e Controle de Faltas

## Conceito

O controle de frequencia e obrigacao legal do professor (LDB Art. 13) e da escola (LDB Art. 12).
O sistema deve registrar a presenca/ausencia de cada aluno por aula, calcular percentuais
e gerar alertas automaticos quando limiares sao atingidos.

## Status de Presenca

| Status | Codigo | Descricao |
|--------|--------|-----------|
| Presente | `present` | Aluno presente na aula |
| Ausente | `absent` | Falta nao justificada |
| Falta Justificada | `justified_absence` | Falta com justificativa aprovada (atestado, etc) |
| Dispensado | `excused` | Dispensa formal (atividade externa, competicao, etc) |

## Frequencia Minima por Nivel

| Nivel | Frequencia Minima | Base Legal |
|-------|-------------------|------------|
| Ed. Infantil (0-3 anos) | Sem exigencia legal | Nao ha obrigatoriedade de frequencia |
| Ed. Infantil (4-5 anos) | 60% | Lei 12.796/2013 |
| Ensino Fundamental | 75% | LDB Art. 24, VI |

**Base de calculo:** Total de horas letivas do ano (minimo 800h / 200 dias).

**Falta justificada:** Conta como falta para fins de frequencia, mas isenta o aluno de penalizacao
disciplinar e gera direito a compensacao de ausencias.

## Limiares de Alerta (configuraveis por escola)

| Limiar | Acao Esperada | Base Legal |
|--------|---------------|------------|
| 5 faltas consecutivas | Contato com familia pela escola | ECA Art. 56 / Res. SEDUC 39/2023 |
| 7 faltas alternadas no mes | Contato com familia pela escola | ECA Art. 56 / Res. SEDUC 39/2023 |
| 20% de faltas no bimestre | Ativar compensacao de ausencias | Parecer CEE 67/98 |
| 25% de faltas acumuladas | Alerta critico — risco de retencao por falta | LDB Art. 24 |
| Persistencia apos contato | Notificacao ao Conselho Tutelar | ECA Art. 56 |

Os limiares sao configuraveis via `attendance_configs` (motor de regras), pois cada escola
pode ter politicas complementares.

## Compensacao de Ausencias

| Aspecto | Regra |
|---------|-------|
| Gatilho | Faltas superiores a 20% das aulas dadas no bimestre |
| Quem solicita | Responsavel legal do aluno (requerimento formal) |
| Quem programa | Professor da turma define e orienta atividades |
| Natureza | NAO anula faltas; compensa carga horaria de aprendizagem |
| Registro | Obrigatorio: atividades realizadas, datas, resultados |
| Base legal | Parecer CEE 67/98, Resolucao SEDUC 39/2023 |

## Busca Ativa

Quando o aluno atinge os limiares de alerta, a escola deve iniciar busca ativa:

1. Tentativa de contato telefonico com responsavel
2. Visita domiciliar (quando possivel)
3. Notificacao formal por escrito
4. Encaminhamento ao Conselho Tutelar (se persistir)
5. Comunicacao a Diretoria de Ensino (se evasao confirmada)

O sistema deve registrar cada tentativa de contato (data, tipo, resultado) para evidencia documental.

## Calculo de Frequencia

```
frequencia_percentual = (total_presencas / total_aulas_dadas) * 100
```

- `total_presencas` = present + excused (dispensado conta como presenca)
- `total_aulas_dadas` = todas as aulas registradas no periodo
- Falta justificada (justified_absence) conta como falta no calculo

## Registro no Sistema

- Lancamento em lote por turma/dia (AttendanceBulkPage)
- Professor seleciona a turma, data e marca presenca/falta por aluno
- Permite upsert (alterar lancamento do mesmo dia)
- Exibe frequencia acumulada do aluno na tela de consulta individual

## Impacto no Fechamento

- O fechamento de periodo exige 100% dos dias letivos com frequencia lancada
- Alunos com frequencia abaixo de 75% sao candidatos a retencao por falta
- Decisao final cabe ao Conselho de Classe (pode aprovar mesmo com baixa frequencia, com justificativa)

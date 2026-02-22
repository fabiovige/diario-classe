# Regras de Negocio

## Principio Central

Toda regra educacional e **configuravel** via motor de regras (Rules Engine).
Mudancas na legislacao NAO exigem alteracao de codigo.
Cada escola pode ter parametros diferentes se necessario.
Regras sao versionadas por ano letivo.

## Ano Letivo

- Entidade central: tudo gira em torno do ano letivo
- Status: planning → active → closing → closed
- Ano letivo so fecha se todos os periodos avaliativos estiverem fechados
- Dados imutaveis apos fechamento oficial

## Matricula e Enturmacao

- Aluno so pode ter uma enturmacao ativa por turno
- Transferencia encerra enturmacao anterior e cria nova
- Movimentacoes geram audit trail automatico
- Tipos: matricula_inicial, transferencia_interna, transferencia_externa, abandono, falecimento, reclassificacao

## Frequencia

- Registro por aula (presenca, falta, falta_justificada, dispensado)
- Calculo de % frequencia automatico
- Limiares de alerta configuraveis por escola:
  - X faltas consecutivas
  - X faltas alternadas no mes
  - X% de faltas no bimestre
- Busca ativa: registro de tentativas de contato com familia
- Compensacao de ausencias via workflow formal

## Avaliacao

- Motor de regras configuravel por escola/serie/ano letivo
- Modelos de avaliacao:
  - **Descritivo** (Ed. Infantil): texto livre por campo de experiencia, portfolios — SEM notas
  - **Numerico**: escala configuravel (0-10, 0-100)
  - **Conceitual**: escala configuravel (A/B/C/D, MB/B/R/I)
  - **Misto**: notas + conceitos
- Formulas de media: aritmetica, ponderada, customizada
- Instrumentos avaliativos com pesos configuraveis
- Recuperacao: paralela, continua, final (regras de substituicao configuraveis)

## Fechamento de Periodo

- Workflow: pendente → em_validacao → aprovado → fechado
- Exige completude: todas as notas e frequencia lancadas
- Aprovacao hierarquica: professor → coordenador → diretor
- Imutabilidade apos fechamento
- Retificacao APENAS via fluxo formal com justificativa + audit trail

## Conselho de Classe

- Decisao do conselho pode sobrescrever resultado calculado (com audit trail)
- Decisoes: aprovado, retido, progressao_parcial, encaminhamento
- Pre-conselho: resumo automatico (notas, frequencia, alertas) por aluno

## Grade de Aulas

- Cada turma tem 25 slots semanais (5 aulas/dia x 5 dias)
- Time slots sao configurados por turno (shift): horario de inicio, fim e tipo (aula ou intervalo)
- Professor monta sua propria grade; sistema apenas impede conflitos
- Validacoes obrigatorias:
  - **Conflito de turma:** nao pode haver 2 atribuicoes no mesmo slot+dia para a mesma turma
  - **Conflito de professor:** nao pode haver 2 atribuicoes no mesmo slot+dia para o mesmo professor (mesmo em turmas diferentes)
  - **Turno correto:** time_slot.shift_id deve ser compativel com class_group.shift_id
- Operacao de salvamento e sync: remove slots antigos da atribuicao e insere novos (permite redesenhar a grade)
- Grade vinculada a teacher_assignment (que ja vincula professor + turma + componente)
- Turno padrao: 5 aulas de 50min + 1 intervalo de 20min

## LGPD

- Dados de menores de 12 anos exigem responsavel vinculado
- Campos sensiveis (laudo, saude) com criptografia em repouso
- RBAC rigoroso: professor ve so suas turmas, responsavel ve so seus filhos
- Audit trail de toda consulta/alteracao de dados pessoais
- Anonimizacao em relatorios estatisticos

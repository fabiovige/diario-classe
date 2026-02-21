# Avaliacao e Notas

## Principio Geral

A avaliacao na educacao basica brasileira deve ser continua, cumulativa e formativa,
com prevalencia dos aspectos qualitativos sobre os quantitativos (LDB Art. 24).
O sistema implementa um motor de regras configuravel para suportar diferentes modelos
por escola, serie e ano letivo.

## Modelos Avaliativos por Nivel

### Educacao Infantil (Bercario I ao Pre II)

| Aspecto | Regra |
|---------|-------|
| Tipo | Processual, formativa, descritiva |
| Notas/conceitos | PROIBIDO (LDB Art. 31) |
| Retencao/reprovacao | PROIBIDA |
| Instrumento | Observacao + registro descritivo por campo de experiencia |
| Entrega | Relatorio individual semestral (minimo) |

**Campos de Experiencia (BNCC):**

| Campo | Sigla |
|-------|-------|
| O eu, o outro e o nos | EO |
| Corpo, gestos e movimentos | CG |
| Tracos, sons, cores e formas | TS |
| Escuta, fala, pensamento e imaginacao | EF |
| Espacos, tempos, quantidades, relacoes e transformacoes | ET |

O professor registra observacoes descritivas (texto livre) por campo de experiencia,
podendo incluir fotos e midias (portfolio digital). Nao ha numeros nem conceitos.

### Ensino Fundamental - Anos Iniciais (1o ao 5o ano)

| Aspecto | Configuracao padrao |
|---------|---------------------|
| Tipo de nota | Configuravel: numerico (0-10), conceitual (MB/B/R/I), ou misto |
| Periodos | Bimestral (4 bimestres) — configuravel |
| Recuperacao | Paralela (durante o bimestre) + Final (apos 4o bimestre) |
| Bloco pedagogico | 1o ao 3o ano = progressao continuada (sem retencao) |
| Media aprovacao | Configuravel (padrao: 6.0 ou conceito B) |

**Bloco pedagogico (ciclo de alfabetizacao):**
- 1o, 2o e 3o ano formam um bloco continuo
- Aluno NAO pode ser retido dentro do bloco (exceto por frequencia)
- Avaliacao e de acompanhamento do processo de alfabetizacao
- Retencao possivel apenas ao final do 3o ano

### Ensino Fundamental - Anos Finais (6o ao 9o ano)

| Aspecto | Configuracao padrao |
|---------|---------------------|
| Tipo de nota | Numerico (0-10) — configuravel |
| Periodos | Bimestral (4 bimestres) |
| Componentes | Lingua Portuguesa, Matematica, Ciencias, Historia, Geografia, Arte, Ed. Fisica, Ingles (6o ao 9o) |
| Recuperacao | Paralela + Final |
| Media aprovacao | Configuravel (padrao: 5.0 ou 6.0) |

## Instrumentos Avaliativos

| Instrumento | Peso configuravel | Exemplo |
|-------------|-------------------|---------|
| Prova escrita | Sim | Avaliacao bimestral (peso 3) |
| Trabalho | Sim | Pesquisa, apresentacao (peso 2) |
| Participacao | Sim | Envolvimento em aula (peso 1) |
| Portfolio | Sim | Colecao de trabalhos (Ed. Infantil) |
| Observacao | Sim | Registro qualitativo do professor |
| Autoavaliacao | Sim | Reflexao do aluno |

Os instrumentos e pesos sao configuraveis por escola/serie/componente via `assessment_configs`.

## Formulas de Media

| Formula | Calculo | Quando usar |
|---------|---------|-------------|
| Aritmetica | (nota1 + nota2 + ... + notaN) / N | Padrao simples |
| Ponderada | (nota1*peso1 + nota2*peso2 + ...) / (peso1 + peso2 + ...) | Instrumentos com pesos diferentes |
| Customizada | Funcao definida pela escola | Regras especificas do regimento |

## Recuperacao

### Tipos

| Tipo | Quando | Regra |
|------|--------|-------|
| Continua | Durante o bimestre | Atividades de reforco ao longo do periodo |
| Paralela | Ao final de cada bimestre | Avaliacao de recuperacao; nota substitui a menor nota do bimestre |
| Final | Apos o 4o bimestre | Para alunos que nao atingiram media anual; nota pode substituir media |

### Regras de Substituicao (configuraveis)
- **Padrao:** nota de recuperacao substitui a nota original APENAS se for maior
- **Alternativo:** media entre nota original e nota de recuperacao
- A regra especifica depende do regimento escolar de cada municipio/escola

## Conselho de Classe

| Aspecto | Regra |
|---------|-------|
| Obrigatoriedade | Obrigatorio ao final de cada bimestre (LDB) |
| Participantes | Professores da turma, coordenador pedagogico, diretor |
| Poder decisorio | Pode sobrescrever resultado calculado (aprovacao pelo conselho) |
| Registro | Ata obrigatoria com justificativa de cada decisao |
| Audit trail | Toda decisao do conselho gera registro de auditoria |

### Resultados Possiveis do Conselho

| Resultado | Codigo | Descricao |
|-----------|--------|-----------|
| Aprovado | `approved` | Atingiu criterios de aprovacao |
| Retido | `retained` | Nao atingiu criterios e conselho confirma retencao |
| Retido por Falta | `retained_by_absence` | Frequencia abaixo de 75% |
| Aprovado pelo Conselho | `council_approved` | Nao atingiu media mas conselho decidiu aprovar |

## Boletim Escolar

- Gerado automaticamente por aluno
- Exibe notas/conceitos por componente curricular e bimestre
- Inclui media anual e resultado final
- Exibe frequencia por bimestre e anual
- Disponivel para consulta por responsaveis no portal
- Base legal: LDB Art. 12, VII — informar pais sobre rendimento

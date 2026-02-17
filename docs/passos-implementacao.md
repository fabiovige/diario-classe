# Passos de Implementacao - Diario de Classe

## Fase 0: Fundacao (Sprint 1-2)

### 0.1 Setup do Projeto Backend
- Inicializar projeto Laravel 11+
- Configurar PostgreSQL
- Estruturar pastas do Modular Monolith (modulos por bounded context)
- Configurar migrations, seeders e factories
- Setup de testes (PHPUnit + Pest)
- Configurar CI basico (lint, testes, static analysis)

### 0.2 Setup do Projeto Frontend
- Inicializar projeto Vue.js 3 com Vite
- Configurar TypeScript
- Setup de roteamento (Vue Router)
- Setup de estado global (Pinia)
- Configurar testes (Vitest + Testing Library)
- Definir design system base (componentes atomicos)

### 0.3 Infraestrutura Base
- Docker Compose para ambiente local (PHP, PostgreSQL, Node, Redis)
- Configurar ambiente de desenvolvimento
- Setup de migrations iniciais

---

## Fase 1: Nucleo do Sistema (Sprint 3-6)

### 1.1 Modulo de Identidade e Acesso (IAM)
**Bounded Context: Identity**

- Autenticacao (login/logout/refresh token)
- RBAC com permissoes granulares
- Perfis: Administrador, Secretario, Diretor, Coordenador, Professor, Responsavel
- Politica de senhas e sessoes
- Audit log de acessos

### 1.2 Modulo de Estrutura Escolar
**Bounded Context: SchoolStructure**

- Cadastro de escolas (EMEB)
- Cadastro de turnos e periodos
- Ano letivo (abertura, fechamento, status)
- Series/anos e turmas vinculadas ao ano letivo
- Salas fisicas e capacidade

### 1.3 Modulo de Pessoas
**Bounded Context: People**

- Cadastro de alunos (dados pessoais, responsaveis, documentos)
- Cadastro de professores (formacao, habilitacoes)
- Cadastro de responsaveis legais
- Vinculo responsavel-aluno
- Conformidade LGPD (termos, consentimento, anonimizacao)

### 1.4 Modulo de Matricula e Enturmacao
**Bounded Context: Enrollment**

- Matricula do aluno no ano letivo
- Enturmacao (vinculo aluno-turma)
- Transferencia entre turmas/escolas
- Remanejamento
- Historico de movimentacoes (entrada, saida, motivo)

---

## Fase 2: Diario de Classe (Sprint 7-10)

### 2.1 Modulo de Matriz Curricular
**Bounded Context: Curriculum**

- Configuracao de componentes curriculares por serie/ano
- Campos de experiencia (Ed. Infantil) como entidade separada
- Carga horaria por componente
- Vinculo professor-turma-componente
- Matriz configuravel por escola e ano letivo

### 2.2 Modulo de Calendario Escolar
**Bounded Context: AcademicCalendar**

- Calendario por escola e ano letivo
- Tipos de dia: letivo, nao-letivo, reposicao, recesso, feriado
- Periodos avaliativos (bimestres/trimestres/semestres) configuraveis
- Validacao automatica de dias letivos e carga horaria minima
- Eventos especiais (conselho de classe, reuniao de pais)

### 2.3 Modulo de Frequencia
**Bounded Context: Attendance**

- Registro de presenca/falta por aula
- Calculo automatico de percentual de frequencia
- Motor de alertas configuravel (limiares parametrizaveis)
- Registro de justificativas de falta
- Compensacao de ausencias (workflow)
- Busca ativa: registro de tentativas de contato com familia

### 2.4 Modulo de Registro de Aulas
**Bounded Context: ClassRecord**

- Registro diario de conteudo ministrado
- Vinculo com habilidades/objetivos do curriculo
- Observacoes da aula
- Registro de atividades aplicadas

---

## Fase 3: Avaliacao (Sprint 11-14)

### 3.1 Motor de Regras de Avaliacao
**Bounded Context: Assessment**

- Engine de regras configuravel (nao hardcoded)
- Suporte a multiplos modelos de avaliacao:
  - Descritivo (Ed. Infantil): texto livre, campos de experiencia, portfolios
  - Numerico: notas de 0 a 10 (ou escala configuravel)
  - Conceitual: A/B/C/D ou configuravel
  - Misto: notas + conceitos
- Formulas de media configuraveis por escola/serie
- Instrumentos avaliativos (provas, trabalhos, participacao, etc)
- Pesos por instrumento

### 3.2 Recuperacao e Reavaliacao
- Recuperacao paralela (durante o bimestre)
- Recuperacao continua
- Recuperacao final
- Regras de substituicao de nota configuraveis
- Registro de atividades de recuperacao

### 3.3 Conselho de Classe
- Agendamento vinculado ao calendario
- Registro de atas e deliberacoes
- Decisoes por aluno (aprovacao, retencao por conselho, encaminhamentos)
- Assinatura digital dos participantes

### 3.4 Fechamento de Periodo
- Workflow de fechamento bimestral/semestral
- Validacao de completude (todas as notas lancadas, frequencia fechada)
- Aprovacao hierarquica (professor -> coordenador -> diretor)
- Imutabilidade apos fechamento
- Fluxo de retificacao com justificativa e auditoria

---

## Fase 4: Comunicacao e Portais (Sprint 15-17)

### 4.1 Portal do Professor
- Dashboard com turmas, pendencias e alertas
- Lancamento rapido de frequencia e notas
- Visualizacao de calendario e prazos
- Relatorios da turma

### 4.2 Portal do Responsavel
- Consulta de frequencia e notas do filho
- Boletim digital
- Comunicados da escola
- Historico escolar simplificado

### 4.3 Portal do Gestor (Diretor/Coordenador)
- Dashboard com indicadores da escola
- Acompanhamento de fechamentos pendentes
- Relatorios comparativos por turma
- Alertas de evasao e baixo rendimento

### 4.4 Portal da Secretaria de Educacao
- Visao consolidada de todas as escolas
- Indicadores municipais (frequencia, rendimento, evasao)
- Relatorios para prestacao de contas
- Gerenciamento centralizado de parametros

---

## Fase 5: Integracao e Exportacao (Sprint 18-19)

### 5.1 Exportacao de Dados
- Exportacao para formato Educacenso/INEP
- Geracao de documentos oficiais (boletim, historico escolar, ficha individual)
- Relatorios em PDF
- Exportacao em CSV/Excel para analise

### 5.2 API Publica
- API REST documentada (OpenAPI/Swagger)
- Endpoints para integracao com sistemas externos
- Webhooks para eventos importantes

---

## Fase 6: Mobile e Offline (Sprint 20-22)

### 6.1 App Mobile (PWA ou Nativo)
- Lancamento de frequencia offline
- Sincronizacao quando online
- Notificacoes push
- Interface otimizada para uso em sala de aula

---

## Fase 7: Auditoria e Compliance (Transversal)

### 7.1 Sistema de Auditoria
- Audit trail completo (quem, quando, o que, valor anterior, valor novo)
- Logs imutaveis
- Relatorios de auditoria para orgaos de controle
- Monitoramento de acessos e operacoes sensiveis

### 7.2 LGPD
- Gestao de consentimentos
- Portabilidade de dados
- Direito ao esquecimento (respeitando obrigacoes legais de guarda)
- RIPD (Relatorio de Impacto a Protecao de Dados)
- Anonimizacao de relatorios estatisticos

---

## Prioridade de Entrega (MVP)

| Prioridade | Modulo | Justificativa |
|---|---|---|
| P0 | IAM + Estrutura Escolar + Pessoas | Base para tudo funcionar |
| P0 | Matricula + Enturmacao | Sem alunos enturmados nao ha diario |
| P0 | Matriz Curricular + Calendario | Estrutura do diario depende disso |
| P1 | Frequencia + Registro de Aulas | Core do diario de classe |
| P1 | Avaliacao (motor de regras) | Core do diario de classe |
| P2 | Fechamento + Conselho de Classe | Ciclo completo de avaliacao |
| P2 | Portal do Professor | UX principal |
| P3 | Portal do Responsavel | Comunicacao com familias |
| P3 | Portal do Gestor | Gestao e indicadores |
| P4 | Exportacoes + Integracao | Compliance e prestacao de contas |
| P5 | Mobile/Offline | Diferencial competitivo |

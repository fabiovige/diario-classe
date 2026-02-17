# TASKS - Diario de Classe Digital

> Checklist de desenvolvimento. Marcar `[x]` conforme cada tarefa for concluida.
> Referencia: `docs/passos-implementacao.md` e `docs/PRD-jandira.md`

---

## FASE 0 — Fundacao [Sprint 1-2]

### 0.1 Backend (Laravel)

- [x] Inicializar projeto Laravel 11+ com PHP 8+
- [x] Configurar conexao com PostgreSQL
- [x] Estruturar pastas do Modular Monolith por bounded context
- [x] Configurar migrations, seeders e factories base
- [x] Setup de testes (Pest + PHPUnit)
- [x] Configurar PHPStan (static analysis)
- [x] Configurar Laravel Pint (code style)
- [x] Criar migration base do audit trail (transversal)

### 0.2 Frontend (Vue.js)

- [ ] Inicializar projeto Vue.js 3 com Vite
- [ ] Configurar TypeScript
- [ ] Setup Vue Router
- [ ] Setup Pinia (estado global)
- [ ] Configurar Vitest + Testing Library
- [ ] Definir design system base (botoes, inputs, tabelas, modais)
- [ ] Configurar ESLint + Prettier
- [ ] Criar layout base (sidebar, header, content area)

### 0.3 Infraestrutura

- [ ] Criar Docker Compose (PHP-FPM, PostgreSQL, Redis, Node)
- [ ] Configurar Nginx ou Caddy como reverse proxy
- [ ] Criar Makefile com comandos uteis (up, down, test, migrate, seed)
- [ ] Configurar .env.example com todas as variaveis
- [ ] Setup de CI basico (GitHub Actions: lint, testes, build)
- [ ] Configurar .gitignore para backend e frontend

---

## FASE 1 — Nucleo do Sistema [Sprint 3-6]

### 1.1 Identidade e Acesso (IAM) — `Modules/Identity`

**Backend:**

- [ ] Criar modulo Identity com estrutura de camadas (Domain, Application, Infrastructure, Presentation)
- [ ] Implementar entidade User (email, password, status, perfil)
- [ ] Implementar autenticacao (login/logout/refresh token via JWT ou Sanctum)
- [ ] Implementar RBAC: Role, Permission
- [ ] Criar perfis padrao: Admin, Secretario, Diretor, Coordenador, Professor, Responsavel
- [ ] Implementar middleware de autorizacao por permissao
- [ ] Implementar escopo por escola (professor ve so suas turmas)
- [ ] Implementar politica de senhas (minimo 8 chars, complexidade)
- [ ] Implementar audit log de acessos (login, logout, tentativas)
- [ ] Criar seeders com usuarios de teste por perfil
- [ ] Escrever testes: autenticacao, autorizacao, escopos

**Frontend:**

- [ ] Tela de login
- [ ] Gerenciamento de token (armazenamento, refresh automatico)
- [ ] Guards de rota por perfil/permissao
- [ ] Tela de gestao de usuarios (CRUD)
- [ ] Tela de gestao de perfis e permissoes

### 1.2 Estrutura Escolar — `Modules/SchoolStructure`

**Backend:**

- [ ] Criar modulo SchoolStructure
- [ ] Entidade School (nome, endereco, codigo INEP, telefone, email, tipo)
- [ ] Entidade AcademicYear (ano, data_inicio, data_fim, status: planning/active/closed)
- [ ] Entidade Shift (turno: manha, tarde, integral, noturno)
- [ ] Entidade GradeLevel (serie/ano: 1o ano, 2o ano, Pre I, Pre II, Berçario, etc)
- [ ] Entidade ClassGroup (turma: vinculo serie + turno + ano_letivo + escola)
- [ ] Value Object: SchoolType (creche, pre-escola, fundamental, mista)
- [ ] CRUD completo com validacoes de dominio
- [ ] Regra: turma sempre vinculada a ano letivo + serie + turno + escola
- [ ] Regra: ano letivo so pode fechar se todos os periodos avaliativos estiverem fechados
- [ ] Seeders com as 30 EMEBs de Jandira
- [ ] Escrever testes para todas as regras de dominio

**Frontend:**

- [ ] Tela de listagem/cadastro de escolas
- [ ] Tela de configuracao de ano letivo
- [ ] Tela de gestao de turnos
- [ ] Tela de gestao de series/anos
- [ ] Tela de gestao de turmas (criar, editar, visualizar alunos)

### 1.3 Pessoas — `Modules/People`

**Backend:**

- [ ] Criar modulo People
- [ ] Entidade Student (nome, data_nascimento, cpf, nis, foto, sexo, raca_cor, necessidades_especiais)
- [ ] Entidade Guardian (responsavel: nome, cpf, telefone, email, parentesco)
- [ ] Entidade Teacher (professor: nome, cpf, formacao, registro_funcional)
- [ ] Vinculo StudentGuardian (aluno-responsavel, com tipo: mae, pai, outro)
- [ ] Value Object: Address, Phone, Document
- [ ] Regra: dados sensiveis (laudo, necessidades especiais) com flag LGPD
- [ ] Regra: aluno menor de 12 anos exige responsavel vinculado
- [ ] CRUD completo com busca e filtros
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de listagem/cadastro de alunos
- [ ] Tela de listagem/cadastro de professores
- [ ] Tela de vinculacao aluno-responsavel
- [ ] Busca inteligente (por nome, CPF, RA)

### 1.4 Matricula e Enturmacao — `Modules/Enrollment`

**Backend:**

- [ ] Criar modulo Enrollment
- [ ] Entidade Enrollment (matricula: aluno + ano_letivo + escola + data_entrada + status)
- [ ] Entidade ClassAssignment (enturmacao: matricula + turma + data_inicio + data_fim)
- [ ] Entidade EnrollmentMovement (movimentacao: tipo, data, motivo, escola_destino)
- [ ] Tipos de movimentacao: matricula_inicial, transferencia_interna, transferencia_externa, abandono, falecimento, reclassificacao
- [ ] Regra: aluno so pode ter uma enturmacao ativa por turno
- [ ] Regra: transferencia encerra enturmacao anterior e cria nova
- [ ] Regra: movimentacoes geram audit trail automatico
- [ ] UseCase: MatricularAluno, EnturmarAluno, TransferirAluno
- [ ] Escrever testes para todos os use cases

**Frontend:**

- [ ] Tela de matricula de aluno (selecionar escola, serie, turma)
- [ ] Tela de enturmacao (arrastar/selecionar alunos para turma)
- [ ] Tela de transferencia (interna e externa)
- [ ] Historico de movimentacoes do aluno

---

## FASE 2 — Diario de Classe [Sprint 7-10]

### 2.1 Matriz Curricular — `Modules/Curriculum`

**Backend:**

- [ ] Criar modulo Curriculum
- [ ] Entidade CurricularComponent (componente: nome, area_conhecimento, tipo)
- [ ] Entidade ExperienceField (campo de experiencia - Ed. Infantil)
- [ ] Entidade CurriculumMatrix (matriz: escola + serie + ano_letivo + componentes + carga_horaria)
- [ ] Entidade TeacherAssignment (vinculo professor + turma + componente)
- [ ] Regra: Ed. Infantil usa ExperienceField, Fundamental usa CurricularComponent
- [ ] Regra: matriz configuravel por escola e ano letivo
- [ ] CRUD + validacoes
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de configuracao de componentes curriculares
- [ ] Tela de montagem de matriz curricular por serie
- [ ] Tela de atribuicao professor-turma-componente

### 2.2 Calendario Escolar — `Modules/AcademicCalendar`

**Backend:**

- [ ] Criar modulo AcademicCalendar
- [ ] Entidade SchoolCalendar (calendario: escola + ano_letivo)
- [ ] Entidade CalendarDay (dia: data, tipo, descricao)
- [ ] Entidade AssessmentPeriod (periodo avaliativo: tipo, numero, data_inicio, data_fim, status)
- [ ] Value Object: DayType (letivo, nao_letivo, reposicao, recesso, feriado)
- [ ] Regra: contador automatico de dias letivos
- [ ] Regra: validacao de carga horaria minima (parametro configuravel)
- [ ] Regra: periodo avaliativo tem status (aberto, em_fechamento, fechado)
- [ ] Eventos: conselho_de_classe, reuniao_pais, formacao_docente
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de calendario visual (mensal, com cores por tipo de dia)
- [ ] Tela de configuracao de periodos avaliativos
- [ ] Indicadores: dias letivos cumpridos vs. meta

### 2.3 Frequencia — `Modules/Attendance`

**Backend:**

- [ ] Criar modulo Attendance
- [ ] Entidade AttendanceRecord (registro: aluno + turma + componente + data + status)
- [ ] Value Object: AttendanceStatus (presente, falta, falta_justificada, dispensado)
- [ ] Entidade AbsenceJustification (justificativa: aluno + data + motivo + documento)
- [ ] Entidade ActiveSearch (busca ativa: aluno + tentativas de contato)
- [ ] Service: FrequencyCalculator (calculo de % frequencia por aluno/turma/componente)
- [ ] Service: AlertEngine (motor de alertas com limiares configuraveis)
- [ ] Regra: alerta ao atingir X faltas consecutivas (parametro configuravel)
- [ ] Regra: alerta ao atingir X faltas alternadas no mes (parametro configuravel)
- [ ] Regra: alerta ao atingir X% de faltas no bimestre (parametro configuravel)
- [ ] UseCase: RegistrarFrequencia (batch por turma), JustificarFalta, RegistrarBuscaAtiva
- [ ] Escrever testes para calculator, alertas e use cases

**Frontend:**

- [ ] Tela de chamada (interface rapida: lista de alunos com toggle P/F)
- [ ] Tela de mapa de frequencia (calendario visual por aluno)
- [ ] Tela de justificativa de falta (com upload)
- [ ] Painel de alertas de frequencia
- [ ] Tela de registro de busca ativa

### 2.4 Registro de Aulas — `Modules/ClassRecord`

**Backend:**

- [ ] Criar modulo ClassRecord
- [ ] Entidade LessonRecord (registro: turma + componente + data + conteudo + observacoes)
- [ ] Vinculo opcional com habilidades do curriculo
- [ ] Regra: registro vinculado a dia letivo do calendario
- [ ] CRUD + validacoes
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de registro de aula (integrada com tela de chamada)
- [ ] Historico de aulas por turma/componente

---

## FASE 3 — Avaliacao [Sprint 11-14]

### 3.1 Motor de Regras de Avaliacao — `Modules/Assessment`

**Backend:**

- [ ] Criar modulo Assessment
- [ ] Entidade AssessmentRule (regra: escola + serie + ano_letivo + tipo_avaliacao + formula_media)
- [ ] Entidade AssessmentInstrument (instrumento: nome, peso, tipo)
- [ ] Entidade Grade (nota: aluno + turma + componente + periodo + instrumento + valor)
- [ ] Entidade DescriptiveReport (relatorio descritivo: aluno + campo_experiencia + periodo + texto)
- [ ] Strategy Pattern: GradeCalculator (numerico, conceitual, descritivo)
- [ ] Strategy Pattern: AverageFormula (aritmetica, ponderada, customizada)
- [ ] Regra: tipo de avaliacao determinado pela regra da serie (nao hardcoded)
- [ ] Regra: Ed. Infantil aceita APENAS registros descritivos
- [ ] Regra: escalas e formulas configuraveis por escola/serie/ano_letivo
- [ ] Escrever testes para cada strategy e formula

**Frontend:**

- [ ] Tela de configuracao de regras de avaliacao por serie
- [ ] Tela de lancamento de notas (grid: alunos x instrumentos)
- [ ] Tela de lancamento descritivo (Ed. Infantil: texto + upload)
- [ ] Visualizacao de medias calculadas automaticamente

### 3.2 Recuperacao — `Modules/Assessment`

**Backend:**

- [ ] Entidade RecoveryActivity (atividade de recuperacao: tipo, aluno, componente, periodo)
- [ ] Tipos: paralela, continua, final
- [ ] Regra: substituicao de nota configuravel (maior nota, media, ultima nota)
- [ ] Regra: recuperacao final so se habilita apos fechamento do ultimo periodo regular
- [ ] UseCase: RegistrarRecuperacao, AplicarSubstituicaoNota
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de registro de atividades de recuperacao
- [ ] Indicacao visual de alunos em recuperacao na grid de notas

### 3.3 Conselho de Classe — `Modules/Council`

**Backend:**

- [ ] Criar modulo Council
- [ ] Entidade CouncilMeeting (reuniao: turma + periodo + data + participantes)
- [ ] Entidade CouncilDeliberation (deliberacao: aluno + decisao + justificativa)
- [ ] Decisoes: aprovado, retido, progressao_parcial, encaminhamento
- [ ] Service: PreCouncilReport (resumo automatico: notas, frequencia, alertas por aluno)
- [ ] Regra: decisao do conselho pode sobrescrever resultado calculado (com audit trail)
- [ ] Escrever testes

**Frontend:**

- [ ] Tela de pre-conselho (resumo por aluno com indicadores)
- [ ] Tela de registro de ata e deliberacoes
- [ ] Indicacao visual de decisoes do conselho no boletim

### 3.4 Fechamento de Periodo — `Modules/PeriodClosing`

**Backend:**

- [ ] Criar modulo PeriodClosing
- [ ] Entidade PeriodClosing (fechamento: turma + componente + periodo + status + aprovado_por)
- [ ] Entidade FinalResult (resultado final: aluno + turma + ano_letivo + resultado + media_final)
- [ ] Entidade Rectification (retificacao: fechamento + campo_alterado + valor_anterior + valor_novo + justificativa)
- [ ] Workflow: pendente -> em_validacao -> aprovado -> fechado
- [ ] Regra: fechamento exige todas as notas e frequencia lancadas
- [ ] Regra: aprovacao hierarquica (professor -> coordenador -> diretor)
- [ ] Regra: dados imutaveis apos status "fechado"
- [ ] Regra: retificacao so via fluxo formal com justificativa e audit trail
- [ ] Resultado final: aprovado, retido, transferido, abandono, reclassificado
- [ ] Escrever testes para workflow e imutabilidade

**Frontend:**

- [ ] Tela de fechamento bimestral (checklist de pendencias)
- [ ] Tela de aprovacao hierarquica (coordenador/diretor)
- [ ] Tela de retificacao pos-fechamento
- [ ] Tela de resultado final do ano letivo

---

## FASE 4 — Portais e Dashboards [Sprint 15-17]

### 4.1 Portal do Professor

**Frontend:**

- [ ] Dashboard: turmas do dia, pendencias, alertas de frequencia
- [ ] Lancamento rapido de frequencia (acesso direto pela dashboard)
- [ ] Lancamento rapido de notas
- [ ] Calendario com prazos de fechamento
- [ ] Relatorios: frequencia da turma, notas por instrumento, alunos em risco

### 4.2 Portal do Responsavel

**Backend:**

- [ ] Endpoint: dados do aluno (notas, frequencia, boletim) filtrado por responsavel
- [ ] Regra: responsavel ve APENAS dados dos seus filhos

**Frontend:**

- [ ] Dashboard: resumo do filho (frequencia, ultima nota, comunicados)
- [ ] Tela de boletim digital
- [ ] Tela de historico de frequencia
- [ ] Tela de comunicados da escola

### 4.3 Portal do Gestor (Diretor/Coordenador)

**Frontend:**

- [ ] Dashboard: indicadores da escola (frequencia geral, rendimento, evasao)
- [ ] Painel de fechamentos pendentes por turma
- [ ] Relatorios comparativos entre turmas
- [ ] Alertas: alunos com risco de evasao, turmas com baixo rendimento
- [ ] Listagem de professores com pendencias

### 4.4 Portal da Secretaria de Educacao

**Frontend:**

- [ ] Dashboard: indicadores consolidados das 30 escolas
- [ ] Ranking de escolas por indicadores
- [ ] Tela de configuracao de parametros municipais (regras de avaliacao, limiares)
- [ ] Relatorios para prestacao de contas
- [ ] Visao de calendario unificado do municipio

---

## FASE 5 — Integracao e Exportacao [Sprint 18-19]

### 5.1 Documentos Oficiais

**Backend:**

- [ ] Service: PDFGenerator (geracao de documentos em PDF)
- [ ] Template: Boletim escolar (layout configuravel por escola)
- [ ] Template: Ficha individual do aluno
- [ ] Template: Historico escolar
- [ ] Template: Ata de resultado final
- [ ] Template: Mapa de frequencia
- [ ] Exportacao generica CSV/Excel

**Frontend:**

- [ ] Tela de geracao de documentos (selecionar tipo, turma, periodo)
- [ ] Preview do documento antes de gerar
- [ ] Download em PDF/Excel

### 5.2 Integracao Externa

**Backend:**

- [ ] Exportacao em formato compativel com Educacenso/INEP
- [ ] API REST documentada (OpenAPI/Swagger)
- [ ] Endpoints para integracao com sistemas externos
- [ ] Webhooks para eventos (matricula, fechamento, transferencia)

### 5.3 Relatorios Gerenciais

**Backend:**

- [ ] Relatorio: frequencia por escola/turma/aluno
- [ ] Relatorio: rendimento por escola/turma/componente
- [ ] Relatorio: taxa de evasao/abandono
- [ ] Relatorio: movimentacao de alunos
- [ ] Relatorio: comparativo entre periodos avaliativos

---

## FASE 6 — Mobile e Offline [Sprint 20-22]

### 6.1 PWA

- [ ] Configurar Service Worker para cache de dados criticos
- [ ] Implementar modo offline para lancamento de frequencia
- [ ] Implementar sincronizacao ao recuperar conexao (conflict resolution)
- [ ] Configurar notificacoes push (Web Push API)
- [ ] Otimizar interface para touch (botoes maiores, gestos)
- [ ] Testar em dispositivos reais (Android/iOS via browser)

---

## TRANSVERSAL — Auditoria e LGPD

### T.1 Sistema de Auditoria

- [ ] Implementar audit trail generico (trait/observer no Laravel)
- [ ] Campos: user_id, action, entity, entity_id, old_values, new_values, ip, user_agent, timestamp
- [ ] Logs imutaveis (append-only, sem update/delete)
- [ ] Tela de consulta de audit trail (filtros por usuario, entidade, periodo)
- [ ] Relatorios de auditoria para orgaos de controle
- [ ] Monitoramento de operacoes sensiveis (alteracao de nota, exclusao, fechamento)

### T.2 LGPD

- [ ] Implementar gestao de termos de consentimento (versionados)
- [ ] Implementar portabilidade de dados (exportacao dos dados do aluno)
- [ ] Implementar anonimizacao para relatorios estatisticos
- [ ] Documentar politica de retencao de dados
- [ ] Marcar campos sensiveis (laudo, saude) com criptografia em repouso
- [ ] Implementar log de acesso a dados pessoais

---

## Legenda

| Simbolo | Significado |
|---|---|
| `[ ]` | Pendente |
| `[x]` | Concluido |
| P0 | Critico — necessario para MVP |
| P1 | Importante — core do diario |
| P2 | Desejavel — ciclo completo |
| P3 | Futuro — diferencial |

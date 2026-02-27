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

- [x] Inicializar projeto Vue.js 3 com Vite
- [x] Configurar TypeScript
- [x] Setup Vue Router
- [x] Setup Pinia (estado global)
- [x] Configurar Vitest + Testing Library
- [x] Definir design system base (botoes, inputs, tabelas, modais)
- [x] Configurar ESLint + Prettier
- [x] Criar layout base (sidebar, header, content area)

### 0.3 Infraestrutura

- [x] Criar Docker Compose (PHP-FPM, MySQL, Redis, Node)
- [ ] Configurar Nginx ou Caddy como reverse proxy
- [ ] Criar Makefile com comandos uteis (up, down, test, migrate, seed)
- [x] Configurar .env.example com todas as variaveis
- [ ] Setup de CI basico (GitHub Actions: lint, testes, build)
- [x] Configurar .gitignore para backend e frontend

---

## FASE 1 — Nucleo do Sistema [Sprint 3-6]

### 1.1 Identidade e Acesso (IAM) — `Modules/Identity`

**Backend:**

- [x] Criar modulo Identity com estrutura de camadas (Domain, Application, Infrastructure, Presentation)
- [x] Implementar entidade User (email, password, status, perfil)
- [x] Implementar autenticacao (login/logout/refresh token via Sanctum)
- [x] Implementar RBAC: Role, Permission
- [x] Criar perfis padrao: Admin, Secretario, Diretor, Coordenador, Professor, Responsavel
- [x] Implementar middleware de autorizacao por permissao (CheckPermission)
- [x] Implementar escopo por escola (CheckSchoolScope)
- [ ] Implementar politica de senhas (minimo 8 chars, complexidade)
- [ ] Implementar audit log de acessos (login, logout, tentativas)
- [x] Criar seeders com usuarios de teste por perfil
- [x] Escrever testes: autenticacao, autorizacao (AuthTest, UserTest)

**Frontend:**

- [x] Tela de login (LoginPage)
- [x] Gerenciamento de token (armazenamento, refresh automatico)
- [x] Guards de rota por perfil/permissao (authGuard, roleGuard)
- [x] Tela de gestao de usuarios (UserListPage, UserFormPage)
- [x] Tela de gestao de perfis e permissoes (RoleListPage, RoleFormPage)

### 1.2 Estrutura Escolar — `Modules/SchoolStructure`

**Backend:**

- [x] Criar modulo SchoolStructure
- [x] Entidade School (nome, endereco, codigo INEP, telefone, email, tipo)
- [x] Entidade AcademicYear (ano, data_inicio, data_fim, status)
- [x] Entidade Shift (turno: manha, tarde, integral, noturno)
- [x] Entidade GradeLevel (serie/ano com min_age_months e type)
- [x] Entidade ClassGroup (turma: vinculo serie + turno + ano_letivo + escola + max_students)
- [x] Enums: SchoolType, ShiftName, GradeLevelType, AcademicYearStatus
- [x] CRUD completo com validacoes de dominio
- [x] Regra: turma sempre vinculada a ano letivo + serie + turno + escola
- [x] Regra: ano letivo so pode fechar se todos os periodos avaliativos estiverem fechados
- [x] Seeders com as 30 EMEBs de Jandira
- [x] Escrever testes (SchoolTest, ClassGroupTest)

**Frontend:**

- [x] Tela de listagem/cadastro de escolas (SchoolListPage, SchoolFormPage)
- [x] Tela de configuracao de ano letivo (AcademicYearListPage, AcademicYearFormPage)
- [x] Endpoint e UseCase para encerramento do ano letivo (CloseAcademicYearUseCase + POST /academic-years/{id}/close)
- [x] Tela de gestao de turnos (ShiftListPage, ShiftFormPage)
- [x] Tela de gestao de series/anos (GradeLevelListPage, GradeLevelFormPage)
- [x] Tela de gestao de turmas (ClassGroupListPage, ClassGroupFormPage)

### 1.3 Pessoas — `Modules/People`

**Backend:**

- [x] Criar modulo People
- [x] Entidade Student (nome, data_nascimento, cpf, nis, sexo, raca_cor, necessidades_especiais, disability_type)
- [x] Entidade Guardian (responsavel: nome, cpf, telefone, email, parentesco)
- [x] Entidade Teacher (professor: nome, cpf, formacao, registro_funcional)
- [x] Vinculo StudentGuardian (aluno-responsavel, com tipo: mae, pai, outro + is_primary, can_pickup)
- [ ] Value Object: Address, Phone, Document
- [ ] Regra: dados sensiveis (laudo, necessidades especiais) com flag LGPD
- [ ] Regra: aluno menor de 12 anos exige responsavel vinculado
- [x] CRUD completo com busca e filtros
- [x] Escrever testes (StudentTest, TeacherTest)

**Frontend:**

- [x] Tela de listagem/cadastro de alunos (StudentListPage, StudentFormPage, StudentDetailPage)
- [x] Tela de listagem/cadastro de professores (TeacherListPage, TeacherFormPage)
- [x] Tela de listagem/cadastro de responsaveis (GuardianListPage, GuardianFormPage)
- [ ] Busca inteligente (por nome, CPF, RA)

### 1.4 Matricula e Enturmacao — `Modules/Enrollment`

**Backend:**

- [x] Criar modulo Enrollment
- [x] Entidade Enrollment (matricula: aluno + ano_letivo + escola + data_entrada + status + enrollment_number)
- [x] Entidade ClassAssignment (enturmacao: matricula + turma + data_inicio + data_fim + status)
- [x] Entidade EnrollmentMovement (movimentacao: tipo, data, motivo, escola_origem/destino)
- [x] Entidade EnrollmentDocument (documentos: tipo, upload, status de revisao)
- [x] Tipos de movimentacao: matricula_inicial, transferencia_interna, transferencia_externa, abandono, falecimento, reclassificacao
- [x] Regra: aluno so pode ter uma enturmacao ativa por turno
- [x] Regra: transferencia encerra enturmacao anterior e cria nova
- [x] Regra: movimentacoes geram audit trail automatico
- [x] UseCase: CreateEnrollment, AssignToClass, TransferEnrollment
- [x] Service: EnrollmentNumberGenerator (geracao automatica de numero de matricula)
- [x] Escrever testes (EnrollmentTest)

**Frontend:**

- [x] Tela de matricula de aluno (EnrollmentListPage, EnrollmentFormPage)
- [x] Tela de detalhe da matricula (EnrollmentDetailPage com documentos)
- [ ] Tela de enturmacao dedicada (arrastar/selecionar alunos para turma)
- [ ] Tela de transferencia dedicada (interna e externa)
- [ ] Historico de movimentacoes do aluno

---

## FASE 2 — Diario de Classe [Sprint 7-10]

### 2.1 Matriz Curricular — `Modules/Curriculum`

**Backend:**

- [x] Criar modulo Curriculum
- [x] Entidade CurricularComponent (componente: nome, area_conhecimento, tipo)
- [x] Entidade ExperienceField (campo de experiencia - Ed. Infantil)
- [ ] Entidade CurriculumMatrix (matriz: escola + serie + ano_letivo + componentes + carga_horaria)
- [x] Entidade TeacherAssignment (vinculo professor + turma + componente/campo_experiencia)
- [x] Entidade TimeSlot (faixa horaria: turno + numero + horario_inicio + horario_fim)
- [x] Entidade ClassSchedule (grade horaria: atribuicao + dia_semana + faixa_horaria)
- [x] Service: DailyClassSummary (resumo diario do professor com flags de pendencias)
- [x] Regra: Ed. Infantil usa ExperienceField, Fundamental usa CurricularComponent
- [ ] Regra: matriz configuravel por escola e ano letivo
- [x] CRUD + validacoes
- [x] Escrever testes (CurricularComponentTest, TeacherAssignmentTest, DailyClassSummaryTest, ClassScheduleTest, TimeSlotTest)

**Frontend:**

- [x] Tela de configuracao de componentes curriculares (CurricularComponentListPage, CurricularComponentFormPage)
- [x] Tela de campos de experiencia (ExperienceFieldListPage, ExperienceFieldFormPage)
- [x] Tela de atribuicao professor-turma-componente (TeacherAssignmentListPage, TeacherAssignmentFormPage)
- [x] Tela de gestao de horarios (TimeSlotListPage, ClassSchedulePage, TeacherSchedulePage)
- [x] Tela de agenda mensal do professor (TeacherAgendaPage)
- [x] Workspace unificado do professor (MyClassesPage, ClassSessionPage)

### 2.2 Calendario Escolar — `Modules/AcademicCalendar`

**Backend:**

- [x] Criar modulo AcademicCalendar
- [ ] Entidade SchoolCalendar (calendario: escola + ano_letivo)
- [ ] Entidade CalendarDay (dia: data, tipo, descricao)
- [x] Entidade AssessmentPeriod (periodo avaliativo: tipo, numero, data_inicio, data_fim, status)
- [ ] Value Object: DayType (letivo, nao_letivo, reposicao, recesso, feriado)
- [ ] Regra: contador automatico de dias letivos
- [ ] Regra: validacao de carga horaria minima (parametro configuravel)
- [x] Regra: periodo avaliativo tem status (aberto, fechado)
- [ ] Eventos: conselho_de_classe, reuniao_pais, formacao_docente
- [x] Escrever testes (AssessmentPeriodTest)

**Frontend:**

- [ ] Tela de calendario visual (mensal, com cores por tipo de dia)
- [x] Tela de configuracao de periodos avaliativos (AssessmentPeriodListPage, AssessmentPeriodFormPage)
- [ ] Indicadores: dias letivos cumpridos vs. meta

### 2.3 Frequencia — `Modules/Attendance`

**Backend:**

- [x] Criar modulo Attendance
- [x] Entidade AttendanceRecord (registro: aluno + turma + componente + data + status + notes)
- [x] Enum: AttendanceStatus (presente, falta, falta_justificada, dispensado)
- [x] Entidade AbsenceJustification (justificativa: aluno + data + motivo + aprovacao)
- [x] Entidade AttendanceConfig (configuracao de regras de frequencia por escola/serie)
- [ ] Entidade ActiveSearch (busca ativa: aluno + tentativas de contato)
- [x] Service: FrequencyCalculator (calculo de % frequencia)
- [x] Service: AttendanceAlertChecker (motor de alertas com limiares configuraveis)
- [x] Regra: alerta ao atingir X faltas consecutivas (parametro configuravel)
- [x] Regra: alerta ao atingir X faltas no mes (parametro configuravel)
- [ ] Regra: alerta ao atingir X% de faltas no bimestre (parametro configuravel)
- [x] UseCase: RecordBulkAttendance, JustifyAbsence, ApproveAbsenceJustification
- [x] Escrever testes (AttendanceTest)

**Frontend:**

- [x] Tela de chamada em lote (AttendanceBulkPage)
- [x] Tela de frequencia por turma (AttendanceClassGridPage)
- [x] Tela de frequencia individual do aluno (AttendanceStudentPage)
- [x] Tela de justificativa de falta (AbsenceJustificationPage)
- [ ] Painel de alertas de frequencia
- [ ] Tela de registro de busca ativa

### 2.4 Registro de Aulas — `Modules/ClassRecord`

**Backend:**

- [x] Criar modulo ClassRecord
- [x] Entidade LessonRecord (registro: turma + componente + data + conteudo + metodologia + observacoes + class_count)
- [ ] Vinculo opcional com habilidades do curriculo
- [ ] Regra: registro vinculado a dia letivo do calendario
- [x] CRUD + validacoes
- [x] Escrever testes (LessonRecordTest)

**Frontend:**

- [x] Tela de registro de aula (LessonRecordListPage, LessonRecordFormPage)
- [ ] Historico de aulas por turma/componente (visualizacao consolidada)

---

## FASE 3 — Avaliacao [Sprint 11-14]

### 3.1 Motor de Regras de Avaliacao — `Modules/Assessment`

**Backend:**

- [x] Criar modulo Assessment
- [x] Entidade AssessmentConfig (regra: escola + serie + ano_letivo + tipo_avaliacao + formula_media + recovery rules)
- [x] Entidade AssessmentInstrument (instrumento: nome, peso, tipo)
- [x] Entidade Grade (nota: aluno + turma + componente + periodo + instrumento + valor numerico/conceitual + recovery)
- [x] Entidade DescriptiveReport (relatorio descritivo: aluno + campo_experiencia + periodo + texto)
- [x] Entidade PeriodAverage (media por periodo)
- [x] Entidade FinalAverage (media final por componente/ano)
- [x] Entidade ConceptualScale (escala de conceitos configuravel)
- [x] Strategy Pattern: GradeCalculator (NumericGradeCalculation, ConceptualGradeCalculation, DescriptiveGradeCalculation)
- [x] Factory: GradeCalculationFactory
- [x] Regra: tipo de avaliacao determinado pela regra da serie (nao hardcoded)
- [x] Regra: Ed. Infantil aceita APENAS registros descritivos
- [x] Regra: escalas e formulas configuraveis por escola/serie/ano_letivo
- [x] Escrever testes (AssessmentTest)

**Frontend:**

- [ ] Tela de configuracao de regras de avaliacao por serie
- [x] Tela de lancamento de notas em lote (GradeBulkPage)
- [x] Tela de consulta de notas (GradeListPage)
- [x] Tela de lancamento descritivo (DescriptiveReportPage)
- [x] Tela de boletim do aluno (ReportCardPage) com exportacao PDF client-side

### 3.2 Recuperacao — `Modules/Assessment`

**Backend:**

- [ ] Entidade RecoveryActivity (atividade de recuperacao: tipo, aluno, componente, periodo)
- [x] Enum: RecoveryType, RecoveryReplaces (regras de substituicao)
- [x] Campo recovery_type na entidade Grade
- [ ] Regra: recuperacao final so se habilita apos fechamento do ultimo periodo regular
- [x] UseCase: RecordRecoveryGrade
- [ ] Escrever testes especificos de recuperacao

**Frontend:**

- [ ] Tela de registro de atividades de recuperacao
- [ ] Indicacao visual de alunos em recuperacao na grid de notas

### 3.3 Conselho de Classe — `Modules/Council`

**Backend:**

- [ ] Criar modulo Council (estrutura existe mas SEM implementacao)
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

- [x] Criar modulo PeriodClosing
- [x] Entidade PeriodClosing (fechamento: turma + componente + periodo + status + aprovado_por)
- [x] Entidade FinalResultRecord (resultado final: aluno + turma + ano_letivo + resultado + media_final)
- [x] Entidade Rectification (retificacao: fechamento + campo_alterado + valor_anterior + valor_novo + justificativa)
- [x] Workflow: Draft -> Submitted -> Validated -> Approved -> Closed
- [x] Specification Pattern: LessonRecordsComplete, AttendanceComplete, GradesComplete
- [x] UseCase: ClosePeriod, SubmitPeriodClosing, ValidatePeriodClosing, RunCompletenessCheck, CalculateFinalResult, RequestRectification, CalculateBulkFinalResults, CloseAcademicYear
- [x] Regra: fechamento exige todas as notas e frequencia lancadas (specifications)
- [ ] Regra: aprovacao hierarquica (professor -> coordenador -> diretor)
- [x] Regra: dados imutaveis apos status "fechado"
- [x] Regra: retificacao so via fluxo formal com justificativa
- [x] Resultado final: Approved, NotApproved, Promoted, Retained
- [x] Escrever testes (PeriodClosingTest)

**Frontend:**

- [x] Tela de fechamento (ClosingDashboardPage)
- [x] Tela de detalhe do fechamento (ClosingDetailPage)
- [ ] Tela de retificacao pos-fechamento
- [x] Tela de resultado final do ano letivo (FinalResultPage)
- [x] Tela de resultado anual por turma com calculo em lote e exportacao PDF (AnnualResultPage)

---

## FASE 4 — Portais e Dashboards [Sprint 15-17]

### 4.1 Portal do Professor

**Frontend:**

- [x] Dashboard basico do professor (TeacherDashboard)
- [x] Workspace unificado "Minhas Aulas" (MyClassesPage + ClassSessionPage)
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

- [x] Dashboard basico do diretor (DirectorDashboard)
- [x] Dashboard basico do coordenador (CoordinatorDashboard)
- [ ] Painel de fechamentos pendentes por turma
- [ ] Relatorios comparativos entre turmas
- [ ] Alertas: alunos com risco de evasao, turmas com baixo rendimento
- [ ] Listagem de professores com pendencias

### 4.4 Portal da Secretaria de Educacao

**Frontend:**

- [x] Dashboard basico do admin (AdminDashboard)
- [x] Dashboard basico do secretario (SecretaryDashboard)
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

- [x] Implementar audit trail generico (trait Auditable no Laravel)
- [x] Entidade AuditLog com campos padrao
- [ ] Logs imutaveis (append-only, sem update/delete)
- [ ] Tela de consulta de audit trail (filtros por usuario, entidade, periodo)
- [ ] Relatorios de auditoria para orgaos de controle
- [ ] Monitoramento de operacoes sensiveis (alteracao de nota, exclusao, fechamento)
- [x] Escrever testes (AuditLogTest)

### T.2 LGPD

- [ ] Implementar gestao de termos de consentimento (versionados)
- [ ] Implementar portabilidade de dados (exportacao dos dados do aluno)
- [ ] Implementar anonimizacao para relatorios estatisticos
- [ ] Documentar politica de retencao de dados
- [ ] Marcar campos sensiveis (laudo, saude) com criptografia em repouso
- [ ] Implementar log de acesso a dados pessoais

---

## UI/UX — Padronizacao de Interface

### U.1 Paginas que precisam migrar de dialog para pagina separada

- [x] ShiftListPage: migrado para ShiftFormPage (usa router.push)
- [x] CurricularComponentListPage: migrado para CurricularComponentFormPage (usa router.push)
- [x] ExperienceFieldListPage: migrado para ExperienceFieldFormPage (usa router.push)
- [x] TeacherAssignmentListPage: migrado para TeacherAssignmentFormPage (usa router.push)

### U.2 Filtros por escola padronizados (useSchoolScope + useListFilters)

- [x] StudentListPage: filtro escola + ano letivo + turma
- [x] EnrollmentListPage: filtro escola + ano letivo + turma + status
- [x] AssessmentPeriodListPage: filtro escola + ano letivo
- [x] TeacherAssignmentListPage: filtro escola + turma
- [x] TeacherListPage: filtro escola
- [x] UserListPage: filtro escola + perfil + status
- [x] LessonRecordListPage: filtro escola + turma + data
- [x] AttendanceBulkPage: filtro escola
- [x] GradeBulkPage: filtro escola
- [x] ClosingDashboardPage: filtro escola
- [x] AnnualResultPage: filtro escola + ano letivo + turma
- [x] FinalResultPage: filtro escola + turma + aluno
- [x] TeacherAgendaPage: filtro escola + professor
- [x] Persistencia de filtros na URL via query params (useListFilters)

### U.3 Acoes faltantes nas listas

- [x] AcademicYearListPage: possui editar, excluir, busca e criar
- [x] AssessmentPeriodListPage: possui editar, excluir, busca e criar
- [x] LessonRecordListPage: possui editar, excluir, busca e criar
- [x] TeacherListPage: possui editar, excluir, busca e criar
- [x] RoleListPage: possui editar, excluir, busca e criar
- [x] ShiftListPage: possui editar, excluir, busca e criar

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

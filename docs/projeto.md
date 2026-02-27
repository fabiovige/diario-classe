# Analise do Projeto - Diario de Classe Digital

**Data:** 2026-02-27
**Repositorio:** https://github.com/fabiovige/diario-classe
**Analista:** Claude (Anthropic)

---

## Veredicto Geral

Projeto muito acima da media. Arquitetura de sistema educacional seria, pensada para escalar para 30 escolas reais de Jandira-SP. Os gaps identificados sao features incrementais, nao problemas arquiteturais. A base e muito solida.

---

## O que esta excelente

### Arquitetura Modular Monolith com Bounded Contexts

10+ modulos (Identity, SchoolStructure, People, Enrollment, Curriculum, AcademicCalendar, Attendance, ClassRecord, Assessment, PeriodClosing) cada um com camadas bem definidas: Domain → Application → Infrastructure → Presentation. Disciplina rara em projetos Laravel.

### Motor de calculo de notas desacoplado

`GradeCalculationStrategy` com interface + implementacoes (NumericGradeCalculation, ConceptualGradeCalculation, DescriptiveGradeCalculation) + Factory. Media aritmetica, ponderada, recuperacao com regras configuraveis (higher, average, last). Tudo parametrizado via `AssessmentConfig` por escola/ano/serie, nao hardcoded.

### Modelo de dados robusto

- `assessment_configs` ligado a school + academic_year + grade_level com unique constraint
- `grades` referenciando student + class_group + teacher_assignment + assessment_period + assessment_instrument
- `period_averages` como tabela materializada do calculo
- `final_averages` para o resultado do ano

### Tela de lancamento em lote (GradeBulkPage)

UX correta: seleciona turma → carrega dependencias em cascata (disciplinas, periodos, instrumentos, alunos) → preenche notas → salva em bulk. Inclui clampGrade(), carregamento de notas existentes, suporte a conceitual e numerico.

### Boletim (ReportCardPage)

Filtro por materia e periodo, grafico radar de desempenho, cards de frequencia, tabela de notas com cores por situacao, relatorios descritivos para Ed. Infantil, exportacao PDF com jsPDF+autoTable.

### Workflow de fechamento de periodo

Modulo PeriodClosing com Specifications (AttendanceComplete, GradesComplete, LessonRecordsComplete), fluxo de retificacao com audit trail, calculo de resultado final.

### Testes e qualidade

PHPStan level 6, Laravel Pint, Pest com testes de feature para cada modulo, testes no frontend (Vitest). CLAUDE.md e TASKS.md mostram disciplina de projeto.

---

## Pontos de Atencao e Oportunidades

### 1. UX para professores leigos (PRIORIDADE ALTA)

O GradeBulkPage exige 4 selects antes de chegar nos alunos (turma → disciplina → periodo → instrumento). Para o professor de 50 anos, e muita decisao.

**Acao sugerida:** Criar rota `/teacher/today` que pre-seleciona tudo baseado no horario atual do professor. Fluxo contextual: abrir o app e ja estar na aula atual.

### 2. PDF server-side (PRIORIDADE ALTA)

PDF gerado apenas no frontend com jsPDF. Quando a secretaria precisar gerar 500 boletins de uma vez, ou o diario de classe completo de uma turma no ano inteiro, vai precisar de geracao server-side.

**Acao sugerida:** Implementar geracao via DomPDF ou Browsershot no backend, processamento em background via Laravel Queue. Endpoints:
- `POST /api/exports/report-cards/bulk` (gera ZIP com todos os boletins de uma turma)
- `POST /api/exports/class-diary/{classGroupId}` (diario completo do ano)
- `GET /api/exports/{id}/download` (baixa o arquivo gerado)

### 3. Export Excel (PRIORIDADE ALTA)

Nenhum endpoint ou utilitario de exportacao Excel existe. A coordenacao vai pedir "mapa de notas da turma em Excel" e "lista de alunos abaixo da media".

**Acao sugerida:** Instalar Laravel Excel (Maatwebsite). Relatorios necessarios:
- Mapa de notas por turma/periodo (todas as disciplinas lado a lado)
- Alunos abaixo da media (filtro por escola/serie/periodo)
- Frequencia por turma (faltas por aluno/mes)
- Ata de resultados finais
- Diario de classe completo

### 4. Offline / PWA (PRIORIDADE MEDIA)

O PRD menciona "suporte offline" mas o frontend nao e um PWA.

**Acao sugerida:** Configurar service worker com Workbox, cache de dados das turmas do professor, fila de sync para chamada e notas lancadas offline. Essencial para escolas com internet instavel.

### 5. Alertas proativos para o professor (PRIORIDADE MEDIA)

Faltam notificacoes como:
- "3 notas pendentes para fechar o 2o bimestre"
- "5 alunos com mais de 10 faltas"
- "Periodo encerra em 5 dias e voce tem lancamentos pendentes"

**Acao sugerida:** Criar endpoint `GET /api/teacher/alerts` que retorna alertas agregados. Exibir no TeacherDashboard com badges e cards de acao rapida.

### 6. Portal do responsavel (PRIORIDADE FUTURA)

PRD preve 10.000+ pais. Modulo ainda nao existe no frontend.

**Acao sugerida:** Fase futura conforme TASKS.md. Considerar: boletim via link publico (token temporario), notificacoes via WhatsApp (API Business), tela simplificada mobile-first.

### 7. LGPD - Criptografia de campos sensiveis (PRIORIDADE MEDIA)

RBAC implementado, mas campos sensiveis (laudo, saude, disability_type) ainda sem criptografia em repouso conforme regras de negocio.

**Acao sugerida:** Usar Laravel `encrypted` cast nos campos sensiveis do Student. Implementar log de acesso a dados pessoais (quem consultou, quando).

---

## Resumo Comparativo

| Aspecto | Esperado | Implementado | Status |
|---|---|---|---|
| Motor de notas configuravel | Sim | Strategy Pattern | OK |
| Multiplos modelos (numerico/conceitual/descritivo) | Sim | Sim | OK |
| Recuperacao configuravel | Sim | higher/average/last | OK |
| Lancamento em lote | Sim | GradeBulkPage | OK |
| Boletim individual | Sim | ReportCardPage + PDF | OK |
| Diario de classe (visao ano) | Sim | GradeListPage (parcial) | PARCIAL |
| Export PDF server-side | Sim | Apenas client-side | PENDENTE |
| Export Excel | Sim | Ausente | PENDENTE |
| Fluxo contextual do professor | Sim | TeacherAgenda | OK |
| Alertas proativos | Sim | Ausente | PENDENTE |
| PWA/Offline | Sim | Ausente | PENDENTE |
| Fechamento de periodo | Sim | Specifications pattern | OK |
| Audit trail | Sim | Auditable trait | OK |
| Multi-escola | Sim | school_scope middleware | OK |
| LGPD | Sim | RBAC ok, criptografia pendente | PARCIAL |

---

## Proximos Passos Sugeridos (em ordem de prioridade)

1. **Rota `/teacher/today`** — Quick win que melhora drasticamente a UX do professor
2. **Export Excel** — Laravel Excel + endpoints para mapa de notas e frequencia
3. **PDF server-side** — DomPDF/Browsershot + Queue para geracao em lote
4. **Alertas proativos** — Endpoint de alertas + cards no dashboard do professor
5. **Criptografia LGPD** — Encrypted casts nos campos sensiveis
6. **PWA** — Service worker + Workbox para suporte offline
7. **Portal do responsavel** — Fase futura, boletim via link + WhatsApp

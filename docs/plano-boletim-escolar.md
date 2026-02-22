# Plano: Boletim Escolar do Aluno

## Objetivo

Reescrever a tela de Boletim Escolar (`ReportCardPage.vue`) para ser uma pagina completa de consulta de desempenho do aluno, com:
- Cabecalho com dados do aluno e avatar com iniciais
- Grafico radar comparativo das materias
- Tabela de notas por bimestre com media final
- Cards de frequencia
- Filtros por materia e periodo
- Exportacao em PDF
- Acesso por todos os perfis: admin, director, coordinator, teacher

## Estado Atual

### O que existe
- **Backend**: `GET /api/report-cards/student/{studentId}` retorna `period_averages`, `final_averages` e `descriptive_reports` (sem relacoes detalhadas)
- **Frontend**: `ReportCardPage.vue` basica - apenas tabela de notas e cards de frequencia, sem filtros, sem graficos, sem PDF
- **Dados**: Notas lancadas para o 1o Bimestre 2025 (ensino fundamental), escala 0-10, media 6.0 para aprovacao
- **Foto**: Modelo `Student` NAO possui campo de foto/avatar

### O que falta
- Backend: endpoint retorna IDs crus nas `period_averages` (sem nome da materia/periodo)
- Frontend: chart library (nenhuma instalada)
- Frontend: PDF generation (nenhuma instalada)
- Frontend: filtros, grafico radar, layout completo

## Dependencias a instalar

| Pacote | Onde | Motivo |
|---|---|---|
| `chart.js` | frontend | Motor de graficos |
| `vue-chartjs` | frontend | Wrapper Vue 3 para Chart.js |
| `html2canvas` | frontend | Captura de tela para PDF |
| `jspdf` | frontend | Geracao de PDF no client-side |

> PDF gerado no frontend (client-side) evita instalar lib PHP e simplifica deploy. O `html2canvas` + `jsPDF` capturam a area visivel e geram o arquivo.

---

## Implementacao

### Fase 1: Backend - Enriquecer endpoint do boletim

**Arquivo**: `backend/app/Modules/Assessment/Presentation/Controllers/AssessmentController.php`

Reescrever o metodo `reportCard()` para retornar dados estruturados para consumo direto pelo frontend:

```
GET /api/report-cards/student/{studentId}
```

**Response esperada**:
```json
{
  "student": {
    "id": 1,
    "name": "Joao Silva",
    "display_name": "Joao",
    "birth_date": "2015-03-10",
    "class_group": {
      "id": 5,
      "label": "5o Ano - A - Manha"
    },
    "enrollment_number": "2025-001234"
  },
  "assessment_periods": [
    { "id": 1, "name": "1o Bimestre", "number": 1 },
    { "id": 2, "name": "2o Bimestre", "number": 2 },
    { "id": 3, "name": "3o Bimestre", "number": 3 },
    { "id": 4, "name": "4o Bimestre", "number": 4 }
  ],
  "subjects": [
    {
      "teacher_assignment_id": 10,
      "name": "Portugues",
      "teacher_name": "Maria Souza",
      "knowledge_area": "Linguagens",
      "periods": {
        "1": { "average": 7.5, "absences": 2 },
        "2": { "average": null, "absences": 0 },
        "3": { "average": null, "absences": 0 },
        "4": { "average": null, "absences": 0 }
      },
      "final_average": null,
      "recovery_grade": null,
      "final_grade": null,
      "total_absences": 2,
      "frequency_percentage": 95.0,
      "status": "pending"
    }
  ],
  "descriptive_reports": [
    {
      "experience_field": "O eu, o outro e nos",
      "period": "1o Bimestre",
      "content": "Texto descritivo..."
    }
  ],
  "summary": {
    "total_subjects": 8,
    "passing_grade": 6.0,
    "scale_max": 10.0,
    "grade_type": "numeric"
  }
}
```

**Alteracoes no controller**:
1. Buscar matricula ativa do aluno (`Enrollment` com `classAssignments` ativas) para obter `class_group`
2. Buscar `TeacherAssignment` ativos da turma, com eager load de `curricularComponent` e `teacher.user`
3. Buscar `PeriodAverage` agrupados por `teacher_assignment_id` e `assessment_period.number`
4. Buscar `FinalAverage` por `teacher_assignment_id`
5. Buscar `AssessmentConfig` da escola/nivel para saber `passing_grade`, `scale_max`, `grade_type`
6. Montar response estruturada sem criar Resource separado (array direto)

**Arquivo**: `backend/routes/api.php`
- Rota ja existe: `GET report-cards/student/{studentId}` — nao precisa alterar

---

### Fase 2: Frontend - Instalar dependencias

```bash
cd frontend && npm install chart.js vue-chartjs jspdf html2canvas
```

Rebuild do container apos install.

---

### Fase 3: Frontend - Componente RadarChart

**Arquivo novo**: `frontend/src/shared/components/RadarChart.vue`

Componente wrapper reutilizavel:

```
Props:
  - labels: string[]         (nomes das materias)
  - datasets: Dataset[]      (dados por bimestre ou media)
  - height: number = 300

Dataset: { label: string, data: number[], borderColor: string, backgroundColor: string }
```

Usando `vue-chartjs` com `Radar` do Chart.js. Configuracao:
- Scale de 0 a 10 (ou `scale_max` do config)
- Linha vermelha pontilhada no 6.0 (passing grade) como plugin
- Labels das materias ao redor
- Legenda no topo
- Responsivo

---

### Fase 4: Frontend - Reescrever ReportCardPage.vue

**Arquivo**: `frontend/src/modules/assessment/pages/ReportCardPage.vue`

#### Layout da pagina

```
+----------------------------------------------------------+
| < Voltar                     Boletim Escolar              |
+----------------------------------------------------------+
|                                                           |
| +------+ +--------------------------------------------+  |
| | [AB] | | Joao Silva                                  |  |
| | init | | 5o Ano - A - Manha | Mat: 2025-001234      |  |
| +------+ +--------------------------------------------+  |
|                                                           |
| Filtros:                                                  |
| [Materia v]  [Periodo v]        [Limpar]  [Exportar PDF]  |
|                                                           |
| +------------------------+ +---------------------------+  |
| |    Grafico Radar       | |   Cards de Frequencia     |  |
| |                        | |  Total | Pres | Falt | %  |  |
| +------------------------+ +---------------------------+  |
|                                                           |
| +------------------------------------------------------+  |
| | Tabela de Notas                                      |  |
| | Materia | 1oB | 2oB | 3oB | 4oB | Media | Situacao |  |
| | Port.   | 7.5 | --  | --  | --  |  --   | Cursando |  |
| | Mat.    | 8.0 | --  | --  | --  |  --   | Cursando |  |
| +------------------------------------------------------+  |
|                                                           |
| +------------------------------------------------------+  |
| | Relatorios Descritivos (se Ed. Infantil)             |  |
| | Campo | Periodo | Conteudo                           |  |
| +------------------------------------------------------+  |
+----------------------------------------------------------+
```

#### Cabecalho do aluno
- Avatar circular com iniciais (2 primeiras letras do nome) em background azul `#0078D4`
- Nome completo (display_name se existir)
- Turma (label composta) e numero de matricula
- Sem foto (campo nao existe no modelo)

#### Filtros (acima do grafico)
- **Materia**: `Select` com opcoes vindas de `subjects[]`, placeholder "Todas as materias", `filter`, `showClear`
  - Ao filtrar: tabela mostra so a materia selecionada, radar destaca a materia
- **Periodo**: `Select` com opcoes vindas de `assessment_periods[]`, placeholder "Todos os periodos", `showClear`
  - Ao filtrar: tabela mostra so a coluna do periodo, radar mostra so aquele bimestre
- Botao "Limpar filtros" (visivel quando filtro ativo)
- Botao "Exportar PDF" (chama funcao de geracao)

#### Grafico Radar
- Um dataset por bimestre que tenha dados (ex: "1o Bimestre" em azul)
- Labels = nomes das materias
- Escala 0 a `summary.scale_max`
- Se filtro de materia ativo: radar continua mostrando todas mas destaca a selecionada
- Se filtro de periodo ativo: mostra so o dataset daquele periodo
- Renderizado apenas para `grade_type === 'numeric'`

#### Cards de Frequencia
- Grid com 4 cards (mesmo padrao atual): Total Aulas, Presencas, Faltas, % Frequencia
- Valores calculados somando `total_absences` de todos os subjects (ou do filtrado)
- Se filtro de materia ativo: mostra frequencia daquela materia
- Se filtro de periodo ativo: mostra frequencia daquele periodo

#### Tabela de Notas
- Colunas: Materia | Professor | 1oB | 2oB | 3oB | 4oB | Media Final | Situacao
- Coluna "Professor" visivel (util para coordenadores/diretores)
- Notas exibidas com 1 casa decimal
- Notas abaixo de `passing_grade` em vermelho `#C42B1C`
- Notas acima de `passing_grade` em verde `#0F7B0F`
- Celulas sem nota: "--"
- Coluna "Situacao": Aprovado (verde) | Reprovado (vermelho) | Cursando (cinza) — baseado no `status` do final_average
- Se filtro de materia ativo: mostra so a linha daquela materia
- Se filtro de periodo ativo: mostra so a coluna daquele bimestre + media

#### Secao Descritivos (condicional)
- Visivel apenas se `descriptive_reports.length > 0`
- Tabela simples: Campo de Experiencia | Periodo | Conteudo
- Truncar conteudo com tooltip no hover

#### Exportacao PDF
- Botao "Exportar PDF" na toolbar de filtros
- Usa `html2canvas` para capturar o container `#report-card-content`
- Gera PDF A4 paisagem via `jsPDF`
- Nome do arquivo: `boletim_{nome_aluno}_{ano}.pdf`
- Antes de capturar: remover botoes e filtros da area de captura (usar `ref` para a area printavel)

---

### Fase 5: Acesso por perfis

**Rota atual**: `/assessment/report-card/:studentId`
- Ja tem `roles: ['admin', 'coordinator', 'teacher']` — adicionar `'director'`

Nenhuma alteracao no backend de autenticacao necessaria (endpoint ja usa `auth:sanctum` global).

---

## Arquivos a modificar/criar

| Arquivo | Acao | Descricao |
|---|---|---|
| `backend/app/Modules/Assessment/Presentation/Controllers/AssessmentController.php` | Modificar | Reescrever `reportCard()` com dados estruturados |
| `frontend/package.json` | Modificar | Adicionar chart.js, vue-chartjs, jspdf, html2canvas |
| `frontend/src/shared/components/RadarChart.vue` | Criar | Componente wrapper do Radar Chart |
| `frontend/src/modules/assessment/pages/ReportCardPage.vue` | Reescrever | Pagina completa com cabecalho, filtros, radar, tabela, PDF |
| `frontend/src/router/index.ts` | Modificar | Adicionar 'director' no roles da rota report-card |
| `frontend/src/types/assessment.ts` | Modificar | Adicionar tipo `ReportCardResponse` |

## Verificacao

1. `docker compose exec app composer test`
2. `docker compose exec app composer analyse`
3. `docker compose up -d --build --force-recreate frontend`
4. Acessar http://localhost:3015 > Alunos > Clicar aluno > Boletim
5. Verificar cabecalho com iniciais e dados do aluno
6. Verificar grafico radar com notas do 1o bimestre
7. Verificar tabela com notas e situacao
8. Filtrar por materia → radar e tabela filtram
9. Filtrar por periodo → radar e tabela filtram
10. Clicar "Exportar PDF" → PDF baixado com conteudo correto
11. Testar com perfis: admin, director, coordinator, teacher

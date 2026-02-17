# Convencoes de Codigo

## PHP / Laravel (Backend)

### Nomenclatura

- Classes: PascalCase (`StudentEnrollment`, `GradeCalculator`)
- Metodos: camelCase (`calculateAverage`, `findByStudent`)
- Variaveis: camelCase (`$academicYear`, `$gradeValue`)
- Constantes: UPPER_SNAKE_CASE (`MINIMUM_FREQUENCY`)
- Tabelas: snake_case plural (`students`, `academic_years`, `assessment_rules`)
- Colunas: snake_case (`first_name`, `academic_year_id`)
- Migrations: Laravel default (`2026_02_17_000000_create_students_table`)

### Estrutura

- Sem comentarios no codigo
- Sem `else` — usar early return e guard clauses
- Sem codigo duplicado — extrair para service/trait/helper
- Um UseCase por arquivo, uma responsabilidade
- FormRequest para validacao de input
- API Resource para formatacao de output
- Testes obrigatorios para toda regra de dominio

### Testes

- Framework: Pest (preferencialmente) + PHPUnit
- Nomenclatura: `it('should calculate average for numeric grades')`
- Cobertura minima de domain layer
- Factories para geracao de dados de teste

## TypeScript / Vue.js (Frontend)

### Nomenclatura

- Componentes: PascalCase (`StudentList.vue`, `AttendanceGrid.vue`)
- Composables: camelCase com prefixo use (`useAttendance`, `useGrades`)
- Stores: camelCase (`useStudentStore`)
- Types/Interfaces: PascalCase (`Student`, `AttendanceRecord`)
- Arquivos de rota: kebab-case (`academic-calendar.ts`)

### Estrutura

- Composition API (setup script) — NAO usar Options API
- TypeScript obrigatorio
- Componentes pequenos e focados
- Logica de negocio em composables, nao em componentes
- Stores Pinia para estado compartilhado
- Sem any — tipar tudo

### Testes

- Framework: Vitest + Testing Library
- Testar composables e logica, nao implementacao de UI

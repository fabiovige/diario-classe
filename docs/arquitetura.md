# Arquitetura

## Stack Tecnologico

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8+ / Laravel 11+ |
| Frontend | Vue.js 3 (Composition API) + TypeScript + Vite |
| Banco de dados | PostgreSQL 16+ |
| Cache | Redis |
| Testes Backend | Pest + PHPUnit |
| Testes Frontend | Vitest + Testing Library |
| CI/CD | GitHub Actions |
| Container | Docker / Docker Compose |

## Principios

- SOLID
- Clean Architecture
- DDD pragmatico
- Modular Monolith (bounded contexts)

## Camadas por Modulo

```
Presentation   → Controllers, FormRequests, API Resources
Application    → UseCases, DTOs, Events, Listeners
Domain         → Entities, Value Objects, Repository Interfaces, Rules Engine
Infrastructure → Eloquent Repositories, External Services, Queue Jobs
```

## Regras Arquiteturais

- Regras de negocio ficam APENAS na camada Domain (nunca em controllers/views)
- Vinculos sempre modelados considerando o ano letivo
- Dados educacionais imutaveis apos fechamento oficial
- Toda alteracao sensivel gera audit trail
- Regras de avaliacao e frequencia sao desacopladas entre si
- NAO propor microservicos prematuros
- Toda regra educacional (avaliacao, frequencia, calendario) e configuravel via motor de regras, NAO hardcoded

## Bounded Contexts

```
┌────────────────────────────────────────────────────────────────┐
│                    Diario de Classe Digital                     │
├──────────────┬──────────────┬──────────────┬──────────────────┤
│   Identity   │   School     │   People     │   Enrollment     │
│   (IAM)      │   Structure  │              │                  │
├──────────────┼──────────────┼──────────────┼──────────────────┤
│  Curriculum  │  Academic    │  Attendance  │   Class          │
│              │  Calendar    │              │   Record         │
├──────────────┼──────────────┼──────────────┼──────────────────┤
│  Assessment  │  Council     │  Period      │   Document       │
│  (motor de   │  (conselho)  │  Closing     │   & Export       │
│   regras)    │              │              │                  │
├──────────────┴──────────────┴──────────────┴──────────────────┤
│                    Audit (transversal)                          │
├───────────────────────────────────────────────────────────────┤
│                    Notification (transversal)                   │
└───────────────────────────────────────────────────────────────┘
```

## Estrutura de Pastas (Backend)

```
backend/
├── app/
│   └── Modules/
│       ├── Identity/
│       │   ├── Domain/
│       │   │   ├── Entities/
│       │   │   ├── ValueObjects/
│       │   │   └── Repositories/      (interfaces)
│       │   ├── Application/
│       │   │   ├── UseCases/
│       │   │   ├── DTOs/
│       │   │   └── Events/
│       │   ├── Infrastructure/
│       │   │   ├── Repositories/      (eloquent)
│       │   │   └── Providers/
│       │   └── Presentation/
│       │       ├── Controllers/
│       │       ├── Requests/
│       │       └── Resources/
│       ├── SchoolStructure/
│       ├── People/
│       ├── Enrollment/
│       ├── Curriculum/
│       ├── AcademicCalendar/
│       ├── Attendance/
│       ├── ClassRecord/
│       ├── Assessment/
│       ├── Council/
│       ├── PeriodClosing/
│       ├── Document/
│       └── Shared/
│           ├── Audit/
│           └── Notification/
├── config/
├── database/
├── routes/
└── tests/
```

## Estrutura de Pastas (Frontend)

```
frontend/
├── src/
│   ├── modules/
│   │   ├── identity/
│   │   │   ├── components/
│   │   │   ├── composables/
│   │   │   ├── pages/
│   │   │   ├── routes.ts
│   │   │   ├── store.ts
│   │   │   └── types.ts
│   │   ├── school-structure/
│   │   ├── people/
│   │   ├── enrollment/
│   │   ├── curriculum/
│   │   ├── academic-calendar/
│   │   ├── attendance/
│   │   ├── class-record/
│   │   ├── assessment/
│   │   ├── council/
│   │   ├── period-closing/
│   │   └── document/
│   ├── shared/
│   │   ├── components/     (design system)
│   │   ├── composables/
│   │   ├── layouts/
│   │   └── utils/
│   ├── router/
│   ├── stores/
│   └── App.vue
├── public/
└── tests/
```

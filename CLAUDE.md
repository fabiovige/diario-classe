# CLAUDE.md

Guia para Claude Code neste repositorio.

## Projeto

Sistema de Diario de Classe Digital para rede municipal de ensino (Jandira-SP).
Multi-escola (30 EMEBs), multi-ano letivo. Ed. Infantil + Ensino Fundamental.

## Stack

- **Backend**: PHP 8.4 / Laravel 12 / MySQL 8.0 / Redis
- **Infra Dev**: Docker Compose (app + mysql + redis)
- **Frontend**: Vue.js 3 (Composition API) + TypeScript + Vite
- **Arquitetura**: Modular Monolith (bounded contexts)
- **Principios**: SOLID, Clean Architecture, DDD pragmatico
- **Testes**: Pest (backend) + Vitest (frontend)
- **Qualidade**: PHPStan level 6 (Larastan) + Laravel Pint

## Estrutura do Projeto

```text
diario-classe/
├── backend/              # Laravel 12 (app/Modules/ por bounded context)
│   ├── app/Modules/      # 13 modulos + Shared (Audit, Notification)
│   ├── database/         # Migrations, seeders, factories
│   └── tests/            # Pest + PHPUnit
├── frontend/             # Vue.js 3 (em construcao)
├── docs/                 # Documentacao (ver indice abaixo)
├── TASKS.md              # Checklist de tarefas por fase
├── README.md             # Setup, scripts, como rodar
└── CLAUDE.md             # Este arquivo (indice)
```

## Docker (ambiente dev)

```bash
docker compose up -d --build          # subir ambiente
docker compose exec app composer install  # instalar deps
docker compose exec app php artisan migrate:fresh --seed  # migrations + seeders
docker compose exec app composer test     # rodar testes
docker compose down                       # parar ambiente
```

Acesso:
- **Frontend**: http://localhost:3015
- **Backend API**: http://localhost:5015

## Scripts Backend

Executar dentro do container (`docker compose exec app`) ou local em `backend/`:

- `composer test` — Pest + PHPUnit
- `composer analyse` — PHPStan level 6
- `composer lint` — Laravel Pint (corrige)
- `composer lint:check` — Laravel Pint (verifica)
- `php artisan serve` — servidor dev (http://localhost:5015)

## Regras Inviolaveis

1. Regras de negocio ficam APENAS na camada Domain
2. Toda regra educacional e **configuravel** (motor de regras), NUNCA hardcoded
3. Dados imutaveis apos fechamento oficial de periodo
4. Toda alteracao sensivel gera audit trail
5. NUNCA excluir dados sem permissao explicita do usuario
6. Sem comentarios no codigo
7. Sem `else` — early return e guard clauses
8. Executar todos os testes apos qualquer alteracao
9. Nunca implementar sem autorizacao do usuario
10. CRUD sempre em pagina separada (*FormPage.vue), NUNCA popup/dialog
11. Toda lista deve ter coluna Acoes (editar/excluir) e busca
12. Enums devem ter values em ingles (backend) mas exibicao SEMPRE em PT-BR (labels). Nunca mostrar o value cru ao usuario
13. Sempre rodar rebuild do frontend (`docker compose up -d --build --force-recreate frontend`) apos alteracoes no frontend
14. Campos de selecao multipla (permissoes, tags, etc) devem exibir opcoes explicitamente (checkboxes/listagem), NUNCA campo de digitacao livre (Chips/text). O usuario precisa ver todas as opcoes disponiveis

## Documentacao (docs/)

| Arquivo | Conteudo |
|---|---|
| [arquitetura.md](docs/arquitetura.md) | Stack, camadas, bounded contexts, estrutura de pastas |
| [regras-negocio.md](docs/regras-negocio.md) | Regras de dominio: matricula, frequencia, avaliacao, fechamento, LGPD |
| [design-patterns.md](docs/design-patterns.md) | Patterns aplicados: Strategy, Observer, Repository, State, etc |
| [convencoes-codigo.md](docs/convencoes-codigo.md) | Nomenclatura, estrutura de arquivos, padroes de teste |
| [PRD-jandira.md](docs/PRD-jandira.md) | PRD completo: requisitos funcionais/nao-funcionais, personas, metricas |
| [passos-implementacao.md](docs/passos-implementacao.md) | Roadmap de implementacao por fase |
| [diario-classe.md](docs/diario-classe.md) | Visao geral original do sistema |
| [diretrizes-educacionais-sp.md](docs/diretrizes-educacionais-sp.md) | Referencia legado de legislacao (usar apenas como contexto) |
| [design-ui.md](docs/design-ui.md) | Padronizacao de layout: regras UI, tokens Fluent, status de cada pagina |

### Documentacao Pedagogica (docs/pedagogia/)

| Arquivo | Conteudo |
|---|---|
| [ciclo-matricula.md](docs/pedagogia/ciclo-matricula.md) | Ciclo de vida da matricula: status, transicoes, cancelamento, reativacao |
| [enturmacao.md](docs/pedagogia/enturmacao.md) | Regras de enturmacao: alocacao em turma, vagas, movimentacoes |
| [frequencia-faltas.md](docs/pedagogia/frequencia-faltas.md) | Controle de frequencia, limiares de alerta, compensacao, busca ativa |
| [avaliacao-notas.md](docs/pedagogia/avaliacao-notas.md) | Modelos avaliativos por nivel, instrumentos, recuperacao, conselho de classe |
| [etapas-series.md](docs/pedagogia/etapas-series.md) | Etapas educacionais, faixas etarias, data de corte, progressao |
| [fechamento-periodo.md](docs/pedagogia/fechamento-periodo.md) | Workflow de fechamento, imutabilidade, retificacao, resultado final |
| [documentacao-escolar.md](docs/pedagogia/documentacao-escolar.md) | Documentos obrigatorios, matricula condicional, LGPD |

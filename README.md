# Diario de Classe Digital

Sistema de Diario de Classe para rede municipal de ensino.

## Requisitos

- PHP 8.2+
- Composer 2+
- PostgreSQL 16+
- Redis
- Node.js 20+ (frontend)

## Setup do Backend

```bash
cd backend

cp .env.example .env

# editar .env com suas credenciais de banco
# DB_DATABASE=diario_classe
# DB_USERNAME=diario
# DB_PASSWORD=diario

composer install

php artisan key:generate

php artisan migrate
```

## Subir o servidor

```bash
cd backend

# servidor de desenvolvimento
php artisan serve

# acessa em http://localhost:8000
```

## Scripts disponiveis

Todos os comandos abaixo devem ser executados dentro da pasta `backend/`.

| Comando | Descricao |
|---|---|
| `composer test` | Roda todos os testes (Pest + PHPUnit) |
| `composer analyse` | Roda PHPStan (analise estatica, level 6) |
| `composer lint` | Corrige code style automaticamente (Pint) |
| `composer lint:check` | Verifica code style sem corrigir |
| `composer dev` | Sobe servidor + queue + logs + vite simultaneamente |
| `composer setup` | Setup completo (install, key, migrate, npm) |

### Testes

```bash
# rodar todos os testes
composer test

# rodar um teste especifico
./vendor/bin/pest tests/Feature/Audit/AuditLogTest.php

# rodar com filtro por nome
./vendor/bin/pest --filter="creates an audit log"
```

### PHPStan (analise estatica)

```bash
composer analyse
```

### Lint (code style)

```bash
# verificar sem alterar
composer lint:check

# corrigir automaticamente
composer lint
```

## Estrutura do Projeto

```
diario-classe/
├── backend/              # Laravel 12 + PHP 8.4
│   ├── app/Modules/      # Modular Monolith (bounded contexts)
│   ├── database/         # Migrations, seeders, factories
│   └── tests/            # Pest + PHPUnit
├── frontend/             # Vue.js 3 (em construcao)
├── docs/                 # Documentacao arquitetural
├── TASKS.md              # Checklist de tarefas
└── CLAUDE.md             # Guia para Claude Code
```

## Documentacao

- [Arquitetura](docs/arquitetura.md)
- [Regras de Negocio](docs/regras-negocio.md)
- [Design Patterns](docs/design-patterns.md)
- [Convencoes de Codigo](docs/convencoes-codigo.md)
- [PRD - Jandira](docs/PRD-jandira.md)
- [Passos de Implementacao](docs/passos-implementacao.md)

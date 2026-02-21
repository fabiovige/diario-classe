# Design UI - Padronizacao de Layout

Regras e padroes obrigatorios para toda interface do sistema.

## Regra 1: CRUD sempre em pagina separada

Criar e editar SEMPRE abrem uma **pagina dedicada** (`*FormPage.vue`), NUNCA popup/dialog.

- Criar: `router.push('/{modulo}/{entidade}/new')`
- Editar: `router.push('/{modulo}/{entidade}/{id}/edit')`
- Excluir: confirmacao inline via `useConfirm()` na propria lista

**Proibido**: usar `FormDialog` ou qualquer modal para formularios de criacao/edicao.

## Regra 2: Toda lista deve ter coluna Acoes

Toda `*ListPage.vue` com DataTable deve ter:

```vue
<Column header="Acoes" :style="{ width: '120px' }">
  <template #body="{ data }">
    <Button icon="pi pi-pencil" text rounded class="mr-1" @click="router.push(`/{modulo}/{entidade}/${data.id}/edit`)" />
    <Button icon="pi pi-trash" text rounded severity="danger" @click="handleDelete(data)" />
  </template>
</Column>
```

Excecoes (sem excluir por regra de negocio):
- Alunos, Responsaveis: dados protegidos
- Matriculas: apenas visualizar detalhe
- Niveis de Ensino: dados de referencia do sistema

## Regra 3: Toolbar padrao

Toda lista deve ter:

```vue
<Toolbar class="mb-4 border-none bg-transparent p-0">
  <template #start>
    <InputText v-model="search" placeholder="Buscar..." @keyup.enter="onSearch" />
    <Button icon="pi pi-search" class="ml-2" @click="onSearch" />
  </template>
  <template #end>
    <Button label="Novo {Entidade}" icon="pi pi-plus" @click="router.push('/{modulo}/{entidade}/new')" />
  </template>
</Toolbar>
```

## Regra 4: Estrutura de pagina

```
<div class="p-6">
  <h1 class="mb-6 text-2xl font-semibold text-fluent-primary">{Titulo}</h1>
  <div class="rounded-lg border border-fluent-border bg-white p-6 shadow-sm">
    <!-- Toolbar -->
    <!-- EmptyState ou DataTable -->
    <!-- Paginator -->
  </div>
</div>
```

## Regra 5: Tokens Fluent Design

Usar tokens semanticos em vez de cores hardcoded:

| Token | Substitui |
|---|---|
| `text-fluent-primary` | `text-[#0078D4]` |
| `border-fluent-border` | `border-[#E0E0E0]` |
| `text-fluent-text-secondary` | `text-[#616161]` |

## Regra 6: Formularios (FormPage)

- Titulo dinamico: "Novo {Entidade}" ou "Editar {Entidade}"
- Botao voltar: `router.push('/{modulo}/{entidade}')`
- Botao salvar: `Salvar` com loading state
- Largura maxima: `max-w-[700px] mx-auto`
- Error handling: `extractApiError(error, 'mensagem fallback')`

## Regra 7: Paginacao

Toda lista paginada deve ter:

```vue
<Paginator
  v-if="totalRecords > perPage"
  class="mt-4 border-t border-fluent-border pt-3"
  :rows="perPage"
  :totalRecords="totalRecords"
  :first="(currentPage - 1) * perPage"
  :rowsPerPageOptions="[10, 15, 25, 50]"
  @page="onPageChange"
/>
```

## Status Atual das Paginas

### Conformes (pagina separada + acoes)

| Pagina | Criar | Editar | Excluir | Busca |
|---|---|---|---|---|
| UserListPage | pagina | pagina | sim | sim |
| RoleListPage | pagina | pagina | sim | - |
| SchoolListPage | pagina | pagina | sim | sim |
| ClassGroupListPage | pagina | pagina | sim | sim |

### Precisam de correcao (dialog -> pagina)

| Pagina | Problema |
|---|---|
| ShiftListPage | Criar/editar via dialog |
| CurricularComponentListPage | Criar via dialog, sem editar/excluir |
| ExperienceFieldListPage | Criar via dialog, sem editar/excluir |
| TeacherAssignmentListPage | Criar via dialog, sem editar/excluir |

### Precisam de acoes adicionais

| Pagina | Falta |
|---|---|
| AcademicYearListPage | Excluir |
| AssessmentPeriodListPage | Excluir |
| LessonRecordListPage | Excluir |
| TeacherListPage | Botao criar |
| EnrollmentListPage | Editar (abre detalhe) |
| GradeLevelListPage | Somente leitura (sem acoes) |
| RoleListPage | Busca |
| ShiftListPage | Busca |

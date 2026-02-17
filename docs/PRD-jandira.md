# PRD - Diario de Classe Digital
## Municipio de Jandira-SP

**Versao:** 1.0
**Data:** 2026-02-17
**Status:** Draft

---

## 1. Visao do Produto

Sistema moderno de Diario de Classe Digital para a rede municipal de ensino de Jandira-SP, substituindo processos manuais/legados por uma plataforma web responsiva com suporte offline, garantindo rastreabilidade, auditoria e conformidade legal.

### 1.1 Problema

- Diarios de classe em papel ou sistemas legados fragmentados
- Dificuldade de consolidar dados de 30 escolas municipais
- Falta de visibilidade em tempo real para gestores e Secretaria de Educacao
- Risco de perda de dados e inconsistencias
- Dificuldade no cumprimento de prazos de prestacao de contas (Educacenso, TCE-SP)
- Comunicacao deficiente entre escola e familia sobre rendimento e frequencia

### 1.2 Solucao

Plataforma web moderna (Vue.js 3 + Laravel + PostgreSQL) com:
- Motor de regras configuravel (avaliacao, frequencia, calendario)
- Multi-escola com gestao centralizada pela Secretaria de Educacao
- Portais dedicados por perfil (professor, gestor, responsavel)
- Auditoria completa e imutabilidade de dados oficiais
- Exportacao para orgaos de controle

### 1.3 Publico-Alvo

| Perfil | Quantidade Estimada | Uso Principal |
|---|---|---|
| Professores | 300-500 | Lancamento diario de frequencia, notas, conteudo |
| Diretores | ~30 | Acompanhamento, aprovacao de fechamentos |
| Coordenadores Pedagogicos | ~30-60 | Monitoramento pedagogico, conselho de classe |
| Secretarios Escolares | ~30 | Matricula, enturmacao, documentos |
| Secretaria Municipal de Educacao | ~10-20 | Indicadores, politicas publicas, prestacao de contas |
| Responsaveis/Pais | ~10.000+ | Consulta de notas, frequencia, comunicados |

---

## 2. Escopo do Produto

### 2.1 Niveis de Ensino Atendidos

| Nivel | Faixa Etaria | Escolas | Modelo de Avaliacao |
|---|---|---|---|
| Creche | 0-3 anos | 9 unidades + mistas | Registro descritivo de desenvolvimento |
| Pre-escola | 4-5 anos | 3 unidades + mistas | Registro descritivo por campos de experiencia |
| Fundamental - Anos Iniciais | 6-10 anos (1o ao 5o) | 7 unidades + mistas | Configuravel (notas/conceitos) |
| Fundamental - Anos Finais | 11-14 anos (6o ao 9o) | Algumas EMEBs | Configuravel (notas/conceitos) |

**Nota:** Ensino Medio NAO esta no escopo (responsabilidade da rede estadual), mas a arquitetura deve permitir expansao futura.

### 2.2 Rede Municipal de Jandira

- **30 EMEBs** (Escolas Municipais de Educacao Basica)
- 7 exclusivas de Ensino Fundamental
- 9 exclusivas de Creche
- 3 exclusivas de Pre-escola
- 11 mistas (Pre-escola + Fundamental)
- **3 escolas com ensino integral** (EMEB Prof. Antonio Goncalves, EMEB Demilson Soares Molica, Complexo Maria Jose Moreau)
- Diretoria de Ensino vinculada: **DE Itapevi**

---

## 3. Requisitos Funcionais

### 3.1 Modulo: Identidade e Acesso (IAM)

| ID | Requisito | Prioridade |
|---|---|---|
| IAM-01 | Autenticacao segura (login/logout, refresh token, MFA opcional) | P0 |
| IAM-02 | RBAC com permissoes granulares por modulo e por escola | P0 |
| IAM-03 | Perfis: Admin, Secretario, Diretor, Coordenador, Professor, Responsavel | P0 |
| IAM-04 | Professor visualiza apenas suas turmas; Responsavel apenas seus filhos | P0 |
| IAM-05 | Gestao de usuarios pela Secretaria (criar, desativar, resetar senha) | P0 |
| IAM-06 | Log de autenticacao (tentativas de login, acessos) | P0 |

### 3.2 Modulo: Estrutura Escolar

| ID | Requisito | Prioridade |
|---|---|---|
| ESC-01 | Cadastro de escolas com dados basicos (nome, endereco, INEP, telefone) | P0 |
| ESC-02 | Configuracao de turnos por escola (manha, tarde, integral, noturno) | P0 |
| ESC-03 | Ano letivo como entidade central (abertura, fechamento, status) | P0 |
| ESC-04 | Series/anos vinculados ao ano letivo | P0 |
| ESC-05 | Turmas vinculadas a serie + turno + ano letivo | P0 |
| ESC-06 | Capacidade e lotacao de turmas | P1 |

### 3.3 Modulo: Pessoas

| ID | Requisito | Prioridade |
|---|---|---|
| PES-01 | Cadastro de alunos (nome, data nascimento, documentos, foto, NIS, necessidades especiais) | P0 |
| PES-02 | Cadastro de responsaveis legais com vinculo ao aluno | P0 |
| PES-03 | Cadastro de professores (formacao, habilitacoes, carga horaria) | P0 |
| PES-04 | Historico de vinculos (aluno-turma, professor-turma) por ano letivo | P0 |
| PES-05 | Marcacao de alunos com necessidades especiais / laudo (dado sensivel LGPD) | P1 |
| PES-06 | Conformidade LGPD: termos de consentimento digitais, anonimizacao | P1 |

### 3.4 Modulo: Matricula e Enturmacao

| ID | Requisito | Prioridade |
|---|---|---|
| MAT-01 | Matricula do aluno no ano letivo com data de entrada | P0 |
| MAT-02 | Enturmacao: vincular aluno a turma especifica | P0 |
| MAT-03 | Transferencia entre turmas da mesma escola | P0 |
| MAT-04 | Transferencia entre escolas da rede (com historico) | P1 |
| MAT-05 | Registro de movimentacao: entrada, saida, motivo (transferencia, abandono, falecimento) | P0 |
| MAT-06 | Reclassificacao e classificacao de alunos | P2 |
| MAT-07 | Lista de espera para vagas | P3 |

### 3.5 Modulo: Matriz Curricular

| ID | Requisito | Prioridade |
|---|---|---|
| CUR-01 | Configuracao de componentes curriculares por serie/ano | P0 |
| CUR-02 | Campos de experiencia como modelo separado para Ed. Infantil | P0 |
| CUR-03 | Carga horaria semanal por componente | P0 |
| CUR-04 | Vinculo professor-turma-componente | P0 |
| CUR-05 | Matriz configuravel por escola e ano letivo | P0 |
| CUR-06 | Suporte a parte diversificada do curriculo | P1 |
| CUR-07 | Catalogo de habilidades/objetivos por componente (referencia BNCC) | P2 |

### 3.6 Modulo: Calendario Escolar

| ID | Requisito | Prioridade |
|---|---|---|
| CAL-01 | Calendario por escola e ano letivo | P0 |
| CAL-02 | Tipos de dia configuraveis: letivo, nao-letivo, reposicao, recesso, feriado | P0 |
| CAL-03 | Periodos avaliativos configuraveis (bimestre, trimestre, semestre) | P0 |
| CAL-04 | Contador automatico de dias letivos e carga horaria acumulada | P0 |
| CAL-05 | Eventos: conselho de classe, reuniao de pais, formacao docente | P1 |
| CAL-06 | Validacao configuravel de minimo de dias letivos e carga horaria | P1 |

### 3.7 Modulo: Frequencia

| ID | Requisito | Prioridade |
|---|---|---|
| FRQ-01 | Registro de presenca/falta por aula (interface rapida para o professor) | P0 |
| FRQ-02 | Calculo automatico de percentual de frequencia por aluno | P0 |
| FRQ-03 | Registro de justificativas de falta (com upload de documento) | P1 |
| FRQ-04 | Motor de alertas configuravel: limiares de faltas parametrizaveis por escola | P1 |
| FRQ-05 | Alerta de faltas consecutivas/alternadas (parametros configuraveis) | P1 |
| FRQ-06 | Registro de busca ativa: tentativas de contato com familia | P1 |
| FRQ-07 | Compensacao de ausencias: workflow com registro de atividades | P2 |
| FRQ-08 | Geracao de notificacao para Conselho Tutelar (template configuravel) | P2 |
| FRQ-09 | Visualizacao de mapa de frequencia da turma (calendario visual) | P1 |

### 3.8 Modulo: Registro de Aulas

| ID | Requisito | Prioridade |
|---|---|---|
| AUL-01 | Registro diario de conteudo ministrado por aula | P0 |
| AUL-02 | Vinculo opcional com habilidades/objetivos do curriculo | P1 |
| AUL-03 | Observacoes da aula | P1 |
| AUL-04 | Registro de atividades e recursos utilizados | P2 |

### 3.9 Modulo: Avaliacao

| ID | Requisito | Prioridade |
|---|---|---|
| AVA-01 | Motor de regras de avaliacao configuravel por escola/serie/ano letivo | P0 |
| AVA-02 | Modelo descritivo (Ed. Infantil): texto livre por campo de experiencia | P0 |
| AVA-03 | Modelo numerico: escala configuravel (0-10, 0-100, etc) | P0 |
| AVA-04 | Modelo conceitual: escala configuravel (A/B/C/D, MB/B/R/I, etc) | P0 |
| AVA-05 | Instrumentos avaliativos configuraveis (prova, trabalho, participacao, etc) | P0 |
| AVA-06 | Pesos por instrumento configuravel | P1 |
| AVA-07 | Formulas de media configuraveis (aritmetica, ponderada, customizada) | P1 |
| AVA-08 | Upload de midia para portfolio (Ed. Infantil): foto, video, audio | P2 |
| AVA-09 | Relatorio descritivo individual por aluno (Ed. Infantil) | P1 |
| AVA-10 | Recuperacao: paralela, continua, final (configuraveis) | P1 |
| AVA-11 | Regras de substituicao de nota por recuperacao configuraveis | P1 |

### 3.10 Modulo: Conselho de Classe

| ID | Requisito | Prioridade |
|---|---|---|
| CON-01 | Agendamento vinculado ao calendario escolar | P2 |
| CON-02 | Pre-conselho: resumo automatico por aluno (notas, frequencia, alertas) | P2 |
| CON-03 | Registro de ata com deliberacoes por aluno | P2 |
| CON-04 | Decisoes: aprovacao, retencao, progressao parcial, encaminhamentos | P2 |
| CON-05 | Assinatura digital dos participantes | P3 |

### 3.11 Modulo: Fechamento de Periodo

| ID | Requisito | Prioridade |
|---|---|---|
| FEC-01 | Workflow de fechamento: bimestral, semestral, anual | P1 |
| FEC-02 | Validacao de completude (notas e frequencia lancadas para todos os alunos) | P1 |
| FEC-03 | Aprovacao hierarquica: professor -> coordenador -> diretor | P1 |
| FEC-04 | Imutabilidade de dados apos fechamento aprovado | P1 |
| FEC-05 | Fluxo de retificacao pos-fechamento com justificativa obrigatoria | P1 |
| FEC-06 | Resultado final do aluno: aprovado, retido, transferido, abandono | P1 |

### 3.12 Modulo: Comunicacao e Portais

| ID | Requisito | Prioridade |
|---|---|---|
| PRT-01 | Dashboard do professor: turmas, pendencias, alertas, lancamentos rapidos | P1 |
| PRT-02 | Dashboard do gestor: indicadores da escola, fechamentos pendentes | P2 |
| PRT-03 | Portal do responsavel: consulta de notas, frequencia, boletim | P2 |
| PRT-04 | Dashboard da Secretaria: indicadores municipais consolidados | P2 |
| PRT-05 | Comunicados da escola para responsaveis | P3 |
| PRT-06 | Notificacoes (email/push) sobre alertas de frequencia e rendimento | P3 |

### 3.13 Modulo: Documentos e Exportacao

| ID | Requisito | Prioridade |
|---|---|---|
| DOC-01 | Boletim escolar (PDF, layout configuravel por escola) | P2 |
| DOC-02 | Ficha individual do aluno | P2 |
| DOC-03 | Historico escolar | P2 |
| DOC-04 | Ata de resultado final | P2 |
| DOC-05 | Mapa de frequencia | P2 |
| DOC-06 | Exportacao para formato Educacenso/INEP | P3 |
| DOC-07 | Exportacao generica CSV/Excel | P2 |
| DOC-08 | Relatorios gerenciais para Secretaria de Educacao | P3 |

---

## 4. Requisitos Nao-Funcionais

### 4.1 Performance

| ID | Requisito |
|---|---|
| NFR-01 | Tempo de resposta < 2s para operacoes comuns (lancamento de frequencia/nota) |
| NFR-02 | Suportar uso simultaneo de 500+ usuarios (pico: inicio/fim de bimestre) |
| NFR-03 | Dashboard de indicadores com cache inteligente (dados nao precisam ser real-time) |
| NFR-04 | Paginacao e lazy loading em listagens grandes |

### 4.2 Seguranca

| ID | Requisito |
|---|---|
| NFR-05 | HTTPS obrigatorio em producao |
| NFR-06 | RBAC com principio do menor privilegio |
| NFR-07 | Protecao contra OWASP Top 10 (SQL injection, XSS, CSRF, etc) |
| NFR-08 | Tokens JWT com expiracao curta + refresh token |
| NFR-09 | Rate limiting em endpoints publicos |
| NFR-10 | Dados sensiveis criptografados em repouso (laudos, dados de saude) |

### 4.3 Auditoria e Compliance

| ID | Requisito |
|---|---|
| NFR-11 | Audit trail completo: quem, quando, acao, valor anterior, valor novo |
| NFR-12 | Logs imutaveis (append-only) |
| NFR-13 | Retencao de dados conforme legislacao educacional vigente |
| NFR-14 | Conformidade LGPD: consentimento, portabilidade, anonimizacao |
| NFR-15 | Backup automatico diario com retencao minima de 30 dias |

### 4.4 Usabilidade

| ID | Requisito |
|---|---|
| NFR-16 | Interface responsiva (desktop, tablet, mobile) |
| NFR-17 | Lancamento de frequencia em menos de 3 cliques por aluno |
| NFR-18 | Acessibilidade WCAG 2.1 nivel AA |
| NFR-19 | Suporte a navegadores modernos (Chrome, Firefox, Edge, Safari) |
| NFR-20 | Interface em Portugues BR |

### 4.5 Disponibilidade

| ID | Requisito |
|---|---|
| NFR-21 | Disponibilidade 99.5% em horario letivo (7h-22h) |
| NFR-22 | Manutencoes programadas fora do horario letivo |
| NFR-23 | Suporte offline para lancamento de frequencia (PWA com sync) |

---

## 5. Arquitetura de Alto Nivel

### 5.1 Stack Tecnologico

| Camada | Tecnologia |
|---|---|
| Frontend | Vue.js 3 (Composition API) + TypeScript + Vite |
| Backend | PHP 8+ / Laravel 11+ |
| Banco de dados | PostgreSQL 16+ |
| Cache | Redis |
| Arquitetura | Modular Monolith (bounded contexts) |
| Principios | SOLID, Clean Architecture, DDD pragmatico |
| Testes | PHPUnit/Pest (backend) + Vitest (frontend) |
| CI/CD | GitHub Actions |
| Container | Docker / Docker Compose |

### 5.2 Bounded Contexts

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

### 5.3 Camadas por Modulo

```
Presentation  →  Controllers, Requests, Resources (API)
Application   →  UseCases, DTOs, Events, Listeners
Domain        →  Entities, Value Objects, Repositories (interface), Rules Engine
Infrastructure→  Eloquent Repositories, External Services, Queue Jobs
```

### 5.4 Principio do Motor de Regras

Toda regra de negocio educacional (avaliacao, frequencia, calendario) e **configuravel**, NAO hardcoded:

```
┌─────────────────────────┐
│  Parametros do Municipio │  ← Configuracao pela Secretaria
│  (regras de avaliacao,  │
│   limiares de falta,    │
│   periodos, formulas)   │
└──────────┬──────────────┘
           │
    ┌──────▼──────┐
    │ Rules Engine │  ← Interpreta e aplica as regras
    └──────┬──────┘
           │
    ┌──────▼──────┐
    │   Resultado  │  ← Media calculada, alerta gerado, etc.
    └─────────────┘
```

Isso garante que:
- Mudancas na legislacao NAO exigem alteracao de codigo
- Cada escola pode ter parametros diferentes se necessario
- Regras podem ser versionadas por ano letivo

---

## 6. Personas e Jornadas

### 6.1 Professora Maria (Anos Iniciais - 1o ao 5o ano)

**Contexto:** Polivalente, ministra todas as disciplinas para uma turma de 30 alunos.

**Jornada diaria:**
1. Abre o sistema no celular ou computador da escola
2. Seleciona a turma do dia
3. Faz chamada marcando presencas/faltas (interface rapida, poucos toques)
4. Registra brevemente o conteudo da aula
5. Ao final do bimestre, lanca notas por instrumento avaliativo
6. Visualiza dashboard com alunos em risco (faltas, baixo rendimento)

**Dor principal:** Pouco tempo, precisa ser rapido e intuitivo.

### 6.2 Professor Joao (Anos Finais - 6o ao 9o ano)

**Contexto:** Especialista em Matematica, da aula em 5 turmas diferentes.

**Jornada diaria:**
1. Seleciona a turma da aula atual
2. Faz chamada
3. Registra conteudo e atividade aplicada
4. Lanca notas de provas/trabalhos quando aplicavel
5. Verifica alunos que precisam de recuperacao paralela

**Dor principal:** Muitas turmas, precisa trocar rapido entre elas.

### 6.3 Educadora Ana (Educacao Infantil - Pre-escola)

**Contexto:** Trabalha com criancas de 4-5 anos, avaliacao puramente descritiva.

**Jornada:**
1. Registra frequencia
2. Ao longo da semana, escreve observacoes de desenvolvimento por campo de experiencia
3. Semestralmente, elabora relatorio descritivo individual
4. Pode anexar fotos de atividades e producoes dos alunos

**Dor principal:** Interface precisa suportar texto longo e uploads, sem pressao de notas.

### 6.4 Diretora Carla

**Contexto:** Gerencia uma EMEB com 500 alunos, 20 professores, 15 turmas.

**Jornada:**
1. Verifica dashboard: alertas de evasao, fechamentos pendentes, frequencia geral
2. Aprova fechamentos bimestrais dos professores
3. Acompanha indicadores da escola
4. Prepara dados para reunioes com a Secretaria

**Dor principal:** Precisa de visao consolidada sem ter que consultar turma por turma.

### 6.5 Secretario Municipal de Educacao

**Contexto:** Responsavel pelas politicas educacionais do municipio inteiro.

**Jornada:**
1. Consulta indicadores consolidados das 30 escolas
2. Identifica escolas com maiores taxas de evasao/retencao
3. Configura parametros de avaliacao para o ano letivo
4. Gera relatorios para prestacao de contas
5. Exporta dados para Educacenso

**Dor principal:** Dados dispersos e desatualizados; precisa de uma fonte unica da verdade.

---

## 7. Metricas de Sucesso

| Metrica | Meta | Prazo |
|---|---|---|
| Adocao pelos professores | 90% dos professores usando ativamente | 6 meses apos deploy |
| Lancamento de frequencia | 95% das aulas com frequencia registrada no dia | 3 meses apos deploy |
| Tempo medio de lancamento de frequencia | < 3 minutos por turma | Desde o lancamento |
| Fechamento bimestral no prazo | 100% das turmas fechadas ate a data limite | Desde o 1o bimestre |
| Reducao de inconsistencias | 80% menos erros vs. processo manual | Apos 1 ano letivo |
| Satisfacao do professor (NPS) | > 40 | 6 meses apos deploy |
| Acessos do portal do responsavel | 50% dos responsaveis cadastrados | 1 ano apos deploy |

---

## 8. Riscos e Mitigacoes

| Risco | Impacto | Probabilidade | Mitigacao |
|---|---|---|---|
| Resistencia dos professores a adocao | Alto | Media | Treinamento presencial, interface ultra-simples, suporte dedicado |
| Internet instavel nas escolas | Alto | Alta | PWA com modo offline, sincronizacao resiliente |
| Mudanca de regras educacionais | Medio | Alta | Motor de regras configuravel, sem hardcode de regras |
| Volume de dados no fechamento | Medio | Media | Cache, filas assincronas, processamento em background |
| Vazamento de dados de menores | Critico | Baixa | RBAC rigoroso, criptografia, auditoria, LGPD compliance |
| Rotatividade de professores | Medio | Alta | Onboarding simplificado, documentacao in-app |

---

## 9. Fora de Escopo (V1)

- Ensino Medio (responsabilidade estadual)
- Gestao financeira da escola
- Gestao de merenda escolar
- Gestao de transporte escolar
- Gestao de patrimonio
- Integracao com folha de pagamento
- Sistema de biblioteca
- App nativo (sera PWA na V1)

---

## 10. Cronograma Macro

| Fase | Escopo | Duracao Estimada |
|---|---|---|
| Fase 0 | Fundacao (setup, infra, CI) | 2 sprints |
| Fase 1 | Nucleo (IAM, Escola, Pessoas, Matricula) | 4 sprints |
| Fase 2 | Diario (Curriculo, Calendario, Frequencia, Aulas) | 4 sprints |
| Fase 3 | Avaliacao (Motor de regras, Recuperacao, Conselho, Fechamento) | 4 sprints |
| Fase 4 | Portais (Professor, Responsavel, Gestor, Secretaria) | 3 sprints |
| Fase 5 | Integracao e Exportacao (Educacenso, Documentos) | 2 sprints |
| Fase 6 | Mobile/Offline (PWA) | 3 sprints |

**MVP (Fases 0-2):** Sistema funcional com frequencia e registro de aulas.
**V1 Completa (Fases 0-5):** Sistema completo para um ano letivo inteiro.

---

## 11. Premissas e Dependencias

### Premissas
- A Secretaria de Educacao de Jandira fornecera o regimento escolar municipal vigente
- Informacoes sobre regras de avaliacao, medias e conceitos serao validadas com o CME (Conselho Municipal de Educacao)
- Sera disponibilizado acesso a pelo menos uma escola para testes com usuarios reais
- Infraestrutura de hospedagem sera provida (cloud ou on-premise)

### Dependencias
- Definicao oficial dos parametros de avaliacao pelo municipio (escalas, medias, formulas)
- Levantamento das escolas que tem internet confiavel (para priorizar funcionalidade offline)
- Validacao do formato de exportacao Educacenso vigente com a DE Itapevi
- Definicao do modelo de boletim escolar utilizado atualmente

---

## 12. Glossario

| Termo | Definicao |
|---|---|
| EMEB | Escola Municipal de Educacao Basica |
| SMEJ | Secretaria Municipal de Educacao de Jandira |
| CME | Conselho Municipal de Educacao |
| DE | Diretoria de Ensino (orgao regional da SEDUC-SP) |
| Enturmacao | Ato de vincular um aluno matriculado a uma turma especifica |
| Componente Curricular | Disciplina ou area de conhecimento (ex: Matematica, Lingua Portuguesa) |
| Campo de Experiencia | Organizacao curricular da Ed. Infantil (substituem disciplinas) |
| Educacenso | Censo escolar nacional realizado pelo INEP/MEC |
| RBAC | Role-Based Access Control (controle de acesso por perfil) |
| PWA | Progressive Web App (aplicacao web com recursos offline) |
| Motor de Regras | Componente que interpreta regras configuraveis sem necessidade de alterar codigo |
| Ano Letivo | Periodo anual de atividades escolares (tipicamente fevereiro a dezembro) |
| Periodo Avaliativo | Subdivisao do ano letivo para fins de avaliacao (bimestre, trimestre, semestre) |
| Fechamento | Processo formal de consolidacao de notas e frequencia de um periodo |

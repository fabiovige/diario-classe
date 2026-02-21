# Documentacao Escolar

## Conceito

A documentacao escolar e o conjunto de documentos exigidos para formalizar a matricula
e manter o prontuario do aluno atualizado. O sistema controla a entrega de cada documento
por aluno, permitindo matricula condicional quando documentos estao pendentes.

## Documentos Controlados pelo Sistema

| Documento | Codigo | Obrigatorio | Observacao |
|-----------|--------|-------------|------------|
| Certidao de Nascimento | `birth_certificate` | Sim | Documento principal de identificacao |
| RG / Documento de Identidade | `id_card` | Nao | Quando disponivel |
| Comprovante de Endereco | `proof_of_address` | Sim | Para georreferenciamento e alocacao |
| Historico Escolar | `school_transcript` | Condicional | Obrigatorio para transferencia recebida |
| Declaracao de Transferencia | `transfer_declaration` | Condicional | Obrigatorio para transferencia recebida |
| Carteira de Vacinacao | `vaccination_card` | Sim | Obrigatorio para Ed. Infantil e 1o ano EF |
| Foto 3x4 | `photo_3x4` | Nao | Para prontuario e carteirinha |
| Cartao SUS | `sus_card` | Nao | Recomendado |
| Numero NIS | `nis_number` | Nao | Para programas sociais (Bolsa Familia, etc) |
| Laudo Medico | `medical_report` | Condicional | Obrigatorio para alunos com deficiencia/necessidades especiais |

## Obrigatoriedade por Tipo de Matricula

### Matricula Nova (new_enrollment)
- Certidao de Nascimento (obrigatorio)
- Comprovante de Endereco (obrigatorio)
- Carteira de Vacinacao (obrigatorio para Ed. Infantil e 1o ano)
- Demais: recomendados, nao bloqueantes

### Rematricula (re_enrollment)
- Documentos ja constam no prontuario do ano anterior
- Solicitar atualizacao de comprovante de endereco
- Verificar se vacinacao esta em dia

### Transferencia Recebida (transfer_received)
- Certidao de Nascimento (obrigatorio)
- Historico Escolar (obrigatorio — prazo de 30 dias para entrega)
- Declaracao de Transferencia (obrigatorio)
- Comprovante de Endereco (obrigatorio)

## Matricula Condicional

A escola NAO pode recusar matricula por falta de documentacao (ECA Art. 53 + Instrucao Normativa SME).
O aluno e matriculado condicionalmente e a escola deve:

1. Registrar quais documentos estao pendentes
2. Notificar o responsavel sobre o prazo de entrega (geralmente 30-60 dias)
3. Acompanhar a entrega e atualizar o sistema
4. Se persistir a pendencia, acionar assistencia social

## Controle no Sistema

Cada documento tem os seguintes campos:

| Campo | Tipo | Descricao |
|-------|------|-----------|
| enrollment_id | FK | Matricula vinculada |
| document_type | enum | Tipo do documento |
| delivered | boolean | Se foi entregue |
| delivered_at | date | Data de entrega |
| notes | text | Observacoes (ex: "copia, original a apresentar") |

O toggle de entrega e feito pelo secretario via interface de checklist na tela de detalhes da matricula.

## Documentos Gerados pelo Sistema

Alem dos documentos recebidos, o sistema gera documentos oficiais:

| Documento | Quando | Quem gera |
|-----------|--------|-----------|
| Boletim Escolar | Final de cada bimestre | Automatico (notas + frequencia) |
| Ficha Individual do Aluno | Final do ano | Automatico (consolidado anual) |
| Historico Escolar | Transferencia ou conclusao | Secretaria |
| Declaracao de Matricula | Sob demanda | Secretaria |
| Declaracao de Transferencia | Transferencia | Secretaria |
| Ata de Conselho de Classe | Final de cada bimestre | Coordenador |
| Ata de Resultado Final | Final do ano | Diretor |

## LGPD e Documentacao

- Documentos com dados sensiveis (laudo medico, NIS) exigem tratamento especial
- Acesso restrito por RBAC (apenas secretaria e direcao)
- Retencao conforme politica municipal (minimo 5 anos apos conclusao/transferencia)
- Responsavel pode solicitar acesso aos dados do aluno (portabilidade)

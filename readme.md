# soat-upload-service

Microsserviço responsável por receber arquivos de diagrama (JPG, JPEG, PNG ou PDF), armazená-los no MinIO e publicar uma mensagem no RabbitMQ para iniciar o fluxo de análise.

## Alunos

| Aluno | RM | Discord | LinkedIn |
|---|---|---|---|
| Felipe | 365154 | felipeoli7eira | [@felipeoli7eira](https://www.linkedin.com/in/felipeoli7eira) |
| Nicolas | 365746 | nic_hcm | [@Nicolas Martins](https://www.linkedin.com/in/nicolas-hcm) |
| William | 365973 | wllsistemas | [@William Francisco Leite](https://www.linkedin.com/in/williamfranciscoleite) |

---

## Descrição do Problema

O sistema tem como objetivo automatizar a análise de diagramas de arquitetura de software por meio de Inteligência Artificial. Equipes de engenharia submetem diagramas (JPG, JPEG, PNG ou PDF) e recebem, de forma assíncrona, um relatório com **componentes identificados**, **riscos** e **recomendações** de melhoria — eliminando a necessidade de revisão manual.

---

## Arquitetura Proposta

O sistema é composto por cinco microsserviços interligados:

| Serviço | Papel |
|---|---|
| **BFF** | Ponto de entrada unificado; orquestra chamadas aos serviços internos |
| **upload-service** *(este serviço)* | Recebe diagramas, armazena no Amazon S3, publica na fila `protocols` |
| **trigger-service** | Consome a fila, aciona a IA e persiste resultados no PostgreSQL |
| **report-service** | Consulta resultados e gera relatório em PDF |
| **RabbitMQ** | Broker de mensagens para comunicação assíncrona |

> Diagrama interativo: [FIAP - HACKATON FASE 5](https://www.tldraw.com/f/CPYtIC_xwtcfbSCH4vgkT?d=v567.4.3513.1667.page)

### Arquitetura Interna

O projeto segue **Clean Architecture** com separação clara entre as camadas:

```
app/
├── Domain/          # Entidades, interfaces e exceções de domínio
├── Application/     # Use Cases, Controllers e Input DTOs
├── Infrastructure/  # Implementações concretas (MinIO, PostgreSQL, RabbitMQ, HTTP)
└── Http/            # Middleware
```

---

## Fluxo da Solução

**Envio para análise:**
1. Usuário envia diagrama via `POST /api/upload` no BFF
2. BFF repassa ao **upload-service**, que armazena no Amazon S3
3. **upload-service** publica `{ protocol_uuid, file_url, ... }` na fila `protocols` do RabbitMQ
4. **trigger-service** consome a mensagem e aciona o **Analysis Service (IA)**
5. IA processa e publica o resultado na fila `analysis_response`
6. **trigger-service** persiste o resultado no PostgreSQL (`SUCESSO` ou `ERRO`)

**Consulta do resultado:**
1. Usuário consulta status via `GET /api/status/{uuid}` no BFF
2. Usuário solicita relatório via `GET /api/report/{uuid}` no BFF
3. **report-service** busca os dados no **trigger-service** e gera o PDF

---

## Segurança

### Requisitos básicos adotados

- **Credenciais via variáveis de ambiente**: senhas e chaves de acesso são injetadas via `.env`, nunca hardcoded no código
- **Rede Docker isolada** (`soat-net`): apenas o BFF expõe porta pública; os demais serviços são acessíveis somente internamente

### Validação e tratamento de entradas não confiáveis

- Tipo MIME e extensão do arquivo validados na camada de Application (aceita apenas `image/jpeg`, `image/png`, `application/pdf`)
- Tamanho máximo de **2 MB** por arquivo, prevenindo abuso de recursos
- Arquivos **renomeados com UUID** antes do armazenamento — elimina path traversal e conflitos de nome
- Arquivo armazenado como blob no MinIO/S3 e **nunca executado** pelo servidor

### Uso controlado do modelo de IA

- Este serviço não se comunica diretamente com a IA — publica apenas um evento estruturado na fila `protocols` com `protocol_uuid`, `file_url` e metadados do arquivo
- Nenhum dado pessoal do usuário é incluído no payload publicado
- O escopo do que a IA recebe é controlado pelo **trigger-service**

### Tratamento de falhas e comportamentos inesperados da IA

- Falhas no processamento pela IA não afetam este serviço — a comunicação é assíncrona via RabbitMQ
- A **Dead Letter Queue (DLQ)** captura mensagens que falharam no processamento downstream, evitando perda de eventos

### Comunicação entre serviços

- **Rede Docker interna** (`soat-net`): este serviço não é acessível externamente
- **Mensagens persistentes** no RabbitMQ (`delivery_mode: persistent`): eventos não são perdidos em reinicializações do broker
- Comunicação assíncrona isola falhas — indisponibilidade do trigger-service não derruba o upload

### Riscos e limitações conhecidos

| Risco | Impacto | Mitigação atual |
|---|---|---|
| Conteúdo interno do arquivo não é inspecionado (vírus scan) | Arquivo corrompido ou malformado pode ser armazenado | Arquivo nunca é executado; armazenado como blob no S3/MinIO |
| Volume elevado de uploads simultâneos | Aumento de carga nos serviços downstream | Limite de tamanho (2 MB) e validação de tipo MIME por requisição |

---

## Pré-requisitos

- Docker e Docker Compose

---

## Rodando o projeto

```bash
docker compose up -d
```

A aplicação fica disponível em `http://localhost:8001` (ou conforme configurado no `docker-compose.yaml`).

---

## API

| Método | Rota          | Descrição                        |
|--------|---------------|----------------------------------|
| GET    | `/api/ping`   | Health check                     |
| POST   | `/api/upload` | Recebe e armazena um arquivo     |

### GET `/api/ping`

```json
{ "err": false, "msg": "pong" }
```

### POST `/api/upload`

**Body:** `multipart/form-data`

| Campo     | Tipo   | Obrigatório | Descrição                            |
|-----------|--------|-------------|--------------------------------------|
| `diagram` | arquivo | Sim        | JPG, JPEG, PNG ou PDF (máx. 2 MB)   |

**Sucesso (200):**
```json
{
  "err": false,
  "msg": "Arquivo recebido com sucesso",
  "data": {
    "protocol": "uuid",
    "file_original_name": "diagrama.jpg",
    "file_unique_name": "uuid-diagrama.jpg",
    "file_mime_type": "image/jpeg",
    "file_size": 12345,
    "storage_endpoint": "http://minio/bucket/uuid-diagrama.jpg"
  }
}
```

**Erro (400):** arquivo ausente, tipo inválido ou tamanho excedido.

---

## Testes

### Pré-requisitos

- Docker e Docker Compose
- Container `soat-upload` em execução (`docker compose up -d`)

### 1. Instalar dependências (incluindo dev)

```bash
docker exec soat-upload composer install
```

> Necessário apenas na primeira vez ou após alterações no `composer.json`.

### 2. Executar os testes

```bash
docker exec soat-upload vendor/bin/phpunit
```

### 3. Executar com relatório de cobertura HTML

```bash
docker exec soat-upload vendor/bin/phpunit --coverage-html var/coverage/html
```

O relatório estará disponível em `application/var/coverage/html/index.html`.

### Estrutura dos testes

| Suite   | Local                              | O que testa                                         |
|---------|------------------------------------|-----------------------------------------------------|
| Unit    | `tests/Unit/Domain/`               | Entidades de domínio (`File`, `Protocol`)           |
| Unit    | `tests/Unit/`                      | Infraestrutura isolada (`MinioFileStorage`)         |
| Feature | `tests/Feature/`                   | Endpoints HTTP e repositório com banco de dados     |

---

## Variáveis de ambiente relevantes

| Variável           | Descrição                          |
|--------------------|------------------------------------|
| `DB_HOST`          | Host do PostgreSQL                 |
| `DB_DATABASE`      | Nome do banco de dados             |
| `DB_USERNAME`      | Usuário do banco                   |
| `DB_PASSWORD`      | Senha do banco                     |
| `MINIO_ENDPOINT`   | URL do MinIO                       |
| `MINIO_KEY`        | Access key do MinIO                |
| `MINIO_SECRET`     | Secret key do MinIO                |
| `RABBITMQ_HOST`    | Host do RabbitMQ                   |

## Equipe

### Integrantes IADT

| Nome | RM |
|---|---|
| Angelo Rossi | RM365902 |
| Carlos Eduardo | RM365213 |
| Felipe Goiabeira | RM365753 |
| Guilherme Groff | RM365281 |
| Rafael Lua | RM366254 |

### Integrantes SOAT

| Nome | RM |
|---|---|
| Felipe Alves de Oliveira | RM365154 |
| Nicolas Henrique Correa Martins | RM365746 |
| William Francisco Leite | RM365973 |

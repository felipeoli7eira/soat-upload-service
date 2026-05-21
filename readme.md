# soat-upload-service

Microsserviço responsável por receber arquivos de diagrama (JPG, JPEG, PNG ou PDF), armazená-los no MinIO e publicar uma mensagem no RabbitMQ para iniciar o fluxo de análise.

---

## Arquitetura

O projeto segue **Clean Architecture** com separação clara entre as camadas:

```
app/
├── Domain/          # Entidades, interfaces e exceções de domínio
├── Application/     # Use Cases, Controllers e Input DTOs
├── Infrastructure/  # Implementações concretas (MinIO, PostgreSQL, RabbitMQ, HTTP)
└── Http/            # Middleware
```

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

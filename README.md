# AutoRecall API (Laravel)

API Laravel 13 com MySQL no Docker. Replica o contrato REST do NestJS em `../api-nest`.

## Subir o banco

```bash
cd api
docker compose up -d
```

MySQL em `localhost:3309`. As tabelas já existentes (TypeORM) são reaproveitadas.

## Migration Laravel

Só cria `personal_access_tokens` (Sanctum). A tabela de controle do Laravel é `laravel_migrations`, para não colidir com a `migrations` do TypeORM.

```bash
php artisan migrate
php artisan db:seed
```

Login: `rafael@autocenter.com.br` / `123456`

## Rodar a API

```bash
php artisan serve --port=3000
```

O front em `http://localhost:5173` encaminha `/api` para essa porta.

Rotas (Bearer, exceto login e hello):

- `POST /auth/login`, `GET /auth/me`
- `GET|POST /customers` e `PUT /customers/:id`
- `GET|POST /vehicles` e `PUT /vehicles/:id`
- `GET|POST /services` e `PUT /services/:id`
- `GET|POST /orders` e `POST /orders/:id/finish`
- `GET /maintenances`
- `GET|POST /contacts`
- `GET /notifications`, `POST /notifications/:id/read`, `POST /notifications/read-all`
- `GET|PUT /settings`

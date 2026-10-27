# Prueba Backend - API de productos

API REST en Laravel para gestionar productos, divisas y precios de productos en distintas divisas. Todas las respuestas son JSON y el acceso está protegido con tokens de Sanctum.

## Stack

- PHP 8.4 / Laravel 13
- SQLite (configurable con cualquier motor soportado por Laravel)
- Laravel Sanctum para autenticación por token
- Pest para tests
- Scramble para la documentación OpenAPI

## Instalación

```bash
git clone <url-del-repo>
cd prueba-backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite      # en Windows: New-Item database/database.sqlite
php artisan migrate --seed
php artisan serve
```

La API queda en `http://localhost:8000/api`.

El seeder crea 5 divisas (US Dollar, Euro, Colombian Peso, Japanese Yen, Canadian Dollar) y 10 productos de ejemplo. Los ids de las divisas son: USD `1`, EUR `2`, COP `3`, JPY `4`, CAD `5`.

## Documentación

- **Swagger / OpenAPI:** con el servidor corriendo, abrir `http://localhost:8000/docs/api`. Por defecto Scramble solo la expone en entorno `local`.
- **OpenAPI exportado:** [docs/openapi.json](docs/openapi.json), para verla sin levantar la app (se puede abrir en https://editor.swagger.io).
- **Colección de Insomnia:** [docs/insomnia.json](docs/insomnia.json). Se importa desde *Import > From File*. Al ejecutar *Login* o *Register* el token se guarda solo en el environment, y *Create product* / *Create currency* guardan el id creado para usarlo en las demás peticiones.

Para regenerar el OpenAPI después de cambiar la API:

```bash
php artisan scramble:export --path=docs/openapi.json
```

## Autenticación

1. `POST /api/auth/register` o `POST /api/auth/login` devuelven un `token`.
2. Enviar el token en cada petición: `Authorization: Bearer <token>`.
3. Enviar también `Accept: application/json`.

`login` y `register` tienen un límite de 6 intentos por minuto (responde `429` al superarlo). Si las credenciales son incorrectas la respuesta es `401` con el mismo mensaje tanto si el email no existe como si la contraseña está mal.

- Los tokens expiran a las 24 horas (configurable con `SANCTUM_EXPIRATION` en minutos); después responde `401` y hay que volver a hacer login.
- Las rutas protegidas tienen un límite de 60 peticiones por minuto (responde `429`).

## Endpoints

Todas las rutas llevan el prefijo `/api`.

| Método | Ruta | Descripción | Respuestas |
|---|---|---|---|
| POST | `/auth/register` | Registrar usuario | 201, 422 |
| POST | `/auth/login` | Iniciar sesión | 200, 401, 422, 429 |
| POST | `/auth/logout` | Revocar el token actual | 200, 401 |
| GET | `/products` | Listar productos (paginado, 15 por página, `?page=`) | 200 |
| POST | `/products` | Crear producto | 201, 422 |
| GET | `/products/{id}` | Ver producto con su divisa y sus precios | 200, 404 |
| PUT/PATCH | `/products/{id}` | Actualizar producto (solo los campos enviados) | 200, 404, 422 |
| DELETE | `/products/{id}` | Eliminar producto (y sus precios) | 204, 404 |
| GET | `/products/{id}/prices` | Listar precios del producto | 200, 404 |
| POST | `/products/{id}/prices` | Agregar precio en otra divisa | 201, 404, 422 |
| GET | `/currencies` | Listar divisas | 200 |
| POST | `/currencies` | Crear divisa | 201, 422 |
| GET | `/currencies/{id}` | Ver divisa | 200, 404 |
| PUT/PATCH | `/currencies/{id}` | Actualizar divisa | 200, 404, 422 |
| DELETE | `/currencies/{id}` | Eliminar divisa | 204, 404, 409 |

Todas excepto `register` y `login` requieren token (`401` sin él).

### Ejemplo: crear producto

```http
POST /api/products
Authorization: Bearer <token>
Content-Type: application/json
Accept: application/json

{
  "name": "Laptop",
  "description": "Gaming laptop 16GB RAM",
  "price": 1500,
  "tax_cost": 150,
  "manufacturing_cost": 900,
  "currency_id": 1
}
```

```json
{
  "data": {
    "id": 11,
    "name": "Laptop",
    "description": "Gaming laptop 16GB RAM",
    "price": "1500.000",
    "tax_cost": "150.000",
    "manufacturing_cost": "900.000",
    "currency": { "id": 1, "name": "US Dollar", "symbol": "$", "exchange_rate": "1.000" },
    "created_at": "2026-10-27T04:15:20.000000Z",
    "updated_at": "2026-10-27T04:15:20.000000Z"
  }
}
```

## Precios en otras divisas

`POST /api/products/{id}/prices` recibe `currency_id` y opcionalmente `price`.

- Si se envía `price`, se guarda tal cual.
- Si no se envía, se calcula con las tasas de cambio. Las tasas están expresadas respecto al dólar (USD = 1), así que primero se pasa el precio a USD y luego a la divisa destino:

```
precio = precio_producto / tasa_divisa_producto * tasa_divisa_destino
```

Ejemplo: un producto de 100 EUR (tasa 0.85) en COP (tasa 3800) da `100 / 0.85 * 3800 = 447058.82`.

Un producto solo puede tener un precio por divisa; repetir la divisa responde `422`.

## Errores

- `401` sin token o token inválido.
- `404` con un mensaje genérico (`{"message": "Resource not found."}`), sin exponer nombres internos de modelos.
- `409` al intentar borrar una divisa que todavía usa algún producto o precio.
- `422` con el formato estándar de Laravel: `message` + `errors` por campo.

## Estructura

```
app/
  Http/Controllers/Api/   controllers (Auth, Product, ProductPrice, Currency)
  Http/Requests/          validación con Form Requests
  Http/Resources/         formato de salida JSON (API Resources)
  Repositories/
    Contracts/            interfaces de los repositorios
    Eloquent/             implementaciones con Eloquent (BaseRepository + uno por modelo)
  Services/PriceConverter.php   conversión de precios entre divisas
  Providers/RepositoryServiceProvider.php   bind interfaz -> implementación
```

Algunas decisiones:

- **Repository pattern:** los controllers dependen de interfaces y no llaman a Eloquent directamente. `BaseRepository` tiene el CRUD común y cada repositorio solo agrega sus consultas propias.
- **Form Requests** para toda la validación y **API Resources** para controlar exactamente qué campos salen en el JSON (por ejemplo, el password nunca se expone).
- **Service** para la conversión de precios, así se puede probar con un test unitario sin base de datos.
- Montos guardados como `decimal`, nunca `float`.
- Claves foráneas con `restrictOnDelete` en divisas y `cascadeOnDelete` en los precios de un producto.

## Tests

```bash
php artisan test
```

Hay tests de feature para autenticación, productos, precios y divisas (casos correctos, validaciones, 401, 404, 409, throttle) y un test unitario para la conversión de precios. Usan SQLite en memoria, no tocan la base de datos local.

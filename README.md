# Sistema de Gestión Académica

Aplicación web progresiva para administrar admisiones, matrículas, asignación de cursos, pensiones y seguimiento
académico en centros de educación infantil.

El sistema ofrece portales separados para administración, docentes y representantes. Está construido con Laravel 13, Vue
3, TypeScript, Tailwind CSS 4 y Laravel Sanctum.

## Requisitos locales

- PHP 8.5 y Composer 2.
- Node.js 22.
- pnpm 11 mediante Corepack.
- SQLite, utilizado por la configuración de desarrollo incluida, o una base de datos configurada en `.env`.

## Instalación local

1. Instalar las dependencias, crear `.env`, generar la clave de la aplicación, ejecutar las migraciones y compilar el
   frontend:

   ```bash
   corepack enable pnpm
   composer setup
   ```

2. Cargar el escenario de demostración. Este seeder puede ejecutarse nuevamente sin acumular los registros de
   cumplimiento:

   ```bash
   php artisan db:seed --class='Database\Seeders\ComplianceEnrollmentSeeder'
   ```

3. Iniciar el entorno de desarrollo:

   ```bash
   composer run dev
   ```

4. Abrir `http://localhost:8000/login` e iniciar sesión con una de las cuentas de prueba.

## Credenciales de prueba

Todas las cuentas creadas por `ComplianceEnrollmentSeeder` usan la contraseña `password`.

| Rol                    | Correo                                        | Contraseña | Escenario principal                                                                         |
|------------------------|-----------------------------------------------|------------|---------------------------------------------------------------------------------------------|
| Administrador          | `compliance.admin@cenestur.test`              | `password` | Gestión y auditoría del escenario completo                                                  |
| Docente principal      | `compliance.teacher@cenestur.test`            | `password` | Docente principal de Maternal 1                                                             |
| Docente principal      | `compliance.teacher.maternal-2@cenestur.test` | `password` | Curso de Maternal 2 con estudiantes activos                                                 |
| Docente sin asignación | `compliance.unassigned@cenestur.test`         | `password` | Estado vacío y restricciones por falta de curso                                             |
| Representante          | `compliance.parent@cenestur.test`             | `password` | Familia Rivera: dos hijos, matrícula en borrador y matrícula pagada pendiente de asignación |
| Representante          | `compliance.single@cenestur.test`             | `password` | Familia Torres: un hijo con pago en proceso                                                 |
| Representante          | `compliance.debtor@cenestur.test`             | `password` | Familia Mora: deuda histórica y matrícula pendiente de pago                                 |
| Representante          | `compliance.pending@cenestur.test`            | `password` | Familia Vega: estudiante activo asignado a Maternal 2                                       |

El seeder también crea estas cuentas adicionales, todas con la misma contraseña:

| Tipo                   | Correos disponibles                                                                                                                         |
|------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| Docentes principales   | `compliance.teacher.inicial-1@cenestur.test`, `compliance.teacher.inicial-2@cenestur.test` y `compliance.teacher.primero-egb@cenestur.test` |
| Docentes auxiliares    | `compliance.aux.maternal-1.1@cenestur.test` hasta `compliance.aux.maternal-1.4@cenestur.test`, y el mismo patrón para `maternal-2`          |
| Familias de sobrecarga | `compliance.family01@cenestur.test` hasta `compliance.family13@cenestur.test`                                                               |

> Estas credenciales contienen datos ficticios y están destinadas únicamente a demostración y pruebas. El workflow de
> producción no ejecuta este seeder automáticamente.

## Comprobaciones

Para ejecutar las mismas comprobaciones principales utilizadas por integración continua:

```bash
composer ci:check
pnpm run test:e2e
```

`composer ci:check` valida el formato de PHP, el análisis estático, el frontend, los tipos de TypeScript y las pruebas
de Laravel.

## Autodespliegue

El autodespliegue está definido en [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) y depende del
workflow [`.github/workflows/tests.yml`](.github/workflows/tests.yml).

### Activación

- Cada `push` a `main` inicia el workflow `tests`.
- El despliegue comienza únicamente si todas las comprobaciones y pruebas end-to-end terminan correctamente.
- Las ejecuciones originadas por un `pull_request` no despliegan.
- También se puede iniciar manualmente desde **GitHub Actions > deploy > Run workflow**, siempre sobre la rama `main`.
- El grupo de concurrencia `production-deployment` evita que dos despliegues de producción se ejecuten simultáneamente.

### Secretos de GitHub

Los siguientes secretos deben configurarse en el entorno `production` del repositorio:

| Secreto              | Requerido | Descripción                                                                |
|----------------------|-----------|----------------------------------------------------------------------------|
| `DEPLOY_HOST`        | Sí        | Dominio o dirección IP del servidor                                        |
| `DEPLOY_PORT`        | Sí        | Puerto SSH, por ejemplo `22`                                               |
| `DEPLOY_USER`        | Sí        | Usuario SSH con acceso al directorio de la aplicación                      |
| `DEPLOY_PATH`        | Sí        | Ruta absoluta y escribible de la aplicación en el servidor                 |
| `DEPLOY_SSH_KEY`     | Sí        | Clave SSH privada autorizada para el usuario de despliegue                 |
| `DEPLOY_KNOWN_HOSTS` | Sí        | Entrada verificada de `known_hosts` para validar la identidad del servidor |
| `DEPLOY_URL`         | No        | URL pública usada para comprobar `GET /up` al finalizar                    |

No se debe guardar la clave privada, el archivo `.env` de producción ni ningún otro secreto dentro del repositorio.

### Preparación del servidor

Antes del primer despliegue, el servidor debe cumplir estas condiciones:

- `DEPLOY_PATH` existe y el usuario SSH puede escribir en él.
- `DEPLOY_PATH/.env` existe y contiene la configuración de producción.
- `DEPLOY_PATH/composer.phar` está disponible para instalar dependencias.
- Los comandos `php8.5`, `composer` y `rsync` están instalados.
- El servidor web apunta a `DEPLOY_PATH/public`.
- Los procesos programados y de cola están configurados en el servidor según las necesidades de la aplicación.

### Secuencia del despliegue

1. GitHub Actions descarga exactamente la revisión que aprobó el workflow de pruebas.
2. Instala las dependencias de Node y compila los recursos de producción con `pnpm run build`.
3. Configura una conexión SSH con verificación estricta de la identidad del servidor.
4. Comprueba el directorio, el `.env` y los comandos requeridos en el servidor.
5. Activa el modo mantenimiento con `php8.5 artisan down --retry=60`.
6. Sincroniza el código y los recursos compilados mediante `rsync`. Conserva el `.env`, `storage`, `public/storage`,
   `vendor` y otros archivos propios del servidor.
7. Instala las dependencias PHP de producción, ejecuta `php8.5 artisan migrate --force`, optimiza Laravel y recarga los
   procesos de la aplicación.
8. Desactiva siempre el modo mantenimiento, incluso si un paso anterior falla.
9. Si `DEPLOY_URL` está configurado, solicita `${DEPLOY_URL}/up` y marca el despliegue como fallido si la aplicación no
   responde correctamente.
10. Elimina la clave SSH temporal del runner.

Las migraciones sí forman parte del autodespliegue. Los seeders no se ejecutan automáticamente para evitar introducir
cuentas y datos ficticios en producción.

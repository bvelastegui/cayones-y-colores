<x-mail::message>
# Hola, {{ $name }}

Se ha creado tu cuenta de {{ $roleLabel }} en **Crayones y Colores**.

La cuenta está asociada al correo **{{ $email }}**.

<x-mail::button :url="$setupUrl">
Crear mi contraseña
</x-mail::button>

Este enlace vence en 60 minutos. Si ya venció, comunícate con la institución.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>

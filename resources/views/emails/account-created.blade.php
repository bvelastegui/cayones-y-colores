<x-mail::message>
# Hola, {{ $name }}

Se ha creado tu cuenta de {{ $roleLabel }} en **Crayones y Colores**.

Puedes iniciar sesión con las siguientes credenciales:

- **Correo:** {{ $email }}
- **Contraseña:** {{ $password }}

<x-mail::button :url="url('/login')">
Iniciar sesión
</x-mail::button>

Te recomendamos cambiar tu contraseña después del primer ingreso.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>

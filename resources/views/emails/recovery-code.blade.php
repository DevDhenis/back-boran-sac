<x-mail::message>
# Recuperación de Contraseña

¡Hola **{{ $person->first_name }}**!

Hemos recibido una solicitud para restablecer la contraseña de tu cuenta. 
Por favor, utiliza el siguiente código para completar el proceso:

<x-mail::panel>
🔑 <span style="font-size: 20px; color: black;">{{ $code }}</span>
</x-mail::panel>

**Importante:** Si no solicitaste este cambio, puedes ignorar este correo. Por seguridad, tu contraseña no será modificada sin este código.

Atentamente,
🚀 **Equipo FERREMAX S.A.C**
</x-mail::message>
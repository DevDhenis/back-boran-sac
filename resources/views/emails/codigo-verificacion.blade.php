<x-mail::message>
# Verifica tu dirección de correo electrónico

¡Hola **{{ $persona->nombres }}** 🌟

📬 Acabamos de enviarte un código de verificación para asegurarnos de que esta dirección de correo electrónico realmente te pertenece.  
Por favor, ingresa el siguiente código en nuestra página de verificación:

<x-mail::panel>
🔑 <span style="font-size: 20px; color: black;">{{ $codigo }}</span>
</x-mail::panel>

Estamos emocionados de tenerte en **BORAN S.A.C**.  
¡Gracias por ser parte de nuestra comunidad!

Atentamente,  
🚀 **Equipo BORAN S.A.C**
</x-mail::message>

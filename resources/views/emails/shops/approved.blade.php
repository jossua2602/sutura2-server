<x-mail::message>
# Welcome to Sutura, {{ $registration->first_name }}!

We are thrilled to inform you that your shop registration for **{{ $registration->shop_name }}** has been officially approved.

You can now log in to the Sutura platform and start configuring your tailoring shop. For your security, we have generated a temporary password for your first login. Please log in and change your password immediately.

<x-mail::panel>
**Email Address:** {{ $registration->email }}  
**Temporary Password:** {{ $temporaryPassword }}
</x-mail::panel>

<x-mail::button :url="config('app.frontend_url', 'http://localhost:3000') . '/login'">
Log in to your Dashboard
</x-mail::button>

If you have any questions, feel free to reply to this email.

Best regards,<br>
The Sutura Team
</x-mail::message>

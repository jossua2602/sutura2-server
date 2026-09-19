<x-mail::message>
# Update on your Sutura Registration

Hi {{ $registration->first_name }},

Thank you for your interest in joining Sutura. We have carefully reviewed your application for **{{ $registration->shop_name }}**.

Unfortunately, we are unable to approve your shop registration at this time. 

**Reason for rejection:**
> {{ $registration->rejection_reason }}

If you believe this was a mistake or you have updated your documents, you are welcome to submit a new registration.

Best regards,<br>
The Sutura Team
</x-mail::message>

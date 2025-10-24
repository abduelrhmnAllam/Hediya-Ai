@component('mail::message')
# Verification Code

Your verification code is:

@component('mail::panel')
**{{ $code }}**
@endcomponent

This code will be used to verify your email address.

Thanks,<br>
{{ config('app.name') }}
@endcomponent

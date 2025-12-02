@component('mail::message')
# Email Verification Code

Use the one-time password below to verify your account:

@component('mail::panel')
{{ $otp }}
@endcomponent

This code will expire in 10 minutes.

Thanks,
{{ config('app.name') }}
@endcomponent

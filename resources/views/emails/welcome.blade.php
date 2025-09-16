@component('mail::message')
# Welcome, {{ $user->name }}

Thanks for registering.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
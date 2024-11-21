@component('mail::message')
<p>Hello {{ $user->name }},</p>

<p>You can log in to your account by clicking the following link:</p>

<a href="{{ route('login.process', ['token' => $user->login_token]) }}">{{ route('login.process', ['token' =>
    $user->login_token]) }}</a>

<p>This link will expire in 30 minutes.</p>
@endcomponent
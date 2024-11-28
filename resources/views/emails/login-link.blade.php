@component('mail::message')
<h1>Hei {{ $user->name }},</h1>

<p>Du kan logge inn ved å følge denne lenken:</p>

<a href="{{ route('login.process', ['token' => $user->login_token]) }}">{{ route('login.process', ['token' =>
    $user->login_token]) }}</a>

<p>Den vil gå ut på dato om 30 minutter.</p>
@endcomponent
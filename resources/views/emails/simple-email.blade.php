@component('mail::layout')
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot
<p>{!! $body !!}</p>
<br />
<br />
<br />
<br/>
@slot('footer')
@component('mail::footer')
<img src="{{ url('images/logo-frikirken-w.png') }}" alt="Frikirkens logo" class="img-fluid py-2" style="max-height: 65px">
<br />
<br />
<p>Den Evangelisk Lutherske Frikirke<br />
Organisasjonsnummer: 963 558 406<br />
Pilestredet 69, 0350 Oslo<br />
Telefon: 22 74 86 00<br />
<br />
<a href="{{ route('privacy-policy') }}">Personvernerklæring for web appen, {{ config('app.name') }}.</a>
</p>
@endcomponent
@endslot
@endcomponent

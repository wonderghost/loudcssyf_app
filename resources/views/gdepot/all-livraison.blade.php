@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container uk-container-large">
    <h3><a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>Livraisons</h3>
       <hr class="uk-divider-small">
       <livraison depot-courant="{{Auth::user()->depot()->first()->localisation}}"></livraison>
</div>
</div>
@endsection

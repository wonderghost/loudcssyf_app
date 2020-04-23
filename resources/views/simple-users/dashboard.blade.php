@extends('layouts.app_users')
@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container uk-container-large">
    <h3>Tableau de bord</h3>
    <hr class="uk-divider-small">
    <dashboard the-user="{{Auth::user()->type}}"></dashboard>
</div>
</div>
@endsection('content')

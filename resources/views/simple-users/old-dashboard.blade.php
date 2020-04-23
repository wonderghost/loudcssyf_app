@extends('layouts.app_users')

@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container">
    <h3>Tableau de bord</h3>
    <hr class="uk-divider-small">
    <div class="uk-child-width-1-4@m" uk-grid>
      @if(Auth::user()->type == 'v_standart' || Auth::user()->type == 'v_da')
      <div class="">
        <!-- VENTES -->
        <h3>Ventes</h3>
        <canvas id="resume-vente" width="400" height="400"></canvas>
      </div>
      @endif
    </div>
</div>
</div>
@endsection('content')

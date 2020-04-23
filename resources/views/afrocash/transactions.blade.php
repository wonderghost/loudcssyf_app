@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Toutes les Transaction</h3>
    @if(Auth::user()->type == 'admin' || Auth::user()->type == 'gcga')
    <transaction-afrocash></transaction-afrocash>
    @else
    <transaction-afrocash the-user="{{Auth::user()->localisation}}"></transaction-afrocash>
    @endif
</div>
</div>
@endsection

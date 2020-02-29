@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container uk-container-large">
    <h3><a href="{{url('/')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Operations Afrocash</h3>
    <hr class="uk-divider-small">
    <afrocash></afrocash>
  </div>
</div>
@endsection

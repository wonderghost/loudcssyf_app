@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container uk-container-large">
    <h3>
			<a href="{{url()->previous()}}" uk-tooltip="Retour" uk-icon="icon:arrow-left;ratio:1.5"></a>Inventaire</h3>
      <hr class="uk-divider-small">
      <inventory-depot the-user="{{Auth::user()->depot()->first()->localisation}}"></inventory-depot>
</div>
</div>
@endsection

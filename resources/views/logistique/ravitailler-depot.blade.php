@extends('layouts.app_users')
@section('content')
<div class="uk-section uk-section-default">
  <div class="uk-container uk-container-large">
    <h3><a href="{{url()->previous()}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Ravitailler un Depot</h3>
		<hr class="uk-divider-small">
    <ravitaillement-depot></ravitaillement-depot>
  </div>
</div>
@endsection

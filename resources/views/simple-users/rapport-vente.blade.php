@extends('layouts.app_users')

@section('user_content')
<div class="uk-section">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/')}}" uk-tooltip="Tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Rapport de vente</h3>
		<hr class="uk-divider-small">
		<rapport-component></rapport-component>
	</div>
</div>
@endsection

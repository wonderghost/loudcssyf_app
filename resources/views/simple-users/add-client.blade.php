@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/user/list-client')}}" uk-tooltip="Repertoire" uk-icon="icon:arrow-left;ratio:1.5"></a>Ajouter au Carnet d'adresse</h3>
		<hr class="uk-divider-small">
		<add-contact></add-contact>
	</div>
</div>
@endsection

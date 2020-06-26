@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/user/list-client')}}" uk-tooltip="Repertoire" uk-icon="icon:arrow-left;ratio:1.5"></a>Carnet d'adresse</h3>
		<hr class="uk-divider-small">

		<ul class="uk-subnav uk-subnav-pill" uk-switcher>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Ajouter un contact</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Tous les contacts</a></li>
		</ul>

		<ul class="uk-switcher">
			<li>
				<add-contact></add-contact>
			</li>
			<li>
				<view-contact></view-contact>
			</li>
		</ul>
	</div>
</div>
@endsection

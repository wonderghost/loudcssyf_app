@extends('layouts.app_admin')
@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container ">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouveau Rapport</h3>
		<hr class="uk-divider-small">
		<add-rapport></add-rapport>
	</div>
</div>
@endsection

@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Formules</h3>
		<hr class="uk-divider-small">
		<formule-component></formule-component>
	</div>
</div>
@endsection

@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>Inventaire</h3>
		<hr class="uk-divider-small">
		<inventory></inventory>
	</div>
</div>
@endsection

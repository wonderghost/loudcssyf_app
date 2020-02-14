@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a> Inventaire Reseaux</h3>
		<hr class="uk-divider-small">

		<inventory></inventory>

	</div>
</div>
@endsection

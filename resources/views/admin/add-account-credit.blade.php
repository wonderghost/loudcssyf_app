@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( CGA / REX / AFROCASH)</h3>
		<hr class="uk-divider-small">
		<account the-user="{{Auth::user()->type}}"></account>
	</div>
</div>
@endsection

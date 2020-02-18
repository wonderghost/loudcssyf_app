@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin/add-depot')}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Inventaire des depots</h3>
		<hr class="uk-divider-small">
		<inventory-depot></inventory-depot>
	</div>
</div>
@endsection

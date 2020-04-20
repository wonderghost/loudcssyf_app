@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin/add-depot')}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Depots</h3>
		<hr class="uk-divider-small">
		<ul class="uk-subnav uk-subnav-pill" uk-switcher>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Inventaire</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-hover-small" href="#">Historique de ravitaillement</a></li>
		</ul>

		<!-- This is the container of the content items -->
		<ul class="uk-switcher">
			<li>
				<inventory-depot></inventory-depot>
			</li>
			<li>
				<historique-ravitaillement-depot></historique-ravitaillement-depot>
			</li>
		</ul>		
	</div>
</div>
@endsection

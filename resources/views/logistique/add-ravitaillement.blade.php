@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/user/commandes')}}" uk-tooltip="Toutes les commandes" uk-icon="icon:arrow-left;ratio:1.5"></a> Ravitailler un vendeur</h3>
		<hr class="uk-divider-small"></hr>
		<input type="hidden" id="id_commande" value="{{$commande}}">
		<ravitaillement-command></ravitaillement-command>
	</div>

</div>
@endsection

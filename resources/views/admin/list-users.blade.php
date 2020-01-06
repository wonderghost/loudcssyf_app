@extends('layouts.app_admin')
@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les utilisateurs</h3>
		<hr class="uk-divider-small"></hr>
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
			<div class="uk-width-1-3@m">
				<h4><span uk-icon="icon:search"></span> Search</h4>
				{!!Form::text('search','',['class'=>'uk-input uk-border-rounded','placeholder'=>'...','id'=>'users-search'])!!}
			</div>
			<div class="uk-width-1-3@m">
				<h4><span uk-icon="icon:more-vertical"></span> Filter </h4>
				{!!Form::select('filter',['all'=>'All','v_da'=>'Distributeur Agree','v_standart'=>'Vendeur Standart','logistique'=>"Logistique",'gcga'=>"Gestionnaire Cga",'grex'=>"Gestionnaire Rex",'controleur'=>"Controleur",'gdepot'=>"Gestionnaire Depot",'coursier'=>"Coursier"],null,['class' => 'uk-select uk-border-rounded','id'=>'type-filter'])!!}
			</div>
			<div class="uk-width-1-3@m">

			</div>
		</div>
		<div id="table-loader" style="display: none;" uk-spinner></div>
		<table  class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-hover">
			<thead>
				<tr>
					<th>Username</th>
					<th>Type</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Agence</th>
					<th>Status</th>
					<th colspan="2" class="uk-text-center">-</th>
				</tr>
			</thead>
			<tbody id="list-users"></tbody>
		</table>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {

		$logistique.usersList("{{csrf_token()}}","{{url('admin/users/list')}}","all")

		$("#type-filter").on('change',function () {
			$logistique.usersList("{{csrf_token()}}","{{url('admin/users/list')}}",$(this).val())
		})
		// AJAX SEARCH
		$("#users-search").on('keyup',function(e) {
			if($(this).val() !== "") {
				$logistique.ajaxSearch("{{csrf_token()}}","{{url('admin/search/users')}}",$(this).val())
			} else {
				$logistique.usersList("{{csrf_token()}}","{{url('admin/users/list')}}","all")
			}
		})
	});
</script>
@endsection

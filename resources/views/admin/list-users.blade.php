@extends('layouts.app_admin')
@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les utilisateurs</h3>
		<hr class="uk-divider-small"></hr>
		<!-- CONFIRM PASSWORD MDOAL -->
		<div id="modal-confirm-password" uk-modal="bg-close : false ; esc-close : false">
		    <div class="uk-modal-dialog uk-modal-body">
		        <h2 class="uk-modal-title">Reinitialisation de Mot de passe</h2>
						<div class="uk-alert-warning uk-border-rounded uk-box-shadow-small" uk-alert>
							<p><span uk-icon="icon : warning"></span> Vous etes sur le point de Reinitialiser le mot de passe du compte : <span class="uk-text-bold" id="users"></span></p>
						</div>
						{!!Form::open(['url'=>'admin/reset-user','id'=>'reset-form-user'])!!}
						{!!Form::label('Mot de passe *')!!}
						{!!Form::password('admin_password',['class'=>'uk-input uk-border-rounded uk-margin-small','placeholder'=>'Entrez Votre Mot de passe','autofocus'])!!}
						{!!Form::hidden('user','',['id'=>'user-id'])!!}
            <button class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small uk-modal-close" type="button">Annuler</button>
						{!!Form::submit('Envoyez',['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
						{!!Form::close()!!}
		    </div>
		</div>
<!-- // -->
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
			<div class="uk-width-1-3@m">
				<label><span uk-icon="icon:search"></span> Search</label>
				{!!Form::text('search','',['class'=>'uk-input uk-border-rounded','placeholder'=>'...','id'=>'users-search'])!!}
			</div>
			<div class="uk-width-1-3@m">
				<label><span uk-icon="icon:more-vertical"></span> Type</label>
				{!!Form::select('filter',['all'=>'All','v_da'=>'Distributeur Agree','v_standart'=>'Vendeur Standart','logistique'=>"Logistique",'gcga'=>"Gestionnaire Cga",'grex'=>"Gestionnaire Rex",'controleur'=>"Controleur",'gdepot'=>"Gestionnaire Depot",'coursier'=>"Coursier"],null,['class' => 'uk-select uk-border-rounded','id'=>'type-filter'])!!}
			</div>
			<div class="uk-width-1-3@m">
				<label for=""><span uk-icon = "icon : info"></span> Status</label>
				<select class="uk-select uk-border-rounded" name="" id="">
					<option value="all">All</option>
					<option value="">Debloquer</option>
					<option value="">Bloquer</option>
				</select>
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

		$("#reset-form-user").on('submit',function (e) {
			UIkit.modal($("#modal-confirm-password")).hide(200)
			e.preventDefault()
			$.ajax({
				url : $(this).attr('action'),
				type : 'post',
				dataType : 'json',
				data : $(this).serialize()
			})
			.done(function (data) {
				$("#loader").hide(200)
				UIkit.notification({
					message : "<span uk-icon='icon : check'></span> Success!",
					status : 'success',
					pos : 'top-center',
					timeout : 5000
				})
				console.log(data)
			})
			.fail(function (data) {

				$("#loader").hide(200)
				UIkit.modal($("#modal-confirm-password")).show(200)
				UIkit.notification({
			    message: "<span uk-icon='icon : warning'></span> "+data.responseJSON,
			    status: 'danger',
			    pos: 'top-center',
			    timeout: 5000
				});
			})
		})

	});
</script>
@endsection

@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toute les commandes</h3>
			 <hr class="uk-divider-small">

			 @if(session("_errors"))
			 <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session("_errors")}}</p>
			 </div>
			 @endif
			 @if(session("success"))
			 <div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session("success")}}</p>
			 </div>
			 @endif
			 <ul uk-accordion>
			     <li class="uk-open">
			         <a class="uk-accordion-title" href="#">En attente de confirmation</a>
			         <div class="uk-accordion-content">
								 <table class="uk-table uk-table-divider">
								  <thead>
								 	 <tr>
								 		 <th>Date</th>
								 		 <th>Vendeur</th>
								 		 <th>Item</th>
								 		 <th>Quantite</th>
								 		 <th>Numero_Recu</th>
								 		 <th>Paraboles a livrer</th>
								 		 <th>Status</th>
								 		 <th>Recu</th>
								 		 <th class="uk-text-center" colspan="2">-</th>
								 	 </tr>
								  </thead>
								  <tbody id="list-commands"></tbody>
								 </table>
							 </div>
			     </li>
					 <li>
						 <a href="#" class="uk-accordion-title">Deja Confirmer</a>
						 <div class="uk-accordion-content">
							 <table class="uk-table uk-table-divider">
							  <thead>
							 	<tr>
							 		<th>Date</th>
							 		<th>Vendeur</th>
							 		<th>Item</th>
							 		<th>Quantite</th>
							 		<th>Numero_Recu</th>
							 		<th>Paraboles a livrer</th>
							 		<th>Status</th>
							 		<th>Recu</th>
							 		<th class="uk-text-center" colspan="2">-</th>
							 	</tr>
							  </thead>
							  <tbody id="list-command-confirm"></tbody>
							 </table>
						 </div>
					 </li>
					 <li>
						 <a href="#" class="uk-accordion-title">Livraison a Valider</a>
						 <div class="uk-accordion-content">
							 <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
							 	<thead>
							 		<tr>
							 			<th>Date</th>
							 			<th>Vendeur</th>
							 			<th>ARTICLE</th>
							 			<th>COMMANDE</th>
							 			<th>QUANTITE</th>
							 			<th>STATUS</th>
										<th>-</th>
							 		</tr>
							 	</thead>
								<tbody id="livraison-to-validate"></tbody>
							 </table>
						 </div>
					 </li>
					 <li>
						 <a href="#" class="uk-accordion-title">Livraison validee</a>
						 <div class="uk-accordion-content">
							 <table class="uk-table uk-table-striped uk-table-hover uk-table-small">
							 	<thead>
							 		<tr>
							 			<th>Date</th>
							 			<th>Vendeur</th>
							 			<th>ARTICLE</th>
							 			<th>COMMANDE</th>
							 			<th>QUANTITE</th>
							 			<th>STATUS</th>
										<th>-</th>
							 		</tr>
							 	</thead>
								<tbody id="livraison-validee"></tbody>
							 </table>
						 </div>
					 </li>
			 </ul>

			 <!-- MODAL DETAIL LIVRAISON TO DOWNLOAD -->

			 <div id="modal-livraison-detail" class="uk-flex-top" uk-modal>
			     <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical uk-text-center">
			         <button class="uk-modal-close-default" type="button" uk-close></button>
			         <p class="uk-text-lead">Cliquez sur le button pour telecharger le fichier text</p>
							 <a id="file-link" download="" target="_blank" class="uk-button uk-button-primary uk-border-rounded uk-box-shadow-small">Telecharger <span uk-icon="icon : download"></span> </a>
			     </div>
			 </div>
			 <!-- // -->
			 <!-- MODAL VALIDATE LIVRAISON  -->
			 <div id="modal-livraison-validate" class="uk-flex-top" uk-modal>
			     <div class="uk-modal-dialog uk-modal-body">
			         <button class="uk-modal-close-default" type="button" uk-close></button>
			         <p class="">
								 Vous confirmez l'envoi de materiel chez  :  <span id="vendeur-name" class="uk-text-bold"></span>
							 </p>
							 <table class="uk-table uk-table-divider uk-table-striped uk-table-small uk-table-hover" >
								 <thead>
								 	<tr>
								 		<th>Serial</th>
								 	</tr>
								 </thead>
									 <tbody id="serial-validate-list"></tbody>
							 </table>
							 <hr class="uk-divider-small">
							 {!!Form::open(['url'=>'/user/commandes/validate-livraison-serials'])!!}
							 {!!Form::hidden('livraison','',['id'=>'id_livraison'])!!}
							 {!!Form::label('Confirmez Votre Mot de passe')!!}
							 {!!Form::password('password_confirmation',['class'=>'uk-input uk-border-rounded uk-margin-small','required'=>''])!!}
							 {!!Form::submit('Confirmer',['class'=>'uk-button uk-button-primary uk-border-rounded uk-float-right uk-box-shadow-small'])!!}
							 {!!Form::close()!!}
			     </div>
			 </div>
			 <!-- // -->
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function () {
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}","");
		form.on('submit',function(e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				dataType : 'json',
				data : $(this).serialize()
			})
			.done(function (data) {
				// recuperation des listes de commande [confrimer , non confirmer]
				$adminPage.createTableCommandRowLogistique(data.unconfirmed,['date','vendeur','item','quantite','numero_recu','parabole','status','recu','confirm'],$("#list-commands"),"","{{url('/')}}");
				$adminPage.createTableCommandRowLogistique(data.confirmed,['date','vendeur','item','quantite','numero_recu','parabole','status','recu','confirm'],$("#list-command-confirm"),"","{{url('/')}}");
			})
			.fail(function (data) {
				alert(data.responseJSON.message)
				$(location).attr('href',"{{url()->current()}}")
			});
		});

		form.submit()
		setInterval(function() {
			form.submit();
		},20000);

		// recuperation de la liste des livraison a Valider
		$logistique.listLivraisonToConfirm($adminPage,"{{csrf_token()}}","{{url('/user/commandes/livraison-validation')}}")
		// recuperation de la liste des livraisons deja validee
		$logistique.listLivraisonValidee("{{csrf_token()}}","{{url('/user/commandes/livraison-validee')}}")
	});
</script>
@endsection

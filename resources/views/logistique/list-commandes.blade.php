@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toute les commandes</h3>
			 <hr class="uk-divider-small">

			 @if(session("_errors"))
			 <div class="uk-alert-danger" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
				 <p>{{session("_errors")}}</p>
			 </div>
			 @endif
			 @if(session("success"))
			 <div class="uk-alert-success" uk-alert>
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
			 </ul>
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
	});
</script>
@endsection

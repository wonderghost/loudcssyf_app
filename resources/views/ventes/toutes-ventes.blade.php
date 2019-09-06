@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Toutes les ventes</h3>
		<hr class="uk-divider-small">
		<!-- // -->
		<input type="search" search="" placeholder="Recherche" class="uk-input">
		<!-- // -->
		<hr class="uk-divider-small">
		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
		    <li><a href="#">Abonnement</a></li>
		    <li><a href="#">Reabonnement</a></li>
		    <li><a href="#">Migration</a></li>
		    <li><a href="#">Upgrade</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
		    <li>
		    	<!-- RECRUTEMENT -->
		    	<table class="uk-table uk-table-divider">
					<thead>
						<tr>
							<th>Numero abonne</th>
							<th>Prenom</th>
							<th>Nom</th>
							<th>Formule</th>
							<th>Numero Materiel</th>
						</tr>
					</thead>
					<tbody id="liste-recrutement"></tbody>	
				</table>
		    </li>
		    <li>
		    	<!-- REABONNEMENT -->
		    	<table class="uk-table uk-table-divider">
					<thead>
						<tr>
							<th>Numero Materiel</th>
							<th>Nom</th>
							<th>Prenom</th>
							<th>Numero client</th>
							<th>Formule</th>
							<th>Expiration</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
		    </li>
		    <li>
		    	<!-- MIGRATION -->
		    	<table class="uk-table uk-table-divider">
					<thead>
						<tr>
							<th>Numero Materiel</th>
							<th>Nom</th>
							<th>Prenom</th>
							<th>Numero client</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
		    </li>
		    <li>
		    	<!-- UPGRADE -->
		    	<table class="uk-table uk-table-divider">
					<thead>
						<tr>
							<th>Numero Materiel</th>
							<th>Nom</th>
							<th>Prenom</th>
							<th>Numero client</th>
							<th>Ancienne Formule</th>
							<th>Nouvelle Formule</th>
							<th>Expiration</th>
						</tr>
					</thead>
					<tbody id="list-upgrade"></tbody>
				</table>
		    </li>
		</ul>

	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {

		// HISORIQUE DES RECRUTEMENTS

		var form = $adminPage.makeForm("{{csrf_token()}}","","");

		form.on('submit',function(e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				data : $(this).serialize(),
				dataType : 'json'
			})
			.done(function(data) {
				if(data && data != 'fail') {
					$adminPage.createTableRecrutement(data,['material','nom','prenom','client','formule'],$('#liste-recrutement'));
				} 
				else {
					console.log(data);
				}
			})
			.fail(function(data) {
				console.log(data);
			});

		});

		form.submit();
		
		window.setInterval(function() {
			form.submit();
		},2000);




	});
</script>
@endsection
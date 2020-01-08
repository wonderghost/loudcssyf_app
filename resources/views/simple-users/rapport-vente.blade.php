@extends('layouts.app_users')

@section('user_content')
<div class="uk-section">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/')}}" uk-tooltip="Toutes les ventes" uk-icon="icon:arrow-left;ratio:1.5"></a> Rapport de vente</h3>
		<hr class="uk-divider-small">

		{!!Form::open()!!}
	<div class="uk-child-width-1-4@m" uk-grid>
		<div>

			<span uk-icon="icon:search"></span> {!!Form::label('Search')!!}
			{!!Form::text('search','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Search...'])!!}
		</div>
		<div>
			<span uk-icon="icon:calendar"></span> {!!Form::label('Du')!!}
			{!!Form::date('date_debut','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Du...'])!!}
		</div>
		<div>
			<span uk-icon="icon:calendar"></span> {!!Form::label('Au')!!}
			{!!Form::date('date_fin','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Au...'])!!}
		</div>

		<div>
			{!!Form::label('Commission (GNF)')!!}
			{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center uk-border-rounded','disabled','id'=>'commission-jour'])!!}
		</div>
	</div>
	{!!Form::close()!!}

	<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left">
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Recrutement</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Reabonnement</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Migration</a></li>
	</ul>

	<ul class="uk-switcher uk-margin">
		<li>
			<!-- RECRUTEMENT -->
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
				<thead>
					<tr>
						<th>Date</th>
						<th>Type</th>
						<th>Credit</th>
						<th>Quantite</th>
						<th>Montant Ttc</th>
						<th>Commission</th>
						<th>Promo</th>
						<th>Paiement Commission</th>
					</tr>
				</thead>
				<tbody id="recrutement-list"><div id="loader" uk-spinner></div></tbody>
			</table>
			<!-- // -->
		</li>
		<li>
			<!-- REABONNEMENT -->
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
				<thead>
					<tr>
						<th>Date</th>
						<th>Type</th>
						<th>Credit</th>
						<th>Quantite</th>
						<th>Montant Ttc</th>
						<th>Commission</th>
						<th>Promo</th>
						<th>Paiement Commission</th>
					</tr>
				</thead>
				<tbody id="reabonnement-list"><div id="loader" uk-spinner></div></tbody>
			</table>
			<!-- // -->
		</li>
		<li>
			<!-- MIGRATION -->
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
				<thead>
					<tr>
						<th>Date</th>
						<th>Type</th>
						<th>Credit</th>
						<th>Quantite</th>
						<th>Montant Ttc</th>
						<th>Commission</th>
						<th>Promo</th>
					</tr>
				</thead>
				<tbody id="migration-list"><div id="loader" uk-spinner></div></tbody>
			</table>
			<!-- // -->
		</li>
	</ul>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {
		setInterval(function () {
			$logistique.getListRapportVente("{{csrf_token()}}","{{url('/user/rapport-ventes/get-list')}}")
		}, 10000);
		$logistique.getListRapportVente("{{csrf_token()}}","{{url('/user/rapport-ventes/get-list')}}")
	});
</script>
@endsection

@extends('layouts.app_users')

@section('user_content')
<div class="uk-section">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/')}}" uk-tooltip="Toutes les ventes" uk-icon="icon:arrow-left;ratio:1.5"></a> Rapport de vente</h3>
		<hr class="uk-divider-small">

		<!-- This is the modal -->
		<div id="paiement-commission" uk-modal="esc-close : false ; bg-close : false">
		    <div class="uk-modal-dialog uk-modal-body">
		        <h3 class="uk-modal-title"><i class="material-icons">monetization_on</i> Paiement commission</h3>
		        {!!Form::open(['url'=>'/user/rapport-ventes/pay-commission','id'=>'pay-commission-form'])!!}
						{!!Form::label('Montant total Cumule')!!}
						{!!Form::text("commission_total",'',['class'=>'uk-input uk-margin-small uk-border-rounded','disabled','id'=>'commission-cumulee'])!!}
						{!!Form::label('Confirmez le mot de passe')!!}
						{!!Form::password('password_confirm',['class'=>'uk-input uk-border-rounded uk-margin-small','autofocus'])!!}
						{!!Form::submit('validez',['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
						{!!Form::close()!!}
		        <p class="uk-text-right">
		            <button class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-danger uk-modal-close" type="button">Annuler</button>
		        </p>
		    </div>
		</div>
		<!-- // -->
		{!!Form::open(['url'=>'user/rapport-ventes/filter','id'=>'filter-vendeur-rapport'])!!}
	<div class="uk-grid-small" uk-grid>
		<div class="uk-width-1-6@m">
			<span uk-icon="icon:calendar"></span> {!!Form::label('Du')!!}
			{!!Form::date('date_debut','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Du...'])!!}
		</div>
		<div class="uk-width-1-6@m">
			<span uk-icon="icon:calendar"></span> {!!Form::label('Au')!!}
			{!!Form::date('date_fin','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Au...'])!!}
		</div>

		<div class="uk-width-1-5@m">
			{!!Form::label('Commission (GNF)')!!}
			{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center uk-border-rounded','disabled','id'=>'commission-jour'])!!}
			@if(Auth::user()->type == 'v_da')
			<button type="button" name="button" class="uk-button uk-button-small uk-button-primary uk-box-shadow-small uk-border-rounded" uk-toggle="target: #paiement-commission">Paiement Commission</button>
			@endif
			{!!Form::submit('ok',['class'=>'uk-button uk-button-small uk-button-primary uk-visible@m uk-box-shadow-small uk-border-rounded uk-position-absolute uk-margin-small-top uk-margin-left'])!!}
			{!!Form::submit('ok',['class'=>'uk-button uk-button-small uk-button-primary uk-hidden@m uk-box-shadow-small uk-border-rounded'])!!}
			<a href="{{url()->current()}}" uk-tooltip="retirer le filtre" class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-hidden@m">retirer le filtre</a>
		</div>

	</div>
	{!!Form::close()!!}
	<a href="{{url()->current()}}" uk-tooltip="retirer le filtre" class="uk-float uk-float-right uk-visible@m"> <i class="material-icons">delete_sweep</i> </a>

	<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left">
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Recrutement</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Reabonnement</a></li>
			<li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Migration</a></li>
	</ul>

	<ul class="uk-switcher uk-margin">
		<li>
			<!-- RECRUTEMENT -->
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
			<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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

		// $logistique.getListRapportVente("{{csrf_token()}}","{{url('/user/rapport-ventes/get-list')}}")
		//
		$logistique.payCommission()
		//
		// $logistique.filterRapportForVendeurs(intervalId)
	});
</script>
@endsection

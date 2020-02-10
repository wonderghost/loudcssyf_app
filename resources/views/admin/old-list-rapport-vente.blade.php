@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les Rapports</h3>
		<hr class="uk-divider-small">

		<input type="hidden" id="user-type" value="{{Auth::user()->type}}">
			{!!Form::open()!!}
		<div class="uk-child-width-1-5@m" uk-grid>
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
				<span uk-icon="icon:location"></span> {!!Form::label('Vendeurs')!!}
				<select name="vendeurs" class="uk-select uk-margin-small uk-border-rounded">
					<option value="all">Tous</option>
					@if($users)
					@foreach($users as $user)
					<option value="{{$user->username}}">({{$user->localisation}})</option>
					@endforeach
					@endif
				</select>
			</div>
			<div>
				{!!Form::label('Commission Cumulee (GNF)')!!}
				{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center uk-border-rounded','disabled','id'=>'commission-cumulee'])!!}
			</div>
		</div>
		{!!Form::close()!!}

		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
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
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
							<th>Promo</th>
							<th>Paiement Commission</th>
						</tr>
					</thead>
					<tbody id="recrutement-list"></tbody>
				</table>
				<!-- // -->
				<!-- // -->
				<ul class="uk-pagination uk-flex uk-flex-center" id="recrutement-paginate">
						<li><a><span>Page : </span><span id="recrutement-page">1</span> </a> </li>
				    <li><a class="paginate-link" data-id="previous"><span uk-pagination-previous></span> Precedent</a></li>
				    <li><a class="paginate-link" data-id="next">Suivant <span uk-pagination-next></span> </a></li>
				</ul>
			</li>
			<li>
				<!-- REABONNEMENT -->
				<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
					<thead>
						<tr>
							<th>Date</th>
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
							<th>Promo</th>
							<th>Paiement Commission</th>
						</tr>
					</thead>
					<tbody id="reabonnement-list"></tbody>
				</table>
				<!-- // -->
				<ul class="uk-pagination uk-flex uk-flex-center" id="reabonnement-paginate">
						<li><a><span>Page : </span><span id="page">1</span> </a> </li>
				    <li><a class="paginate-link" data-id="previous"><span uk-pagination-previous></span> Precedent</a></li>
				    <li><a class="paginate-link" data-id="next">Suivant <span uk-pagination-next></span> </a></li>
				</ul>


			</li>
			<li>
				<!-- MIGRATION -->
				<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
					<thead>
						<tr>
							<th>Date</th>
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
							<th>Promo</th>
						</tr>
					</thead>
					<tbody id="migration-list"></tbody>
				</table>
				<!-- // -->
				<ul class="uk-pagination uk-flex uk-flex-center" id="migration-paginate">
						<li><a><span>Page : </span><span id="migration-page">1</span> </a> </li>
				    <li><a class="paginate-link" data-id="previous"><span uk-pagination-previous></span> Precedent</a></li>
				    <li><a class="paginate-link" data-id="next">Suivant <span uk-pagination-next></span> </a></li>
				</ul>
			</li>
		</ul>

	</div>
</div>
@endsection
@section("script")
<script type="text/javascript">
	$(function() {
			if($('#user-type').val() == 'admin') {
				$logistique.reabonnementListForAdmin("{{url('/admin/rapport/list-reabonnement')}}")
				$logistique.recrutementListFormAdmin("{{url('admin/rapport/list-recrutement')}}")
				$logistique.migrationListForAdmin("{{url('admin/rapport/list-migration')}}")
				setInterval(function () {
					$logistique.getCommissionCumulee("{{url('admin/rapport/commission-total')}}")
				}, 10000);
				$logistique.getCommissionCumulee("{{url('admin/rapport/commission-total')}}")
			} else {
				$logistique.reabonnementListForAdmin("{{url('user/rapport/reabonnement-rapport')}}")
				$logistique.recrutementListFormAdmin("{{url('user/rapport/recrutement-rapport')}}")
				$logistique.migrationListForAdmin("{{url('user/rapport/migration-rapport')}}")
				setInterval(function () {
					$logistique.getCommissionCumulee("{{url('user/rapport/commission-total')}}")
				}, 10000);
				$logistique.getCommissionCumulee("{{url('user/rapport/commission-total')}}")
			}
		//
	});
</script>
@endsection

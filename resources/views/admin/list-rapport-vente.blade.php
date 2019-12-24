@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les Rapports</h3>
		<hr class="uk-divider-small">

			{!!Form::open()!!}
		<div class="uk-child-width-1-4@m" uk-grid>
			<div>

				<span uk-icon="icon:search"></span> {!!Form::label('Search')!!}

				{!!Form::text('search','',['class'=>'uk-input uk-margin-small','placeholder'=>'Search...'])!!}
			</div>
			<div>
				<span uk-icon="icon:calendar"></span> {!!Form::label('Date')!!}
				{!!Form::text('date','',['class'=>'uk-input uk-margin-small', 'placeholder'=>'date...'])!!}
			</div>
			<div>
				<span uk-icon="icon:location"></span> {!!Form::label('Vendeurs')!!}
				<select name="vendeurs" class="uk-select uk-margin-small">
				</select>
			</div>
			<div>
				{!!Form::label('Commission du jour (GNF)')!!}
				{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center','disabled'])!!}
			</div>
		</div>
		{!!Form::close()!!}

		<ul uk-tab>
		    <li><a href="#">Recrutement</a></li>
		    <li><a href="#">Reabonnement</a></li>
		    <li><a href="#">Migration</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
			<li>
				<!-- RECRUTEMENT -->
				<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
					<thead>
						<tr>
							<th>Date</th>
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
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
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
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
							<th>Vendeurs</th>
							<th>Type</th>
							<th>Credit</th>
							<th>Quantite</th>
							<th>Montant Ttc</th>
							<th>Commission</th>
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
@section("script")
<script type="text/javascript">
	$(function() {

		$logistique.getListRapportVente("{{csrf_token()}}","{{url('/admin/get-rapport')}}")

	});
</script>
@endsection

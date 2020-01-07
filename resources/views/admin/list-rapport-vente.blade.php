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
				{!!Form::text('date_debut','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Du...'])!!}
			</div>
			<div>
				<span uk-icon="icon:calendar"></span> {!!Form::label('Au')!!}
				{!!Form::text('date_fin','',['class'=>'uk-input uk-margin-small uk-border-rounded', 'placeholder'=>'Au...'])!!}
			</div>
			<div>
				<span uk-icon="icon:location"></span> {!!Form::label('Vendeurs')!!}
				<select name="vendeurs" class="uk-select uk-margin-small uk-border-rounded">
					@if($users)
					@foreach($users as $user)
					<option value="{{$user->username}}">({{$user->localisation}})</option>
					@endforeach
					@endif
				</select>
			</div>
			<div>
				{!!Form::label('Commission du jour (GNF)')!!}
				{!!Form::text('commission_jour','N/A',['class'=>'uk-input uk-margin-small uk-text-center uk-border-rounded','disabled','id'=>'commission-jour'])!!}
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
		setInterval(function () {
			if($('#user-type').val() == 'admin') {
				$logistique.getListRapportVente("{{csrf_token()}}","{{url('/admin/get-rapport')}}")
			} else {
				$logistique.getListRapportVente("{{csrf_token()}}","{{url('/user/get-rapport')}}")
			}
		},10000);

		if($('#user-type').val() == 'admin') {
			$logistique.getListRapportVente("{{csrf_token()}}","{{url('/admin/get-rapport')}}")
		} else {
			$logistique.getListRapportVente("{{csrf_token()}}","{{url('/user/get-rapport')}}")
		}
		//
	});
</script>
@endsection

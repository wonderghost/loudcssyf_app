@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section">
	<div class="uk-container">
		<h3><a href="{{url('/admin/add-depot')}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les Materiels</h3>
		<hr class="uk-divider-small">
		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Inventaire</a></li>
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Numeros Materiels</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
		    <li>
					<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
						<div>
							<h4><span uk-icon="icon:search"></span> Search</h4>
							{!!Form::text('search','',['class'=>'uk-input uk-border-rounded','placeholder'=>'...'])!!}
						</div>
						<div>
							<h4><span uk-icon="icon:more-vertical"></span> Filter by depots </h4>
							<select class="uk-select uk-border-rounded" id="mat-filter">
								<option value="all">Tous</option>
								@if($depots)
								@foreach($depots as $values)
								<option value="{{$values->localisation}}">{{$values->localisation}}</option>
								@endforeach
								@endif
							</select>
						</div>
					</div>
					<div uk-spinner id="_loader" style="display: none;"></div>
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
						<thead>
							<tr>
								<th>Designation</th>
								<th>Quantite</th>
								<th>Prix Initial (GNF)</th>
								<th>Prix TTC (GNF)</th>
								<th>HT (GNF)</th>
								<th>TVA (18%) (GNF)</th>
								<th>Marge</th>
								<th class="uk-text-center" colspan="2">-</th>
							</tr>
						</thead>
						<tbody id="mat-list"></tbody>
					</table>
				</li>
				<li>
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
						<thead>
							<tr>
								<th>Serial</th>
								<th>Vendeurs</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody id="serial-list"></tbody>
					</table>

					<ul class="uk-pagination">
							<li><a><span>Page</span> <span id="page">1</span></a></li>
							<li><a><span id="inf"></span></a></li>
					    <li><a class="paginate-link" id="previous"> <span uk-pagination-previous></span> Precedent</a></li>
					    <li><a class="paginate-link" id="next">Suivant <span uk-pagination-next></span></a></li>
					</ul>
				</li>
		</ul>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {

		// console.log($adminPage.activeData);
		// return 0;
		setInterval(function() {
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val(),false);
		},50000);
		$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val(),false);
		// filtrer par depot
		$('#mat-filter').on('change',function () {
			$("#_loader").show(500);
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val(),false);
		});
		//Liste des Numeros de SERIES
		$logistique.ListSerialNumber($adminPage,"{{csrf_token()}}","{{url('/admin/get-serialNumber')}}")


	});
</script>
@endsection

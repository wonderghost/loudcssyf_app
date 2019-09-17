@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section">
	<div class="uk-container">
		<h3><a href="{{url('/admin/add-depot')}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les Materiels</h3>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
			<div>
				<h4><span uk-icon="icon:search"></span> Search</h4>
				{!!Form::text('search','',['class'=>'uk-input','placeholder'=>'...'])!!}
			</div>
			<div>
				<h4><span uk-icon="icon:more-vertical"></span> Filter by depots </h4>
				<select class="uk-select" id="mat-filter">
					<option value="all">Tous</option>
					@if($depots)
					@foreach($depots as $values)
					<option value="{{$values->localisation}}">{{$values->localisation}}</option>
					@endforeach
					@endif
				</select>
			</div>
		</div>
		<div uk-spinner id="loader" style="display: none;"></div>
		<table class="uk-table uk-table-divider">
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
		},10000);

		$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val(),false);
		// filtrer par depot
		$('#mat-filter').on('change',function () {
			$("#loader").show(500);
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val(),false);
		});

		//


	});
</script>
@endsection

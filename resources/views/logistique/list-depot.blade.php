@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin/add-depot')}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Tous les depots</h3>
		<hr class="uk-divider-small">
		<div uk-spinner id="loader" style="display: none;"></div>
		<table class="uk-table uk-table-divider">
			<thead>
				<tr>
					<th>Depots</th>
					<th>Materiels</th>
					<th>Prix TTC (GNF)</th>
					<th>HT (GNF)</th>
					<th>TVA (18%) (GNF)</th>
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
		// $adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		// // filtrer par depot
		// $('#mat-filter').on('change',function () {
		// 	$("#loader").show(500);
		// 	$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		// })
	});
</script>
@endsection
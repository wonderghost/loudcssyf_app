@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			@if(Auth::user()->type == 'admin')
			<a href="{{url('/admin')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			@else
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			@endif
			 Inventaire</h3>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
			<div>
				<h4><span uk-icon="icon:search"></span> Search</h4>
				{!!Form::text('search','',['class'=>'uk-input','placeholder'=>'...'])!!}
				<button class="uk-button-default uk-border-rounded">Valider <span uk-icon="icon:check;ratio:.8"></span></button>
			</div>
			<div>
				<h4><span uk-icon="icon:more-vertical"></span> Vendeurs </h4>
				<select class="uk-select uk-text-bold" id="mat-filter">
					<option value="all">Tous</option>
					@if($users)
					@foreach($users as $key => $values)
					<option value="{{$values->username}}">{{$values->username.'('.$values->localisation.'/'.$agence[$key]->societe.')'}}</option>
					@endforeach
					@endif
				</select>
			</div>
		</div>
		<div uk-spinner id="loader" style="display: none;"></div>
		<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
			<thead>
				<tr>
					<th>Designation</th>
					<th>Quantite</th>
					<th>Prix TTC (GNF)</th>
					<th>HT (GNF)</th>
					<th>TVA (18%) (GNF)</th>
					<!-- <th>Marge</th> -->
					<!-- <th class="uk-text-center" colspan="2">-</th> -->
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
		setInterval(function() {
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		},10000);
		$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		// filtrer par depot
		$('#mat-filter').on('change',function () {
			$("#loader").show(100);
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		})
	});
</script>
@endsection

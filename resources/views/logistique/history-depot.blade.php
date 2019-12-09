@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin/list-material')}}" uk-tooltip="tous les materiels" uk-icon="icon:arrow-left;ratio:1.5"></a> Historique d'entree</h3>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
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
			<div>
				<h4><span uk-icon="icon:calendar"></span> Filter by Date</h4>
				<div class="uk-child-width-1-2" uk-grid>
					<div><input type="date" class="uk-input" name="" placeholder="From"></div>
					<div><input type="date" class="uk-input" name="" placeholder="To">
						<button class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
					</div>
				</div>
			</div>
		</div>
		<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small">
			<thead>
				<tr>
					<th>Designation</th>
					<th>Quantite</th>
					<th>Depot</th>
					<th>Origine</th>
					<th>Date</th>
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
		$adminPage.getListCommand("{{csrf_token()}}","{{url()->current()}}",$("#mat-filter").val());
	});
</script>
@endsection

@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/user/my-inventory')}}" uk-tooltip="tous les materiels" uk-icon="icon:arrow-left;ratio:1.5"></a> Historique de ravitaillement</h3>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider" uk-grid>
			<div>
				<h4><span uk-icon="icon:search"></span> Search</h4>
				{!!Form::text('search','',['class'=>'uk-input','placeholder'=>'...'])!!}
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
		<input type="hidden" value="{{Auth::user()->username}}" id="mat-filter">
		<table class="uk-table uk-table-divider">
			<thead>
				<tr>
					<th>Designation</th>
					<th>Quantite</th>
					<th>Depot</th>
					<th>-</th>
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
		$adminPage.getListRavitaillement("{{csrf_token()}}","{{url()->current()}}",$("#mat-filter").val());
	});
</script>
@endsection
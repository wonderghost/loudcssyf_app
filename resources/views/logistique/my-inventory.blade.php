@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Inventaire</h3>
		<hr class="uk-divider-small">
		<ul uk-tab>
		    <li><a href="#">Materiel</a></li>
		    <li><a href="#">Numero de Materiel</a></li>
		</ul>
		<ul class="uk-switcher uk-margin">
		    <li>
					<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
						<div>
							<h4><span uk-icon="icon:search"></span> Search</h4>
							{!!Form::text('search','',['class'=>'uk-input','placeholder'=>'...'])!!}
							<button class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
						</div>
						<div>
							<input type="hidden" id="mat-filter" value="{{Auth::user()->username}}">
							@if($solde)
							<div class="uk-text-lead">
								<div class="uk-grid-small" uk-grid>
										<div class="uk-width-expand" uk-leader="fill: -">SOLDE CGA (GNF)</div>
										<div>{{number_format($solde->solde)}}</div>
								</div>
							</div>
							@endif
							@if($rex)
							<div class="uk-text-lead">
								<div class="uk-grid-small" uk-grid>
										<div class="uk-width-expand" uk-leader="fill: -">SOLDE REX (GNF)</div>
										<div>{{number_format($rex->solde)}}</div>
								</div>
							</div>
							@endif
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
								<th class="uk-text-center" colspan="2">-</th>
							</tr>
						</thead>
						<tbody id="mat-list"></tbody>
					</table>
				</li>
				<li>
					<!-- serial number -->
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small" >
						<thead>
							<tr>
								<th>Serials</th>
								<th>Article</th>
								<th>Vendeurs</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody id="serials-vendeurs"></tbody>
					</table>
				</li>
		</ul>

	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function() {
		setInterval(function() {
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());
		},20000);

		$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}",$('#mat-filter').val());

		$logistique.listSerialByVendeur("{{csrf_token()}}","{{url('/user/my-inventory/serials')}}","{{Auth::user()->username}}")
	});
</script>
@endsection

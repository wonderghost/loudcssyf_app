@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-large">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Inventaire</h3>
		<hr class="uk-divider-small">
		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-fade">
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Materiel</a></li>
		    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Numero de Materiel</a></li>
		</ul>
		<ul class="uk-switcher uk-margin">
		    <li>
					<!-- // -->
					<div class="uk-grid-small uk-child-width-1-4@m uk-child-width-1-1@s" uk-grid>
						@if($solde)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light">
							<div class="uk-card-header">
								<h3 class="uk-card-title uk-text-small">CGA (GNF)</h3>
							</div>
							<div class="uk-card-body uk-text-center uk-text-lead">{{number_format($solde->solde)}}</div>
						</div>
					</div>
						@endif
						@if($afrocashsm)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light">
							<div class="uk-card-header">
								<h3 class="uk-card-title uk-text-small">AFROCASH SEMI GROSSISTE (GNF)</h3>
							</div>
							<div class="uk-card-body uk-text-center uk-text-lead">{{number_format($afrocashsm->solde)}}</div>
						</div>
					</div>
						@endif
						@if($afrocashcourant)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light">
							<div class="uk-card-header">
								<h3 class="uk-card-title uk-text-small">AFROCASH COURANT (GNF)</h3>
							</div>
							<div class="uk-card-body uk-text-center uk-text-lead">{{number_format($afrocashcourant->solde)}}</div>
						</div>
					</div>
						@endif
						@if($rex)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light">
							<div class="uk-card-header">
								<h3 class="uk-card-title uk-text-small">REX (GNF)</h3>
							</div>
							<div class="uk-card-body uk-text-center uk-text-lead">{{number_format($rex->solde)}}</div>
						</div>
					</div>
						@endif
					</div>
					<!-- // -->
					<div uk-spinner id="loader" style="display: none;"></div>
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
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
			$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}","{{Auth::user()->username}}");
		},20000);

		$adminPage.getListMaterial("{{csrf_token()}}","{{url()->current()}}","{{Auth::user()->username}}");

		$logistique.listSerialByVendeur("{{csrf_token()}}","{{url('/user/my-inventory/serials')}}","{{Auth::user()->username}}")
	});
</script>
@endsection

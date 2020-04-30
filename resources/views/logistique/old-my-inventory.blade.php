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
						<div class="uk-card uk-card-primary uk-border-rounded uk-light uk-height-small">
							<div class="uk-card-header">
								<h3 class="uk-card-title">CGA (GNF)</h3>
								<p class="uk-text-lead" style="color : #fff;">{{number_format($solde->solde)}}</p>
							</div>
						</div>
					</div>
						@endif
						@if($afrocashsm)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light uk-height-small">
							<div class="uk-card-header">
								<h3 class="uk-card-title ">AFROCASH GROSSISTE (GNF)</h3>
								<p class="uk-text-lead" style="color : #fff;">{{number_format($afrocashsm->solde)}}</p>
							</div>
						</div>
					</div>
						@endif
						@if($afrocashcourant)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light uk-height-small">
							<div class="uk-card-header">
								<h3 class="uk-card-title">AFROCASH COURANT (GNF)</h3>
								<p class="uk-text-lead" style="color : #fff;">{{number_format($afrocashcourant->solde)}}</p>
							</div>
						</div>
					</div>
						@endif
						@if($rex)
						<div class="">
						<div class="uk-card uk-card-primary uk-border-rounded uk-light uk-height-small">
							<div class="uk-card-header">
								<h3 class="uk-card-title">REX (GNF)</h3>
								<p class="uk-text-lead" style="color : #fff;">{{number_format($rex->solde)}}</p>
							</div>
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
					<div class="uk-grid-small" uk-grid>
						<div class="uk-width-1-4@m">
							<label for=""> <span uk-icon="icon : search"></span> Recherche</label>
							<input type="text" name="" value="" class="uk-input uk-border-rounded uk-margin-small" placeholder="...">
						</div>
						<div class="uk-width-1-4@m">
							<label for=""> <span uk-icon ="icon : info"></span> Status</label>
							<select class="uk-select uk-border-rounded uk-margin-small" name="">
								<option value="">-- Status --</option>
								<option value="actif">Actif</option>
								<option value="inactif">Inactif</option>
							</select>
						</div>
					</div>
					<!-- serial number -->
					<table class="uk-table uk-table-divider uk-table-striped uk-table-hover uk-table-small uk-table-responsive">
						<thead>
							<tr>
								<th>Serials</th>
								<th>Article</th>
								<th>Vendeurs</th>
								<th>Status</th>
								<th>Origine</th>
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

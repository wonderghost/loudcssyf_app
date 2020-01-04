@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container uk-container-medium">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Nouvel Commande</h3>
			 <hr class="uk-divider-small">
			 <div uk-spinner style="display: none;" id="loader"></div>
			 @if(session('success'))
			 <div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
			 	<p>{{session('success')}}</p>
			 </div>
			 @endif
			 @if(session('_error'))
			 <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
			 	<p>{{session('_error')}}</p>
			 </div>
			 @endif
			 @if($errors->any())
			 @foreach($errors->all() as $error)
			 <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
			 	<p>{{$error}}</p>
			 </div>
			 @endforeach
			 @endif
			 <ul class="" uk-tab="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
				 @php
				 $test = App\Exemplaire::where('status','inactif')->first();
				 $par = App\Produits::where("libelle",'parabole')->first();
				 @endphp
				 @if($test && $par)
			     <li><a class="" href="#">Materiel</a></li>
					 @endif
			     <li><a class="" href="#">Credit CGA</a></li>
			     <li><a class="" href="#">Credit REX</a></li>
					 @if(Auth::user()->type == 'v_standart')
			     <li><a class="" href="#">AFROCASH SEMI GROSSISTE</a></li>
					 @endif
			 </ul>
			 <ul class="uk-switcher uk-margin">
				 @if($test && $par)
			     <li>
						 <!-- COMMANDE MATERIEL -->
						 {!!Form::open(['url'=>'/user/new-command/material','id'=>'command-form'])!!}

						 {!!Form::hidden('compense',$compense,['id'=>'compense'])!!}

						 @if($material)

						 <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
							 <div>
									 <label uk-tooltip="{{$material->libelle}}">Kit Complet</label>
							 </div>
							 <div id="{{$material->reference}}">
								 <label>Qte{!!Form::number('quantite',0,['class'=>'uk-input uk-text-center  qte','placeholder'=>'Quantite','id'=>'qte-'.$material->reference])!!}</label>
								 {!!Form::hidden('mat-reference',$material->reference)!!}
								 <!-- PRIX DE VENTE  -->
								 {!!Form::hidden('',$material->prix_vente,['id'=>'prix-normal-'.$material->reference])!!}
								 <!-- // -->
								 <!-- PRIX D'ACHAT UNITAIRE -->
								 @if(Auth::user()->type == 'v_da')
								 {!!Form::hidden('',($material->prix_vente-($material->marge/1.18)),['class'=>'totaltopay','id'=>'totaltopay-'.$material->reference])!!}
								 @else
								 {!!Form::hidden('',($material->prix_vente-(0/1.18)),['class'=>'totaltopay','id'=>'totaltopay-'.$material->reference])!!}
								 @endif
								 <!-- // -->
								 {!!Form::hidden('',0,['class'=>'all-somme','id'=>'somm-'.$material->reference])!!}
							 </div>
							 <div>
								 <label>Prix TTC (GNF){!!Form::text('prix_ttc',$material->prix_initial,['class'=>'uk-input','disabled','id'=>'ttc-'.$material->reference])!!}</label>
							 </div>
							 <div>
								 <label>HT (GNF){!!Form::text('ht',number_format(($material->prix_vente/1.18),0,'.',' '),['class'=>'uk-input ht','disabled','id'=>'ht-'.$material->reference])!!}</label>
							 </div>
							 <div>
								 <label>TVA (18%) (GNF){!!Form::text('tva',number_format(($material->prix_vente-($material->prix_vente/1.18)),0,'.',' '),['class'=>'uk-input tva','disabled','id'=>'tva-'.$material->reference])!!}</label>
							 </div>
							 <div>
								 <label>Montant TTC (GNF){!!Form::text('montant',0,['class'=>'uk-input montant-ttc','disabled','id'=>'montant-'.$material->reference])!!}</label>
							 </div>
							 <!-- SUBVENTION -->
							 <div></div>
							 <div><label>Subvention</label></div>
							 <div>
								 <?php $subv = $material->prix_initial-$material->prix_vente; ?>
								 {!!Form::text('',$subv,['class'=>'uk-input','disabled','id'=>'sub-'.$material->reference])!!}
							 </div>
							 <div>
								 {!!Form::text('',number_format($subv/1.18,0,'',' '),['class'=>'uk-input','disabled'])!!}
							 </div>
							 <div>
								 {!!Form::text('',number_format($subv-($subv/1.18),0,'',' '),['class'=>'uk-input','disabled'])!!}
							 </div>
							 <div>
								 {!!Form::text('total_sub',0,['class'=>'uk-input tsubv','disabled','id'=>'tsub-'.$material->reference])!!}
							 </div>
							 <!-- MARGE -->
							 <div></div>
							 <div><label>Marge</label></div>
							 <div>
								 {!!Form::text('',$material->marge,['class'=>'uk-input','disabled'])!!}
							 </div>
							 <div>
								 {!!Form::text('',(int)($material->marge/1.18),['class'=>'uk-input','disabled','id'=>'margeht-'.$material->reference])!!}
							 </div>
							 <div>
								 {!!Form::text('',number_format($material->marge-($material->marge/1.18),0,'',' '),['class'=>'uk-input','disabled'])!!}
							 </div>
							 <div>
								 {!!Form::text('total_marge',0,['class'=>'uk-input','disabled','id'=>'marg'.$material->reference])!!}
							 </div>
						 </div>

						 <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
							 <div>
									 <label uk-tooltip="">Migration</label>
							 </div>
							 <div id="">
								 <label>Qte{!!Form::text('quantite_migration',$migration,['class'=>'uk-input uk-text-center qte','placeholder'=>'Quantite','id'=>'qte-migration','disabled'])!!}</label>
							 </div>
						 </div>
						 <div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
							 <div>
									 <label uk-tooltip="">Paraboles dûes</label>
							 </div>
							 <div id="">
								 <label>Qte{!!Form::text('quantite','N/A',['class'=>'uk-input uk-text-center qte','placeholder'=>'Quantite','id'=>'qte-du','disabled'])!!}</label>
							 </div>
						 </div>

						 <hr class="uk-divider-small">
						 <div class="uk-grid-collapse" uk-grid>
							 <div class="uk-width-5-6@m">
								 <span>TOTAL TTC-MATERIEL (GNF)</span>
							 </div>
							 <div class="uk-width-1-6@m">
								 {!!Form::text('',0,['class'=>'uk-input','disabled','id'=>'total-ttc'])!!}
							 </div>
						 </div>
						 <div class="uk-grid-collapse" uk-grid>
							 <div class="uk-width-5-6@m">
								 <span>TOTAL SUBVENTION (GNF)</span>
							 </div>
							 <div class="uk-width-1-6@m">
								 {!!Form::text('',0,['class'=>'uk-input','disabled','id'=>'subv-topay'])!!}
							 </div>
						 </div>
						 <div class="uk-grid-collapse uk-text-lead" uk-grid>
							 <div class="uk-width-5-6@m">
								 <span>TOTAL A PAYER (GNF)</span>
							 </div>
							 <div class="uk-width-1-6@m">
								 {!!Form::text('total',0,['class'=>'uk-input','disabled','id'=>'total-topay'])!!}
								 {!!Form::hidden('prix_achat','',['id'=>'prix-achat'])!!}
							 </div>
						 </div>
						 <hr class="uk-divider-small">
						 <button type="submit" class="uk-button uk-button-primary uk-box-shadow-small uk-border-rounded uk-box-shadow-small">valider<span uk-icon="icon:check;ratio:.8"></span></button>
							 {!!Form::close()!!}
							 @endif
					 </li>
					 @endif
			     <li>
						 <!-- COMMANDE cga -->
						 {!!Form::open(['url'=>'user/new-command/cga','class'=>'uk-width-1-2@m'])!!}
						 {!!Form::label('Montant')!!}
						 {!!Form::number('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Montant Credit'])!!}
						 {!!Form::submit('validez',['class'=>'uk-button-primary uk-button uk-button-small uk-border-rounded uk-box-shadow-small'])!!}
						 {!!Form::close()!!}
					 </li>
					 <li>
						 <!-- COMMANDE REX -->
						 {!!Form::open(['url'=>'user/new-command/rex','class'=>'uk-width-1-2@m'])!!}
						 {!!Form::label('Montant')!!}
						 {!!Form::number('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Montant Credit'])!!}
						 {!!Form::submit('validez',['class'=>'uk-button-primary uk-button-small uk-button uk-border-rounded uk-box-shadow-small'])!!}
						 {!!Form::close()!!}
					 </li>
					 @if(Auth::user()->type == 'v_standart')
					 <li>
						 <!-- COMMANDE AFROCASH SEMI GROSSISTE -->
						 {!!Form::open(['url'=>'/user/new-command/afrocash-sg','files'=>true])!!}
						 <div class="">
				        <div class="uk-inline uk-width-1-3@m">
				            <span class="uk-form-icon" uk-icon="credit-card"></span>
				            {!!Form::number("montant",'',['class'=>'uk-input uk-border-rounded','placeholder'=>'Montant'])!!}
				        </div>
								<div class="uk-inline uk-width-1-3@m">
									<span class="uk-form-icon" uk-icon="check"></span>
									{!!Form::text("numero_recu",'',['class'=>'uk-input uk-border-rounded','placeholder'=>'Numero Recu'])!!}
								</div>
								<div class="uk-margin">
										<div uk-form-custom>
											{!!Form::file("piece_jointe")!!}
					             <button class="uk-button-default uk-padding-small uk-border-circle" type="button" tabindex="-1"><span uk-icon="image"></span></button>
					         </div>
								</div>
								{!!Form::submit("Envoyer",['class'=>'uk-button-primary uk-button uk-button-small uk-border-rounded uk-box-shadow-small'])!!}
				    </div>
						 {!!Form::close()!!}
					 </li>
					 @endif
			 </ul>
	</div>
</div>
@endsection
@section('script')
<script type="text/javascript">
	$(function() {

		// ===
		$(".qte").on('keyup focus blur change',function () {
			if(parseInt($(this).val()) < 0 || $(this).val() == '') {
				$(this).val(0);
			}

		var ttt = 0;
		var tttt =0;
		var totalTtc = 0;
		var totalSub =0;
		var actif = $(this).parent().parent().attr('id');
		var montant = $("#montant-"+actif);
		var ttc = parseInt($("#ttc-"+actif).val());
		var qte = parseInt($(this).val());
		var mont = ttc * qte;
		var sub = $("#sub-"+actif).val();
		var totSub = qte * sub;
		var totalTopay = parseInt($("#totaltopay-"+actif).val());
		// console.log($("#qte-infos-"+actif));
		$("#qte-infos-"+actif).val($(this).val());
		// $("#compense").
		$("#qte-du").val(($(this).val() - ($("#qte-migration").val() - $("#compense").val())));
		//
		if($("#qte-du").val() < 0) {
			$("#qte-infos-parabole").val(0);
		} else {
			$("#qte-infos-parabole").val($("#qte-du").val());
		}
		//
		// totalTopay.val((parseInt($("#prix-normal-"+actif).val()) - parseInt($("#margeht-"+actif).val())) * qte);

		// console.log(totalTopay);
		// ==
		// montant.val(mont);
		montant.val(mont);
		$("#tsub-"+actif).val(totSub);

		// calcul montant ttc
		$(".montant-ttc").each(function(index,element) {
			// console.log(index);
			totalTtc = parseInt(totalTtc) + parseInt(element.value);
		});

		$(".tsubv").each(function(index,element) {
			// console.log(index);
			totalSub = parseInt(totalSub) + parseInt(element.value);
		});
		// console.log(totalTtc);
		$("#total-ttc").val(lisibilite_nombre(totalTtc));
		$("#subv-topay").val(lisibilite_nombre(totalSub));
		// =====
		ttt = totalTopay * qte;

		$("#somm-"+actif).val(ttt);
		// $("#total-topay").val(lisibilite_nombre(ttt));

		$(".all-somme").each(function (index,element) {
			// total a payer
			tttt = tttt + parseInt(element.value);
		});
		$("#total-topay").val(lisibilite_nombre(tttt));
		$("#prix-achat").val(tttt)
	});
		//


});

</script>
@endsection

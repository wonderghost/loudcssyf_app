@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user')}}" uk-tooltip="Dashboard" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Nouvel Commande</h3>
			 <hr class="uk-divider-small">
			 <div uk-spinner style="display: none;" id="loader"></div>
			 @if(session('success'))
			 <div class="uk-alert-success" uk-alert>
				 <a href="#" class="uk-alert-close" uk-close></a>
			 	<p>{{session('success')}}</p>
			 </div>
			 @endif
			 @if($errors->has('quantite') || $errors->has('numero_versement') || $errors->has('recu') || $errors->has('mat-reference'))
			 <div class="uk-alert uk-alert-danger">
			 	<div>{{$errors->first('quantite')}}</div>
			 	<div>{{$errors->first('numero_versement')}}</div>
			 	<div>{{$errors->first('recu')}}</div>
			 	<div>{{$errors->first('mat-reference')}}</div>
			 </div>
			 @endif
			<ul uk-accordion="collapsible:false">
			    <li>
			        <a class="uk-accordion-title" href="#">Materiels</a>
			        <div class="uk-accordion-content">
			        	{!!Form::open(['url'=>'/user/new-command/material','id'=>'command-form','files' => true])!!}

			        	{!!Form::hidden('compense',$compense,['id'=>'compense'])!!}

			        	@if($material)
								
			        	<div class="uk-margin-small uk-grid-collapse uk-child-width-1-6@m"  uk-grid>
			        		<div>
					            <label uk-tooltip="{{$material->libelle}}">Kit Complet</label>
			        		</div>
			        		<div id="{{$material->reference}}">
			        			<label>Qte{!!Form::number('quantite',0,['class'=>'uk-input uk-text-center  qte','placeholder'=>'Quantite','id'=>'qte-'.$material->reference])!!}</label>
			        			{!!Form::hidden('mat-reference',$material->reference)!!}
			        			{!!Form::hidden('',$material->prix_vente,['id'=>'prix-normal-'.$material->reference])!!}
			        			{!!Form::hidden('',($material->prix_vente-($material->marge/1.18)),['class'=>'totaltopay','id'=>'totaltopay-'.$material->reference])!!}
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
			        	<div class="uk-grid-collapse" uk-grid>
			        		<div class="uk-width-5-6@m">
			        			<span>RECU DE VERSEMENT (No)</span>
			        		</div>
			        		<div class="uk-width-1-6@m">
			        			{!!Form::text('numero_versement','',['class'=>'uk-input','id'=>'numero-versement','placeholder'=>'XXX'])!!}
			        		</div>
			        	</div>
			        	<div class="uk-grid-collapse" uk-grid>
			        		<div class="uk-width-5-6@m">
			        			<span>PIECE JOINTE</span>
			        		</div>
			        		<div class="uk-width-1-6@m">
			        			{!!Form::file('recu')!!}
			        		</div>
			        	</div>
			        	<div class="uk-grid-collapse uk-text-lead" uk-grid>
			        		<div class="uk-width-5-6@m">
			        			<span>TOTAL A PAYER (GNF)</span>
			        		</div>
			        		<div class="uk-width-1-6@m">
			        			{!!Form::text('total',0,['class'=>'uk-input','disabled','id'=>'total-topay'])!!}
			        		</div>
			        	</div>
			        	<hr class="uk-divider-small">
			        	<button type="" class="uk-button-default uk-border-rounded">valider<span uk-icon="icon:check;ratio:.8"></span></button>
			            @endif
			            {!!Form::close()!!}
			        </div>
			    </li>
			    <li>
			        <a class="uk-accordion-title" href="#">Credit Point de Vente</a>
			        <div class="uk-accordion-content">
			            {!!Form::open(['user/new-command/cga'])!!}
			            {!!Form::text('montant','',['class'=>'uk-input uk-margin-small','placeholder'=>'Montant Credit'])!!}
			            <button type="submit" class="uk-button-default uk-border-rounded">valider<span uk-icon="icon:check;ratio:.8"></span></button>
			            {!!Form::close()!!}
			        </div>
			    </li>
			</ul>
	</div>
</div>
@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		// ENVOI DE LA COMMAND AJAX
		// $("#command-form").on('submit',function(e) {
		// 	$("#loader").show(500);
		// 	e.preventDefault();

		// 	$.ajax({
		// 		url : $(this).attr('action'),
		// 		type  : $(this).attr('method'),
		// 		data : $(this).serialize(),
		// 		dataType : 'json'
		// 	})
		// 	.done(function (data) {
		// 		if(data) {
		// 			$("#loader").hide(500);
		// 			UIkit.modal.alert("<div class='uk-alert uk-alert-success'>Commande envoyée !</div>");
		// 		}
		// 	})
		// 	.fail(function (data) {
		// 		$("#loader").hide(500);
		// 		if(data) {
		// 			if(data.responseJSON.errors.quantite && data.responseJSON.errors.numero_versement && data.responseJSON.errors.recu) {
		// 				UIkit.modal.alert("<div class='uk-alert uk-alert-danger'>"+data.responseJSON.errors.numero_versement[0] +'<br>'+ data.responseJSON.errors.quantite[0]+'<br>'+data.responseJSON.errors.recu[0]+"</div>");
		// 			} else if (data.responseJSON.errors.numero_versement) {
		// 				UIkit.modal.alert(data.responseJSON.errors.numero_versement[0]);
		// 			}
		// 			else if (data.responseJSON.errors.quantite){
		// 				UIkit.modal.alert(data.responseJSON.errors.quantite[0]);
		// 			}
		// 			else if(data.responseJSON.errors.recu) {
		// 				UIkit.modal.alert(data.responseJSON.errors.recu[0]);
		// 			}
		// 			else {
		// 				UIkit.modal.alert('Erreur de transmission!');
		// 			}
		// 		}
		// 	});
		// });
		// $("#command-form").submit();
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
	});
		//


});

</script>
@endsection

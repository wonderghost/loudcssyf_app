@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3>
			<a href="{{url('/user/list-command')}}" uk-tooltip="Toutes les commandes" uk-icon="icon:arrow-left;ratio:1.5"></a>
			 Details de la commande</h3>
			 <hr class="uk-divider-small">
			 <input type="hidden" id="id-command" value="{{$id}}">
			 <ul class="uk-list">
			    <li><span>Materiel : </span><span class="uk-text-bold" id="materiel"></span></li>
			    <li><span>Quantite : </span><span class="uk-text-bold" id="quantite"></span></li>
			    <li><span>Numero du recu : </span><span class="uk-text-bold" id="numero-recu"></span></li>
			    <li>
			    	<span>Recu : </span>
		    		<img id="img-recu" src="" class="uk-width-small" uk-img>
			    	<div uk-lightbox>
					    <a class="uk-button-default uk-border-rounded" id="recu" href="" data-caption="Piece jointe">cliquez pour agrandir</a>
					</div>
			    </li>
			    <li><span>Parabole dû : </span><span id="parabole-du"></span></li>
			</ul>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
	$(function (){
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}",$("#id-command").val());
		form.on('submit',function (e) {
			e.preventDefault();

			$.ajax({
				url  : $(this).attr('action'),
				type : $(this).attr('method'),
				data   : $(this).serialize(),
				dataType : 'json'
			})
			.done(function (data) {
				if(data) {
					$("#materiel").html(data.material);
					$("#quantite").html(data.quantite);
					$("#numero-recu").html(data.numero_recu);
					$("#img-recu").attr('src',"{{asset('uploads')}}"+'/'+data.recu);
					$("#recu").attr('href',"{{asset('uploads')}}"+'/'+data.recu);
					$("#parabole-du").html(data.parabole_du);
				}
			})
			.fail(function (data) {
				console.log(data);
			});
		});

		form.submit();

	});
</script>
@endsection

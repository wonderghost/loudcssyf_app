@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Solde Vendeur</h3>
		<hr class="uk-divider-small">

		<!-- LIST DDES SOLDES VENDEURS -->

		<div class="uk-child-width-1-2@m" uk-grid>
			<div class="">
				<div class="uk-h3 uk-heading-divider">
					COMPTE CGA
				</div>
				<div id="solde-cga"></div>
			</div>
			<div class="">
				<div class="uk-h3 uk-heading-divider">
					COMPTE REX
				</div>
				<div class="uk-grid-small" uk-grid>
				    <div class="uk-width-expand" uk-leader="fill: -">Lorem ipsum dolor sit amet</div>
				    <div>$20.90</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}","");
		form.on('submit',function (e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				dataType : 'json',
				data : $(this).serialize()
			})
			.done(function (data) {
				if(data) {
					$("#solde-cga").html("");
					$(data).each(function (index,element) {
						var parentDiv = $("<div></div>") , firstChildDiv = $("<div></div>") , secondChildDiv = $("<div></div>");
						parentDiv.addClass('uk-grid-small');
						parentDiv.attr('uk-grid','');


						firstChildDiv.addClass('uk-width-expand');
						firstChildDiv.attr('uk-leader','fill: -');
						firstChildDiv.html(element.id_vendeur + " - " + element.agence);
						secondChildDiv.html("GNF "+lisibilite_nombre(element.solde));
						secondChildDiv.addClass('uk-text-bold');

						parentDiv.append(firstChildDiv);
						parentDiv.append(secondChildDiv);

						$("#solde-cga").append(parentDiv);
					});
				}
			})
			.fail(function (data) {
				Uikit.modal.alert(data).then(function () {
					$(location).attr('href',"{{url()->current()}}");
				})
			});
		});

		form.submit();
		setInterval(function () {
			form.submit();
		}, 5000);
	});
</script>
@endsection

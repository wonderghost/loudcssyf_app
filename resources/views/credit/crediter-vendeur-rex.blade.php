@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( REX )</h3>
		<hr class="uk-divider-small">
		@if(session('success')) 
		<div class="uk-alert uk-alert-success">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		@if(session('_errors')) 
		<div class="uk-alert uk-alert-danger">
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('_errors')}}</div>
		</div>
		@endif
		<div class="uk-child-width-1-2@m uk-grid-divider " uk-grid>
			<div>
				<h4>...</h4>
				
				<div class="uk-grid-small uk-text-lead" uk-grid>
				    <div class="uk-width-expand uk-text-capitalize" uk-leader>SOLDE REX (GNF)</div>
				    <div>{{number_format($solde)}}</div>
				</div>
				
			</div>
			<div id="">
				<h4>Crediter un compte</h4>
				@if($errors->has('compte') || $errors->has('montant') || $errors->has('vendeur')) 
				<div class="uk-alert uk-alert-danger">
					<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
					<div>{{$errors->first('vendeur')}}</div>
					<div>{{$errors->first('compte')}}</div>
					<div>{{$errors->first('montant')}}</div>
				</div>
				@endif
				{!!Form::open(['url'=>'/user/send-rex'])!!}
				<label>	
					{!!Form::radio('compte','rex',true,['class'=>'uk-radio'])!!} REX
				</label>
				{!!Form::text('vendeur','',['class'=>'uk-input uk-margin-small','placeholder'=>'Vendeur','id'=>'tag'])!!}
				{!!Form::text('montant','',['class'=>'uk-input uk-margin-small','placeholder'=>'Montant'])!!}
				<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
				{!!Form::close()!!}
			</div>
		</div>		
	</div>	
</div>

@endsection
@section('script')
<script type="text/javascript">
	$(function() {
		$(".close-button").on('click',function () {
			$(this).parent().hide(500);
		});
$("#tag").on('keyup',function () {
	var form = $adminPage.makeForm("{{csrf_token()}}","{{url()->current()}}",'');

		form.on('submit',function(e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action'),
				type : $(this).attr('method'),
				dataType : 'json',
				data : $(this).serialize()
			})
			.done(function(data) {
				if(data) {
					var tabUsers = [];
					$(data).each(function (index,element) {
						tabUsers.push(element.username);
					})
					$( "#tag" ).autocomplete({
				      source: tabUsers
				    });
				}
			})
			.fail(function(data) {
				console.log(data);
			});
		})
		form.submit();
});
		
	});
</script>
@endsection
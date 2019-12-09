@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Compte Credit ( CGA / REX / AFROCASH)</h3>
		<hr class="uk-divider-small">
		@if(session('success'))
		<div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
			<button type="button" class="uk-align-right close-button"  uk-icon="icon:close"></button>
			<div>{{session('success')}}</div>
		</div>
		@endif
		<div class="uk-child-width-1-2@m uk-grid-divider " uk-grid>
			<div>
				<h4>...</h4>
				@foreach($credit as $values)
				<div class="uk-grid-small uk-text-lead" uk-grid>
				    <div class="uk-width-expand uk-text-capitalize" uk-leader>SOLDE {{$values->designation}} (GNF)</div>
				    <div>{{number_format($values->solde)}}</div>
				</div>
				@endforeach
			</div>
			<div id="">
				<h4>Crediter les comptes</h4>
				@if($errors->any())
				@foreach($errors->all() as $error)
				<div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
					<a href="#" class="uk-alert-close" uk-close></a>
					<p>{{$error}}</p>
				</div>
				@endforeach
				@endif
				{!!Form::open()!!}
				<label>
					{!!Form::radio('compte','cga',true,['class'=>'uk-radio'])!!} CGA
				</label>
				<label>
					{!!Form::radio('compte','rex','',['class'=>'uk-radio'])!!} REX
				</label>
				<label>
					{!!Form::radio('compte','afrocash','',['class'=>'uk-radio'])!!} AFROCASH
				</label>
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
		})
	});
</script>
@endsection

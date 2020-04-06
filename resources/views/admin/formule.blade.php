@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Formules</h3>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
			<div>
				<h3>Nouvelle Formule</h3>

				@if($errors->any())
				@foreach($errors->all() as $error)
				<div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
					<a href="#" class="uk-alert-close" uk-close></a>
					<p>{{$error}}</p>
				</div>
				@endforeach
				@endif

				@if(session('success'))
				<div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
					<a href="#" class="uk-alert-close" uk-close></a>
					<p>{{session('success')}}</p>
				</div>
				@endif
				{!!Form::open()!!}
				{!!Form::text('nom','',['class'=>'uk-input uk-margin-small','placeholder'=>'Nom de la formule'])!!}
				{!!Form::text('prix','',['class'=>'uk-input uk-margin-small','placeholder'=>'Prix'])!!}
				<button class="uk-button-primary uk-button uk-button-small uk-box-shadow-small uk-border-rounded" type="submit">valider <span uk-icon="icon:check;ratio:.8"></span></button>
				{!!Form::close()!!}
			</div>
			<div>
				<h3>Toutes les formules</h3>
				@if($formule)
				@foreach($formule as $values)
				<div class="uk-grid-small" uk-grid>
				    <div class="uk-width-expand" uk-leader="fill: -"><button type="button" class="uk-button-default uk-border-rounded"><span uk-icon="icon:pencil;ratio:.8"></span> edit</button> {{$values->nom}}</div>
				    <div>{{number_format($values->prix)}}</div>
				</div>
				@endforeach
				@endif
			</div>
		</div>
		<hr class="uk-divider-small">
		<div class="uk-grid-collapse uk-grid-divider uk-child-width-1-2@m" uk-grid>
			<div>
				<h3>Nouvel Option</h3>
				{!!Form::open(['url'=>'/admin/add-option'])!!}
				{!!Form::text('nom','',['class'=>'uk-input uk-margin-small','placeholder'=>"Nom de l'option"])!!}
				{!!Form::text('prix','',['class'=>'uk-input uk-margin-small', 'placeholder'=>"Prix de l'option"])!!}
				<button type="submit" class="uk-button-primary uk-button uk-button-small uk-box-shadow-small uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
				{!!Form::close()!!}
			</div>
			<div>
				<h3>Toutes les options</h3>
				@if($options)
				@foreach($options as $values)
				<div class="uk-grid-small" uk-grid>
				    <div class="uk-width-expand" uk-leader="fill: -"><button type="button" class="uk-button-default uk-border-rounded"><span uk-icon="icon:pencil;ratio:.8"></span> edit</button> {{$values->nom}}</div>
				    <div>{{number_format($values->prix)}}</div>
				</div>
				@endforeach
				@endif
			</div>
		</div>
	</div>
</div>
@endsection

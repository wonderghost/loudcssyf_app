@extends('layouts.app_users')

@section('content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url()->previous()}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Ravitailler un Depot</h3>
		<hr class="uk-divider-small">

    <!-- FORMULAIRE DE RAVITAILLEMENT -->
    @if($errors->any())
    @foreach($errors->all() as $key =>  $error)
    <div class="uk-alert-danger" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{ $error  }}</p>
    </div>
    @endforeach
    @endif
		{!!Form::open(['url'=> url()->current()])!!}

		<!-- SELECT MATERIAL -->
		{!!Form::label('Materiel*')!!}
		<select name="produit" id="produit" class="uk-select uk-margin-small">
			<option value="">--Materiel--</option>
			@if($materiel)
			@foreach($materiel as $values)
			<option value="{{$values->reference}}">{{$values->libelle}}</option>
			@endforeach
			@endif
		</select>
		<!-- SELECT DEPOT -->
		{!!Form::label('Depot*')!!}
		<select name="depot" id="depot" class="uk-select">
      @if($depots)
      @foreach($depots as $key =>  $value)
      <option value="{{$value->localisation}}">{{$value->localisation}}</option>
      @endforeach
      @endif
		</select>
		{!!Form::label('Quantite*')!!}
		{!!Form::number('quantite','',['class'=>'uk-input uk-margin-small','placeholder'=>'Quantite'])!!}
		<button type="submit" class="uk-button-default uk-border-rounded">valider <span uk-icon="icon:check;ratio:.8"></span></button>
		{!!Form::close()!!}
  </div>
</div>
@endsection

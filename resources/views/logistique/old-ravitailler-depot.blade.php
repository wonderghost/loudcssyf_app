@extends('layouts.app_users')

@section('content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url()->previous()}}" uk-tooltip="Nouveau materiel" uk-icon="icon:arrow-left;ratio:1.5"></a> Ravitailler un Depot</h3>
		<hr class="uk-divider-small">
    @if(session('_errors'))
    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('_errors')}}</p>
    </div>
    @endif
    <!-- FORMULAIRE DE RAVITAILLEMENT -->
    @if($errors->any())
    @foreach($errors->all() as $key =>  $error)
    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{ $error  }}</p>
    </div>
    @endforeach
    @endif
		{!!Form::open(['url'=> url()->current()])!!}

		<!-- SELECT MATERIAL -->
    <div class="uk-grid" uk-grid>
      <div class="uk-width-2-3@m">
        {!!Form::label('Materiel*')!!}
    		<select name="produit" id="produit" class="uk-select uk-margin-small">
    			<option value="">--Materiel--</option>
          <!-- <option value="kit_complet">KIT COMPLET</option> -->
    			@if($materiel)
    			@foreach($materiel as $values)
    			<option value="{{$values->reference}}">{{$values->libelle}}</option>
    			@endforeach
    			@endif
    		</select>
    		<!-- SELECT DEPOT -->
      </div>
      <div class="uk-width-1-3@m">
        {!!Form::label('Disponible')!!}
        {!!Form::text('quantite_disponible',0,['class'=>'uk-input uk-margin-small','disabled'=>'','id'=>'qte_dispo'])!!}
      </div>
    </div>

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
@section('script')
<script type="text/javascript">
  $(function () {
    //
    $("#produit").on('change',function () {
      var material = $(this)
      var form = $adminPage.makeForm("{{csrf_token()}}","{{url('user/ravitailler-depot/get-mat-dispo')}}",material.val())

      form.on('submit',function (e) {
        e.preventDefault()
        $.ajax({
          url : $(this).attr('action'),
          type : 'post',
          data : $(this).serialize(),
          dataType : 'json'
        }).done(function (data) {
          if(data && data !== 'fail') {
            $("#qte_dispo").val(data.quantite_disponible)
          } else {
            UIkit.modal.alert(data).then(function () {
              $(location).attr('href',"{{url()->current()}}")
            })
          }
        })
        .fail(function (data) {
          UIkit.modal.alert(data).then(function () {
            $(location).attr('href',"{{url()->current()}}")
          })
        })
      })
      form.submit()
    })

  })
</script>
@endsection

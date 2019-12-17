@extends('layouts.app_users')

@section('user_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Operations Afrocash</h3>
    <hr class="uk-divider-small">
    @if(session('success'))
    <div class="uk-alert-success uk-border-rounded uk-box-shadow-small" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('success')}}</p>
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
    @if(session("_error"))
    <div class="uk-alert-danger uk-border-rounded uk-box-shadow-small" uk-alert>
      <a href="#" class="uk-alert-close" uk-close></a>
      <p>{{session('_error')}}</p>
    </div>
    @endif
    <ul uk-tab>
      @if(Auth::user()->type == 'v_standart')
        <li><a href="#"><span uk-icon="icon : arrow-down"></span> Depots</a></li>
        @endif
        <li><a href="#"><span uk-icon="icon : shrink"></span> Transfert Courant</a></li>
        <li><a href="#"><span uk-icon="icon : arrow-up"></span> Retrait</a></li>
    </ul>

    <ul class="uk-switcher uk-margin">
      @if(Auth::user()->type == 'v_standart')
        <li>
          <!-- DEPOTS -->
          <h3>Effectuez un depot</h3>
          {!!Form::open(['url'=>'/user/afrocash/transaction','class'=>'uk-width-1-2@m'])!!}
          {!!Form::hidden('type_operation','depot')!!}
          {!!Form::label('Vendeur')!!}
          <select class="uk-select uk-margin-small uk-border-rounded" name="numero_compte_courant">
            @if($comptes)
            @foreach($comptes as $value)
            <option value="{{$value->numero_compte}}">{{$value->vendeurs}}( {{$value->vendeurs()->localisation}} ) (compte No {{number_format($value->numero_compte,0,' '," ")}} )</option>
            @endforeach
            @endif
          </select>
          {!!Form::label('Montant')!!}
          {!!Form::number('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Montant de la transaction'])!!}
          {!!Form::label('Confirmez le mot de passe')!!}
          {!!Form::password('password',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Confirmez votre mot de passe'])!!}
          {!!Form::submit('validez',['class'=>'uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
          {!!Form::close()!!}
          <!-- // -->
        </li>
        @endif
        <li>
          <h3>Effectuez une transaction</h3>
          <!-- TRANSACTION COURANT -->
          {!!Form::open(['url'=>'/user/afrocash/transaction','class'=>'uk-width-1-2@m'])!!}
          {!!Form::hidden('type_operation','transfert_courant')!!}
          {!!Form::label('Vendeur')!!}

          @if(Auth::user()->type == 'v_standart')
          <select class="uk-select uk-margin-small" name="vendeurs">
            @if($comptes)
            @foreach($comptes as $value)
            <option value="{{$value->vendeurs}}">{{$value->vendeurs}}( {{$value->vendeurs()->localisation}} ) (compte No {{number_format($value->numero_compte,0,' '," ")}} )</option>
            @endforeach
            @endif
          </select>
          @elseif(Auth::user()->type == 'v_da')
          {!!Form::text('vendeurs','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>"Nom d'utilisateur (ex : LS-XXXX , DA-XXXX)"])!!}
          @endif

          {!!Form::label('Montant')!!}
          {!!Form::number('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Montant de la transaction'])!!}
          {!!Form::label('Confirmez le mot de passe')!!}
          {!!Form::password('password',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Confirmez votre mot de passe'])!!}
          {!!Form::submit('validez',['class'=>'uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
          {!!Form::close()!!}
          <!-- // -->
        </li>
        <li>
          <h3>Effectuez un retrait</h3>
        </li>
    </ul>


  </div>
</div>
@endsection
@section("script")
<script type="text/javascript">
  $(function () {

  })
</script>
@endsection

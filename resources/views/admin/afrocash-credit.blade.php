@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
  <div class="uk-container">
    <h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Afrocash Central</h3>
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
    <ul uk-tab>
        <li><a href="#">Apport</a></li>
        <li><a href="#">Depenses</a></li>
    </ul>

    <ul class="uk-switcher uk-margin">
        <li>
          <!-- AUGMENTATION CAPITAL / APPORT -->
          {!!Form::open(['url'=>'/admin/afrocash/apport','class'=>'uk-width-1-2@m','id'=>'apport-form'])!!}
          <div class="">
            {!!Form::label('Montant')!!}
            {!!Form::text('montant','',['class'=>'uk-input uk-margin-small uk-border-rounded','required'=>''])!!}
          </div>
          {!!Form::submit('validez',['class'=>'uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
          {!!Form::close()!!}
        </li>
        <li>
          <!-- DEPENSES -->
        </li>
    </ul>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  $(function () {

  })
</script>
@endsection

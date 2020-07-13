@extends('layouts.app_users')
@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container uk-container-large">
    <!-- <h3>Tableau de bord</h3> -->
    <!-- <hr class="uk-divider-small"> -->

    @if(Auth::user()->type == 'v_da' || Auth::user()->type == 'v_standart')
    <ul class="dash-li">
      <li id="visu-objectif"><a class="uk-button uk-button-default uk-border-rounded uk-button-small">
        <i class="material-icons uk-float-left">track_changes</i> Visu Objectif
      </a></li>
      <li id="perf-vente"><a class="uk-button uk-button-default uk-border-rounded uk-button-small">
        <i class="material-icons uk-float-left">timeline</i> Performance Vente
      </a></li>
    </ul>
    <div id="content-visu-objectif" class="content-all">
      <objectif-user></objectif-user>
    </div>
    <div id="content-perf-vente" class='content-all'>
      <dashboard the-user="{{Auth::user()->type}}"></dashboard>
    </div>
    @endif
    

  </div>
</div>
@endsection('content')
@section('script')
<script>
  $(document).ready(function () {
    $('.content-all').hide()
    $("#content-visu-objectif").show()
    let btns = $("ul.dash-li li");
    btns.on('click',function (e) {
      if(this.id == 'visu-objectif') {
        // show vis objectif content
          $('.content-all').hide()
          $("#content-visu-objectif").show(300)
      } else if(this.id == 'perf-vente') {
          // show performance vente
          $(".content-all").hide()
          $("#content-perf-vente").show(300)

      }
    })  
  })
</script>
@endsection
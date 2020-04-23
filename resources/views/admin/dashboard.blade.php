@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container uk-container-large">
    <ul class="dash-li">
      <li class="uk-button uk-button-small uk-border-rounded uk-button-default" id="btn-dash">
        <a>
          <i class="material-icons uk-float-left">dashboard</i> <span class="uk-visible@s">Tableau de bord</span>
        </a>
      </li>
      <li class="uk-button uk-button-small uk-border-rounded uk-button-default" id="btn-perf">
        <a>
          <i class="material-icons uk-float-left">timeline</i> <span class="uk-visible@s">Performances</span>
        </a>
      </li>
      <li class="uk-button uk-button-small uk-border-rounded uk-button-default" id="btn-obj">
        <a>
          <i class="material-icons uk-float-left">track_changes</i> <span class="uk-visible@s">Objectifs</span>
        </a>
      </li>
    </ul>

    <div class="uk-width-1-1@m">
        <div id="dash" class="content-all">
          <dashboard the-user='admin'></dashboard>
        </div>
        <div id="perf" class="content-all">
          <!-- PERFORMANCES ET OBJECTIFS -->
          <perform-objectif></perform-objectif>
          <!-- // -->
        </div>
        <div id="obj" class="content-all">
          <h2>Objectifs</h2>
          <ul uk-tab>
              <li><a href="#">Apercue</a></li>
              <li><a href="#">Nouvel Objectif</a></li>
              <li><a href="#">Tous les objectifs</a></li>
          </ul>

          <ul class="uk-switcher uk-margin">
              <li></li>
              <li>
              <!-- NEW OBJECTIF -->
               <!-- <div class="uk-alert-info uk-width-1-3@m" uk-alert>
                <p><span uk-icon="icon : info"></span> Ajoutez un nouvel objectif !</p>
               </div> -->
              <!-- // -->
              <div class="uk-container">
                <objectif-component></objectif-component>
              </div>
              </li>
              <li></li>
          </ul>          
        </div>
    </div>
  </div>
</div>
@endsection
@section("script")
<script>
$(document).ready(function () {
  $('.content-all').hide()
  $('#dash').show(300)
  let btns = $("ul.dash-li > li")
  // console.log(btns)
  btns.on('click',function(e) {
    $('.content-all').hide(300)
    if(this.id == 'btn-dash') {
      $('#dash').show(300)
    } 
    else if(this.id == 'btn-perf') {
      $('#perf').show(300)
    }
    else {
      $('#obj').show(300)
    }
  })
})
</script>
@endsection
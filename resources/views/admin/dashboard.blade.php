@extends('layouts.app_admin')

@section('content')
<div class="uk-section uk-section-default uk-padding-small uk-margin-remove">
  <div class="uk-container uk-container-large">
    <ul uk-accordion>
      <li class="uk-open">
          <a class="uk-accordion-title" href="#"><i class="material-icons uk-float-left">timeline</i> Performances & Objectifs</a>
          <div class="uk-accordion-content">
              <!-- PERFORMANCES ET OBJECTIFS -->
              <perform-objectif></perform-objectif>
              <!-- // -->
          </div>
      </li>
      <li class="uk-open">
          <a class="uk-accordion-title" href="#"><i class="material-icons uk-float-left">dashboard</i> Tableau de bord</a>
          <div class="uk-accordion-content">
            <dashboard the-user='admin'></dashboard>          
          </div>
      </li>
  </ul>
  </div>
</div>

@endsection

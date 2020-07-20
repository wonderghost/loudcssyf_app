@extends('layouts.app_admin')

@section('admin_content')
<div class="uk-section uk-section-default">
	<div class="uk-container">
		<h3><a href="{{url('/admin')}}" uk-tooltip="tableau de bord" uk-icon="icon:arrow-left;ratio:1.5"></a> Nouvel Utilisateur</h3>

		<div class="uk-alert-info uk-border-rounded uk-box-shadow-small" uk-alert>
          <a href="#" class="uk-alert-close" uk-close></a>
          <p> <span uk-icon="icon : warning"> </span> (*) Champs obligatoires !</p>
        </div>

		<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium, uk-animation-slide-right-medium">
			<li><a class="uk-button uk-button-small uk-button-primary uk-border-rounded" href="#">Utilisateur</a></li>
		</ul>

		<ul class="uk-switcher uk-margin">
			<li>
				<add-user-component></add-user-component>
			</li>
		</ul>		
	</div>
</div>

@endsection

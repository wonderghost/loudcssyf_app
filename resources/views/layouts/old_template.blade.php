<!DOCTYPE html>
<!-- <html onselectstart="return false" oncontextmenu="return false" ondragstart="return false" onMouseOver="window.status=''; return true;"> -->
<html>
<head>

	<title>{{config('app.name')}}-@yield('title')</title>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="csrf-token" content="{{csrf_token()}}">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<!-- UIkit CSS -->
		<link rel="stylesheet" href="{{asset('css/uikit.min.css')}}">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.css" integrity="sha256-IvM9nJf/b5l2RoebiFno92E5ONttVyaEEsdemDC6iQA=" crossorigin="anonymous" />
		<link rel="stylesheet" href="{{asset('css/style.css')}}">
		<script type="text/javascript">
		function noBack(){window.history.forward()}
		noBack();
		window.onload=noBack;
		window.onpageshow=function(evt){if(evt.persisted)noBack()}
		window.onunload=function(){void(0)}
		</script>
</head>
<body>
	<input type="hidden" id="user-type" value="{{Auth::user()->type}}">
	<!-- OPTIONS POUR L'AFFICHAGE RESPONSIVE (NOTIFICATION , MESSAGE , TABLEAU DE BORD) -->
	<div class="uk-position-fixed uk-position-bottom uk-position-z-index uk-background-muted uk-padding-small uk-box-shadow uk-hidden@m" id="outils">
		<div class="uk-child-1-3@s uk-flex uk-flex-center" uk-grid>
			<div class="">
				<a href="{{url('/')}}" class=""> <i class="material-icons">home</i> </a>
			</div>
			<div class="">
				<a href="#all-notification" uk-toggle class=""> <i class="material-icons">notifications</i> <sup id="notification-count-responsive" class="">0</sup></a>
			</div>
			<div class="">
				<a href="#messages-chat-box" id="chat-button-responsive" class="chat-buttons" uk-toggle class=""> <i class="material-icons">message</i> </a>
			</div>
		</div>
	</div>


	<!-- // -->
	<!-- loader -->
	<div id="loader">
		<div class="uk-border-rounded uk-dark uk-width-1-2@s uk-width-1-3@m uk-align-center uk-background-default uk-margin-xlarge-top uk-padding uk-visible@m uk-flex uk-flex-middle uk-flex-center" style="margin-top : 20% !important;">
			<div class="uk-margin-right" uk-spinner></div>  Patientez svp ...
		</div>
		<div class="uk-border-rounded uk-dark uk-width-1-2@s uk-width-1-3@m uk-align-center uk-background-default uk-margin-xlarge-top uk-padding uk-hidden@m uk-flex uk-flex-middle uk-flex-center" style="margin-top : 50% !important;">
			<div class="uk-margin-right" uk-spinner></div>  Patientez svp ...
		</div>
	</div>
	<!-- // -->
	<!-- MODAL PROMO -->
	<div id="modal-promo" class="uk-flex-top" uk-modal>
    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
      <button class="uk-modal-close-default" type="button" uk-close></button>
			<h3 class="">Parametre Promo</h3>
			<hr class="uk-divider-small">

				<div id="new-promo">
					<h4>Nouvelle Promo</h4>
					{!!Form::open(['url'=>'/admin/promo/add','id'=>'promo-form','uk-grid'=>'','class'=>'uk-grid-small'])!!}
					<div class="uk-width-1-2@m">
						{!!Form::label('Debut de la promo')!!}
						{!!Form::date('debut','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Fin de la promo')!!}
						{!!Form::date('fin','',['class'=>'uk-input uk-margin-small uk-border-rounded'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Intitule de la Promo')!!}
						{!!Form::text('intitule','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Donnez un titre a la promo'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Subvention')!!}
						{!!Form::number('subvention','',['class'=>'uk-input uk-margin-small uk-border-rounded','placeholder'=>'Entrez la Subvention','min'=>'0'])!!}
					</div>
					<div class="uk-width-1-1@m">
						{!!Form::label('Description')!!}
						{!!Form::textarea('description','',['class'=>'uk-textarea uk-margin-small uk-border-rounded','placeholder'=>'Decrivez la promo'])!!}
						{!!Form::submit('Validez',['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-width-1-5@m uk-width-1-1@s','id'=>'button-submit'])!!}
					</div>
					{!!Form::close()!!}
				</div>
				<div id="actif-promo">
					{!!Form::open(['url'=>'/admin/promo/edit','id'=>'edit-form','uk-grid'=>'','class'=>'uk-grid-small'])!!}
					<input type="hidden" name="id_promo" value="" id="id-input"/>
					<div class="uk-width-1-2@m">
						{!!Form::label('Debut de la promo')!!}
						{!!Form::date('debut','',['class'=>'uk-input uk-margin-small uk-border-rounded promo-inputs','id'=>'debut-input'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Fin de la promo')!!}
						{!!Form::date('fin','',['class'=>'uk-input uk-margin-small uk-border-rounded promo-inputs','id'=>'fin-input'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Intitule de la Promo')!!}
						{!!Form::text('intitule','',['class'=>'uk-input uk-margin-small uk-border-rounded promo-inputs','id'=>'intitule-input','placeholder'=>'Donnez un titre a la promo'])!!}
					</div>
					<div class="uk-width-1-2@m">
						{!!Form::label('Subvention')!!}
						{!!Form::number('subvention','',['class'=>'uk-input uk-margin-small uk-border-rounded promo-inputs','id'=>'subvention-input','placeholder'=>'Entrez la Subvention','min'=>'0'])!!}
					</div>
					<div class="uk-width-1-1@m">
						{!!Form::label('Description')!!}
						{!!Form::textarea('description','',['class'=>'uk-textarea uk-margin-small uk-border-rounded promo-inputs','id'=>'description-input','placeholder'=>'Decrivez la promo'])!!}
						<button type="button" class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small" style="display : none" id="reset-edit-button" name="button"><span uk-icon="icon : close"></span> Annuler</button>
						<button type="button" class="uk-button uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small" id="edit-button" name="button"><span uk-icon="icon : pencil"></span> Edit</button>
						<button type="button" class="uk-button uk-button-small uk-button-danger uk-border-rounded uk-box-shadow-small" id="delete-button" name="button"><span uk-icon="icon : ban"></span> Interrompre</button>
						{!!Form::submit('Validez',['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-width-1-5@m uk-width-1-1@s','id'=>'edit-submit'])!!}
					</div>
					{!!Form::close()!!}
				</div>
			</div>
    </div>

	<!-- // -->
	<!-- MODAL PAIEMENT COMMISSION -->
	@if(Auth::user()->type == "v_da")
	<input type="hidden" name="" id="commission-for-user" value="{{Auth::user()->username}}">
	<input type="hidden" name="" id="state-validate-button" value="without-validate-button">
	@elseif(Auth::user()->type == "gcga")
	<input type="hidden" name="" id="commission-for-user" value="all">
	<input type="hidden" name="" id="state-validate-button" value="with-validate-button">
	@elseif(Auth::user()->type == 'admin')
	<input type="hidden" name="" id="commission-for-user" value="all">
	<input type="hidden" name="" id="state-validate-button" value="without-validate-button">
	@endif
	<div id="modal-commission" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false;">
	    <div class=" uk-modal-dialog">
	        <div class="uk-modal-header">
	            <h3 class="uk-modal-title"> <i class="material-icons">monetization_on</i> Paiement Commission</h3>
	        </div>
	        <div class="uk-modal-body uk-overflow-auto uk-height-medium">
						<table class="uk-table uk-table-small uk-table-hover uk-table-striped uk-table-divider uk-table-responsive">
							<thead>
								<tr>
									<th>Du</th>
									<th>Au</th>
									<th>Total</th>
									<th>Status</th>
									<th>Vendeurs</th>
								</tr>
							</thead>
							<tbody id="pay-commission-list"><div class="loader" uk-spinner></div></tbody>
						</table>
	        </div>
	        <div class="uk-modal-footer uk-text-right">
	            <button class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small uk-button-danger uk-modal-close" type="button">Fermer</button>
	        </div>
	    </div>
	</div>
	<!-- // MODAL VALIDATE PAIEMENT COMMISSION REQUEST  -->
	<div id="validate-payment-comission" uk-modal="esc-close : false ; bg-close : false">
	    <div class="uk-modal-dialog">
				{!!Form::open(['url'=>'/user/rapport-ventes/validate-pay-commission','id'=>'validate-form-pay-comission'])!!}
	        <div class="uk-modal-header">
	            <h6>Vous confirmez la validation pour le paiement des commissions a hauteur de : <span class="uk-text-bold" id="pay-amount"></span>	pour : <span class="uk-text-bold" id="pay-user"></span> </h6>
	        </div>
	        <div class="uk-modal-body">
						<input type="hidden" name="pay_comission_id" value="" id="pay-id">
						{!!Form::label('Confirmez votre Mot de passe')!!}
						{!!Form::password('password_confirm',['class'=>'uk-input uk-margin-small uk-border-rounded','autofocus' ,'placeholder'=>'Entrez votre mot de passe pour la confirmation'])!!}
					</div>
	        <div class="uk-modal-footer uk-text-right">
						<button class="uk-button uk-button-danger uk-modal-close uk-border-rounded uk-box-shadow-small uk-button-small" type="button">Fermer</button>
						{!!Form::submit("validez",['class'=>'uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small'])!!}
					</div>
					{!!Form::close()!!}
	    </div>
	</div>


	<!-- // -->
	<!-- // -->
<!-- NAVBAR-->
<div class="uk-navbar-container uk-box-shadow-small" id="entete" uk-sticky uk-navbar>
    <div class="uk-navbar-left">
        <button class="uk-navbar-item uk-button" uk-toggle="target:#side-nav" uk-icon="icon:menu"></button>
        <a href="" class="uk-navbar-item uk-logo">LAYE DISTRIBUTION</a>
    </div>
    <div class="uk-navbar-center uk-visible@m">

    	<a class="uk-button uk-button-small border-button" href="{{url('/')}}" uk-tooltip="Tableau de bord"><i class="material-icons">home</i></a>
			<!-- NOTIFICATION -->
			<div class="uk-inline">
				<a class="uk-button uk-button-small  border-button" uk-tooltip="Notifications"><i class="material-icons">notifications</i><sup id="notification-count" class="uk-badge">0</sup></a>

			    <div class="" uk-drop="mode: click ; animation: uk-animation-slide-top-small;">
			        <div class="uk-card-default uk-box-shadow-small notification-container uk-overflow-auto" style="background : #fefefe !important;border : solid 1px #ddd !important; ">
								<dl class="uk-description-list uk-description-list-divider" id="notification-list"><div uk-spinner></div></dl>
								<a class="uk-button uk-button-link uk-text-capitalize uk-margin-small-left" href="#all-notification" uk-toggle>Tout voir</a>
							</div>
			    </div>
			</div>
			<!-- TOUTES LES NOTIFICATIOS -->
			<div id="all-notification" uk-modal="esc-close : false ; bg-close : false">
			    <div class="uk-modal-dialog uk-modal-body">
			        <h3 class="uk-modal-title"><i class="material-icons">notifications</i> Toutes les notifications</h3>
							<ul class="uk-subnav uk-subnav-pill" uk-switcher="animation: uk-animation-slide-left-medium">
							    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Non Lues</a></li>
							    <li><a class="uk-button uk-button-small uk-border-rounded uk-box-shadow-small" href="#">Lues</a></li>
							</ul>

							<ul class="uk-switcher uk-margin">
							    <li>
										<dl class="uk-description-list uk-description-list-divider uk-overflow-auto uk-height-medium" id="notification-unread"><span class='uk-flex uk-flex-center uk-text-meta'>Aucune Notification !<span></dl>
									</li>
							    <li>
										<dl class="uk-description-list uk-description-list-divider uk-overflow-auto uk-height-medium" id="notification-read"><span class='uk-flex uk-flex-center uk-text-meta'>Aucune Notification !<span></dl>
									</li>
							</ul>
			        <p class="uk-text-right">
			           <button class="uk-button uk-button-small uk-button-primary uk-border-rounded uk-box-shadow-small uk-modal-close" type="button">Fermer</button>
			        </p>
			    </div>
			</div>
			<!-- // -->
    	<a class="uk-button uk-button-small border-button" uk-tooltip="Conversations"><i class="material-icons">message</i></a>
    	<a class="uk-button uk-button-small border-button" uk-tooltip="Alarmes"><i class="material-icons">alarm</i></a>

			@if(Auth::user()->type == 'admin')
			<a class="uk-button uk-button-small border-button button-pay-comission" uk-toggle href="#modal-commission" uk-tooltip="Paiement Commission"><i class="material-icons">monetization_on</i></a>
    	<a class="uk-button uk-button-small uk-button-primary uk-box-shadow-hover-small uk-margin-left uk-border-rounded uk-box-shadow-hover-small" href="#modal-promo" uk-toggle><span uk-icon="icon : tag"></span> Promo</a>
			@endif
			@if(Auth::user()->type == 'gcga')
			<a class="uk-button uk-button-small border-button button-pay-comission" id="" uk-toggle href="#modal-commission" uk-tooltip="Paiement Commission"><i class="material-icons">monetization_on</i></a>
			@endif
			@if(Auth::user()->type == 'v_da')
			<a class="uk-button uk-button-small border-button button-pay-comission" uk-toggle href="#modal-commission" uk-tooltip="Paiement Commission"><i class="material-icons">monetization_on</i></a>
			@endif
    </div>
		<div class="uk-navbar-right uk-visible@m">
			@if(Auth::user()->type != 'admin' && Auth::user()->type != 'logistique' && Auth::user()->type != "controleur" && Auth::user()->type != 'commerciale' && Auth::user()->type !='gcga' && Auth::user()->type !='grex' && Auth::user()->type !=='gdepot')
			<a class="uk-button"><span uk-icon="icon:location;ratio:.8"></span> <span>{{Auth::user()->localisation}}</span></a>
			@endif
			@if(Auth::user()->type == 'logistique' || Auth::user()->type =='gcga' || Auth::user()->type =='grex' || Auth::user()->type =='gdepot' || Auth::user()->type == 'controleur')
			<a class="uk-button"><span uk-icon="icon:location;ratio:.8"></span> <span>{{Auth::user()->type}}</span></a>
			@endif
			<a href="#" class="uk-button"><span uk-icon="icon : user ; ratio : .8"></span> {{Auth::user()->username}}</a>
			{!!Form::open(['url'=>'/logout'])!!}
			<button class="uk-button uk-button-small uk-button-link uk-margin-right border-button" type="submit" uk-tooltip="Deconnexion"><i class="material-icons">power_settings_new</i></button>
			{!!Form::close()!!}
		</div>
</div>
<!-- // -->
    <!-- CONTENS -->
<div id="app" class="uk-margin-large-bottom">
    @yield('content')
</div>
<!-- // -->
	<!-- SIDENAV -->
<div id="side-nav"  uk-offcanvas="mode:push;bg-close:true;">
    <div class="uk-offcanvas-bar side-nav">
        <ul class="uk-nav uk-nav-default uk-nav-parent-icon" uk-nav>
            <li class="uk-nav-header">
							<h5>LAYE DIST / CANAL+ AFROCASH <a href="#" class="uk-button"><span uk-icon="icon : user ; ratio : .8"></span> {{Auth::user()->username}}</a>
								@if(Auth::user()->type != 'admin' && Auth::user()->type != 'logistique' && Auth::user()->type != 'commerciale' && Auth::user()->type !='gcga' && Auth::user()->type !='grex' && Auth::user()->type !=='gdepot')
				        <a class="uk-button"><span uk-icon="icon:location;ratio:.8"></span> <span>{{Auth::user()->localisation}}</span></a>
				        @endif
				        @if(Auth::user()->type == 'logistique' || Auth::user()->type =='gcga' || Auth::user()->type =='grex' || Auth::user()->type =='gdepot')
					    	<a class="uk-button"><span uk-icon="icon:location;ratio:.8"></span> <span>{{Auth::user()->type}}</span></a>
				        @endif
								@if(Auth::user()->type == 'gdepot')
								<a href="#" class="uk-button"><span uk-icon="icon:location;ratio:.8"></span>
									@php
									$temp = Auth::user();
									@endphp
									{{$temp->depot()->first()->localisation}}
								</a>
								@endif
							</h5>
					</li>
                        <!-- ADMIN ONLY -->
                        @if(Auth::user()->type == 'admin')
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:user;ratio:0.9"></span> Utilisateurs</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/add-user')}}"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li>
                        <li><a href="{{url('/admin/list-users')}}"><span uk-icon="icon:arrow-right"></span> Tous les utilisateurs</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Compte Credit</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/add-account-credit')}}"><span uk-icon="icon:arrow-right"></span> Comptes</a></li>
                    </ul>
                </li>
								<li class="uk-parent">
									<a href="#"><span uk-icon="icon: credit-card;"></span> Afrocash</a>
									<ul class="uk-nav-sub">
										<li><a href="{{url('admin/afrocash')}}"><span uk-icon="icon :arrow-right;"></span> Operations</a>	</li>
										<li><a href="{{url('/admin/afrocash/all-transactions')}}"><span uk-icon="icon : arrow-right;"></span> Toutes les transactions</a>	</li>
									</ul>
								</li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Materiels</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/add-depot')}}"><span uk-icon="icon:arrow-right"></span> Nouveau Materiel</a></li>
                        <li><a href="{{url('/admin/depot-central')}}"><span uk-icon="icon:arrow-right"></span> Entrepot</a></li>
                        <li><a href="{{url('/admin/list-material')}}"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                        <li><a href="{{url('/admin/history-depot')}}"><span uk-icon="icon:arrow-right"></span> Historique </a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/inventory')}}"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                        <li><a href="#"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/commandes')}}"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('admin/add-rapport')}}"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li>
                                <li><a href="{{url('admin/all-rapport')}}"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li>
                            </ul>
                        </li>
                    @endif
                        <!-- // -->
									@if(Auth::user()->type == 'controleur')
									<li class="uk-parent">
	                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
	                            <ul class="uk-nav-sub">
	                                <li><a href="{{url('user/add-rapport')}}"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li>
	                                <li><a href="{{url('user/all-rapport')}}"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li>
	                            </ul>
	                        </li>
									@endif
                        <!-- LOGISTIC ONLY -->
                        @if(Auth::user()->type =='logistique')
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Depots</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/user/ravitailler-depot')}}"><span uk-icon="icon:arrow-right"></span> Ravitailler un depot</a></li>
                        <li><a href="{{url('/user/list-material')}}"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                        <li><a href="{{url('/user/history-depot')}}"><span uk-icon="icon:arrow-right"></span> Historique </a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                    <ul class="uk-nav-sub">
                        <!-- <li><a href="{{url('/user/add-stock')}}"><span uk-icon="icon:plus"></span> Ravitaillement</a></li> -->
                        <li><a href="{{url('/user/inventory')}}"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                        <li><a href="#"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/user/commandes')}}"><span uk-icon="icon:thumbnails"></span> Toutes les Commandes</a></li>
                    </ul>
                </li>
                @endif
                <!-- // -->
                <!-- VENDEURS ONLY -->
                        @if(Auth::user()->type =='v_da' || Auth::user()->type =='v_standart')
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Ventes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/rapport-ventes')}}"><span uk-icon="icon:plus"></span> Rapport de ventes</a></li>
                            </ul>
                        </li>
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:thumbnails;ratio:0.9"></span> Stock</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/my-inventory')}}"><span uk-icon="icon:arrow-right"></span> Inventaire</a></li>
                                <li><a href="{{url('/user/my-history-ravitaillement')}}"><span uk-icon="icon:arrow-right"></span> Historique de ravitaillement</a></li>
                            </ul>
                        </li>

                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:users;ratio:0.9"></span> Carnet d'adresse</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/add-client')}}"><span uk-icon="icon:arrow-right"></span> Nouveau</a></li>
                                <li><a href="{{url('/user/list-client')}}"><span uk-icon="icon:arrow-right"></span> Repertoire</a></li>
                            </ul>
                        </li>
												<!-- AFROCASH -->
												<li class="uk-parent">
													<a href="#"><span uk-icon="icon : credit-card"></span> Afrocash</a>
													<ul class="uk-nav-sub">
														<li><a href="{{url('/user/afrocash')}}"> <span uk-icon="icon : arrow-right"></span>	Operations</a> </li>
														<li><a href="{{url('/user/afrocash/all-transactions')}}"> <span uk-icon="icon : arrow-right"></span>	Toutes les Transactions</a> </li>
													</ul>
												</li>
												<!-- // -->
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/new-command')}}"><span uk-icon="icon:arrow-right"></span> Nouvelle Commande</a></li>
                                <li><a href="{{url('/user/list-command')}}"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                            </ul>
                        </li>
                        @endif
                        <!-- CREDIT CGA -->
                        @if(Auth::user()->type == 'gcga')
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Credit</a>
                            <ul class="uk-nav-sub">

                                <li><a href="{{url('user/cga-credit')}}"><span uk-icon="icon:arrow-right"></span> Comptes</a></li>
                                <!-- // -->
                            </ul>
                        </li>
												<li class="uk-parent">
													<a href="#" ><span uk-icon="icon:credit-card"></span> Afrocash</a>
													<ul class="uk-nav-sub">
														<li><a href="{{url('user/afrocash/transactions')}}"><span uk-icon="icon : arrow-right"></span>  Toutes les Transactions</a> </li>
													</ul>
												</li>
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/credit-cga/commandes')}}"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                            </ul>
                        </li>
                        @endif
                        <!-- CREDIT REX -->
                        @if(Auth::user()->type == 'grex')
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Credit</a>
                            <ul class="uk-nav-sub">
                                <!-- LOGISTIC ONLY -->
                                <li><a href="{{url('user/rex-credit')}}"><span uk-icon="icon:arrow-right"></span> Comptes</a></li>
                                <!-- // -->
                                <li><a href=""><span uk-icon="icon:arrow-right"></span> Historique</a></li>
                            </ul>
                        </li>
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('/user/credit-rex/commandes')}}"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                            </ul>
                        </li>
                        @endif

                        <!-- // -->
												<!-- GESTIONNAIRE DEPOT -->
												@if(Auth::user()->type == 'gdepot')
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Materiels</a>
                            <ul class="uk-nav-sub">
                                <!-- LOGISTIC ONLY -->
                                <li><a href="{{url('/user/inventaire-depot')}}"><span uk-icon="icon:arrow-right"></span> Stock</a></li>
                                <li><a href="{{url('/user/livraison')}}"><span uk-icon="icon:arrow-right"></span> Commandes</a></li>
                                <!-- // -->
                                <li><a href=""><span uk-icon="icon:history"></span> Historique</a></li>
																<!-- // -->
                            </ul>
                        </li>
                        @endif
												<!-- /// -->
												<!-- COURSIER -->
												@if(Auth::user()->type == 'coursier')
												<li class="uk-parent">
                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Operations</a>
                            <ul class="uk-nav-sub">
                                <!-- LOGISTIC ONLY -->
                                <li><a href="{{url('/user/recouvrement')}}"><span uk-icon="icon:arrow-right"></span> Recouvrement</a></li>
                                <!-- <li><a href=""><span uk-icon="icon:arrow-right"></span> Toutes les transactions</a></li> -->
																<!-- // -->
                            </ul>
                        </li>
												@endif
                        <!-- // -->
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:settings;ratio:0.9"></span> Parametres</a>
                    <ul class="uk-nav-sub">
                        @if(Auth::user()->type == 'admin')
                        <li><a href="{{url('/admin/formule')}}"><span uk-icon="icon:check"></span> Formule</a></li>
                        <li><a href="{{url('admin/settings')}}"><span uk-icon="icon:user"></span> Profile</a></li>
                        @else
                        <li><a href="{{url('/user/settings')}}"><span uk-icon="icon:user"></span> Profile</a></li>
                        @endif
                        <!-- // -->
                        {!!Form::open(['url'=>'/logout'])!!}
                        <li><button class="uk-button uk-button-link" type="submit"><span uk-icon="icon:sign-out"></span>Deconnexion</button></li>
                        {!!Form::close()!!}
                    </ul>
                </li>
            </ul>
    </div>
</div>
<!-- // -->
<!-- CHATBOX -->
<a  class="uk-button uk-button-small uk-padding-small uk-border-pill uk-button-primary uk-visible@m chat-buttons" id="chat-button" href="#messages-chat-box" uk-toggle><i class="material-icons">chat</i></a>

<div id="messages-chat-box" class="uk-modal-container" uk-modal="esc-close : false ; bg-close : false;">
    <div class="uk-modal-dialog">
        <button class="uk-modal-close-default" type="button" uk-close></button>
				<div class="uk-modal-header">
					<h3 class="uk-modal-title"> <i class="material-icons">forum</i></h3>
				</div>
				<div class="uk-modal-body uk-hidden@m">
					{!!Form::open(['url'=>''])!!}
					{!!Form::text('search_user','',['class'=>'uk-input uk-border-rounded uk-box-shadow-hover-small','placeholder' => 'Trouvez un utilisateur','autofocus'])!!}
					{!!Form::close()!!}
					<ul class="uk-list  uk-list-divider uk-height-large uk-overflow-auto chat-user-lists" id="chat-user-list-responsive"><span class="uk-text-meta">Patientez svp... <div uk-spinner></div></span></ul>
				</div>
				<div class="uk-modal-body uk-grid-divider uk-visible@m" uk-grid>
					<div class="uk-width-2-3@m">
						{!!Form::open(['url'=>'','class'=>'uk-width-xlarge form-chat-text'])!!}
							{!!Form::text('message_text','',['class'=>'uk-input','placeholder'=>'Votre message ici ...'])!!}
							<button type="submit" class="uk-button uk-button-small uk-button-link uk-float uk-float-right" name="button"> <i class="material-icons">send</i> </button>
						{!!Form::close()!!}
					</div>
					<div class="uk-width-1-3@m">
						<!-- LIST DES UTILISATEURS -->
						{!!Form::open(['url'=>''])!!}
						{!!Form::text('search_user','',['class'=>'uk-input uk-border-rounded uk-box-shadow-hover-small','placeholder' => 'Trouvez un utilisateur','autofocus'])!!}
						{!!Form::close()!!}
						<ul class="uk-list  uk-list-divider uk-height-large uk-overflow-auto chat-user-lists" id="chat-user-list"><span class="uk-text-meta">Patientez svp... <div uk-spinner></div></span></ul>
						<!-- // -->
					</div>
				</div>
    </div>
</div>
<!-- // -->


<!-- FOOTER -->
<!-- <div  class="uk-position-fixed uk-position-bottom uk-position-z-index uk-text-center ">
<p>Copyright &copy; {{date('Y')}}</p>
</div> -->
<!-- // -->
<script type="text/javascript" src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery-ui.min.js')}}"></script>
<!-- UIkit JS -->
<script type="text/javascript" src="{{asset('js/uikit.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/uikit-icons.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>
<script type="text/javascript" src="{{asset('js/myscript.js')}}"></script>
<script type="text/javascript" src="{{asset('js/myscript_second.js')}}"></script>
<script type="text/javascript" src="{{asset('js/myscript_third.js')}}"></script>
<script src="{{ mix('js/app.js') }}" ></script>
<script src="{{ mix('js/store.js') }}" ></script>
<script src="{{ mix('js/echo.js') }}" ></script>
<script type="text/javascript">
	$(function () {
		$('form').on('submit',function (e) {

			window.scrollTo({
				top : 0
			})
			$("#loader").show()

		})

    $(".close-button").on('click',function () {
      $(this).parent().hide(500);
		})

		// ##%%%
		@if(Auth::user()->type == 'admin')
		$logistique.getPromo("{{csrf_token()}}","{{url('/admin/promo/list')}}","{{url('/admin/promo/interrompre')}}")
		$logistique.sendPromoForm()
		$logistique.sendPromoForm($("#edit-form"))

		// edit
		$("#edit-button").on('click',function () {
			$(".promo-inputs").removeAttr('disabled')
			$("#reset-edit-button").show(200)
			$("#delete-button").hide(200)
			$("#edit-submit").removeAttr('disabled')
			$(this).hide(200)
		})

		// annuler l'edition
		$("#reset-edit-button").on('click',function () {
			$logistique.getPromo("{{csrf_token()}}","{{url('/admin/promo/list')}}","{{url('/admin/promo/interrompre')}}")
			$('.promo-inputs').attr('disabled','')
			$(this).hide(200)
			$("#edit-button").show(200)
			$("#delete-button").show(200)
		})
		@endif
		setInterval(function () {
			$logistique.notificationList("{{csrf_token()}}","{{url('/user/notification/getlist')}}","{{Auth::user()->username}}","{{url('/user/notification/mark-as-read')}}")
		},20000);
		$logistique.notificationList("{{csrf_token()}}","{{url('/user/notification/getlist')}}","{{Auth::user()->username}}","{{url('/user/notification/mark-as-read')}}")

		// RECUPERATION DE L'HISTORIQUE DE PAIEMENT DES COMISSIONS
		// console.log($("#commission-for-user").val())
		$(".button-pay-comission").on('click',function () {
			$logistique.payCommissionList("{{csrf_token()}}","{{url('/user/rapport-ventes/get-pay-commission')}}",$("#commission-for-user").val())
		})
		// chat service
		$chatService.openChatBox("{{url('/user/chat-service/users-list')}}")
})
</script>
@yield('script')
</body>
</html>

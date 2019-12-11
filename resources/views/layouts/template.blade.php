<!DOCTYPE html>
<html>
<head>
	<title>{{config('app.name')}}-@yield('title')</title>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/css/uikit.min.css" />
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

</head>
<body>

<!-- NAVBAR-->
<div class="uk-navbar-container uk-box-shadow-small" uk-sticky uk-navbar>
    <div class="uk-navbar-left">
        <button class="uk-navbar-item uk-button" uk-toggle="target:#side-nav" uk-icon="icon:menu"></button>
        <a href="" class="uk-navbar-item uk-logo">Loudcssyf-Sarl</a>
    </div>
    <div class="uk-navbar-right">
    	<a class="uk-button"><span class="" uk-icon="icon:bell"></span></a>
    	<a class="uk-button"><span uk-icon="icon:comment"></span></a>
        <a class="uk-button"><span uk-icon="icon:user"></span> <span>{{Auth::user()->username}}</span></a>
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
    </div>
</div>
<!-- // -->
    <!-- CONTENS -->
<div>
    @yield('content')
</div>
<!-- // -->
	<!-- SIDENAV -->
<div id="side-nav" uk-offcanvas="mode:slide;bg-close:true;">
    <div class="uk-offcanvas-bar">
        <ul class="uk-nav uk-nav-default uk-nav-parent-icon" uk-nav>
            <li class="uk-nav-header">Navigation</li>
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
                        <li><a href="{{url('/admin/add-account-credit')}}"><span uk-icon="icon:arrow-right"></span> Crediter</a></li>
                        <!-- <li><a href="#"><span uk-icon="icon:arrow-right"></span> Creer un compte</a></li> -->
                        <li><a href="#"><span uk-icon="icon:arrow-right"></span> Toutes les transactions</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                    <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Depots</a>
                    <ul class="uk-nav-sub">
                        <li><a href="{{url('/admin/add-depot')}}"><span uk-icon="icon:arrow-right"></span> Nouveau Materiel</a></li>
                        <li><a href="{{url('/admin/depot-central')}}"><span uk-icon="icon:arrow-right"></span> Depot Central</a></li>
                        <li><a href="{{url('/admin/list-material')}}"><span uk-icon="icon:arrow-right"></span> Tous les materiels</a></li>
                        <!-- <li><a href="{{url('/admin/list-depot')}}"><span uk-icon="icon:list"></span> Tous les depots</a></li> -->
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
                        <li><a href="{{url('/user/commandes')}}"><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
                    </ul>
                </li>
                <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:cart;ratio:0.9"></span> Rapport Ventes</a>
                            <ul class="uk-nav-sub">
                                <li><a href="{{url('admin/add-rapport')}}"><span uk-icon="icon:arrow-right"></span> Ajouter</a></li>
                                <!-- <li><a href="#"><span uk-icon="icon:plus"></span> Recrutement</a></li> -->
                                <!-- <li><a href="#"><span uk-icon="icon:refresh"></span> Reabonnement</a></li> -->
                                <!-- <li><a href="#"><span uk-icon="icon:move"></span> Upgrade</a></li> -->
                                <!-- <li><a href="#"><span uk-icon="icon:shrink"></span> Migration</a></li> -->
                                <!-- <li><a href="#"><span uk-icon="icon:forward"></span> Libre</a></li> -->
                                <li><a href="{{url('admin/all-rapport')}}"><span uk-icon="icon:arrow-right"></span> Toutes les ventes</a></li>
                            </ul>
                        </li>
                    @endif
                        <!-- // -->
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
                                <!-- <li><a href="{{url('/user/recrutement')}}"><span uk-icon="icon:plus"></span> Recrutement</a></li> -->
                                <!-- <li><a href="{{url('/user/abonnement')}}"><span uk-icon="icon:refresh"></span> Reabonnement</a></li>
                                <li><a href="#"><span uk-icon="icon:move"></span> Upgrade</a></li>
                                <li><a href="#"><span uk-icon="icon:shrink"></span> Migration</a></li>
                                <li><a href="#"><span uk-icon="icon:forward"></span> Libre</a></li>
                                <li><a href="{{url('/user/ventes')}}"><span uk-icon="icon:history"></span> Toutes les ventes</a></li>
                                <li><a href="{{url('/user/commissions')}}"><span uk-icon=""></span> Commissions</a></li> -->
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
														<li><a href="#"> <span uk-icon="icon : arrow-right"></span>	Toutes les Transactions</a> </li>
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
                        <!-- <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:minus;ratio:0.9"></span> Technicien</a>
                            <ul class="uk-nav-sub">
                                <li><a href="#"><span uk-icon="icon:check"></span> Installation</a></li>
                                <li><a href="#"><span uk-icon="icon:check"></span> Intervention</a></li>
                            </ul>
                        </li> -->
                        @endif
                        <!-- CREDIT CGA -->
                        @if(Auth::user()->type == 'gcga')
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:credit-card;ratio:0.9"></span> Credit</a>
                            <ul class="uk-nav-sub">
                                <!-- LOGISTIC ONLY -->
                                <li><a href="{{url('user/cga-credit')}}"><span uk-icon="icon:arrow-right"></span> Crediter un Vendeur</a></li>
                                <!-- // -->
                                <li><a href=""><span uk-icon="icon:arrow-right"></span> Historique</a></li>
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
                                <li><a href="{{url('user/rex-credit')}}"><span uk-icon="icon:arrow-right"></span> Crediter un Vendeur</a></li>
                                <!-- // -->
                                <li><a href=""><span uk-icon="icon:arrow-right"></span> Historique</a></li>
                            </ul>
                        </li>
                        <li class="uk-parent">
                            <a href="#"><span uk-icon="icon:grid;ratio:0.9"></span> Commandes</a>
                            <ul class="uk-nav-sub">
                                <li><a href=""><span uk-icon="icon:arrow-right"></span> Toutes les Commandes</a></li>
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



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- UIkit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/js/uikit.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/js/uikit-icons.min.js"></script>
<script type="text/javascript" src="{{asset('js/myscript.js')}}"></script>
<script type="text/javascript" src="{{asset('js/myscript_second.js')}}"></script>
<script type="text/javascript">
	$(function () {
        $(".close-button").on('click',function () {
            $(this).parent().hide(500);
        })
	})
</script>
@yield('script')
</body>
</html>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <!-- <script async src="https://www.googletagmanager.com/gtag/js?id=UA-130660555-4"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-130660555-4');
    </script> -->
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
<!-- UIkit CSS -->
  <link rel="stylesheet" href="{{asset('css/uikit.min.css')}}">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.css" integrity="sha256-IvM9nJf/b5l2RoebiFno92E5ONttVyaEEsdemDC6iQA=" crossorigin="anonymous" />
  <link rel="stylesheet" href="{{asset('css/app.css')}}?<?php echo filemtime('css/app.css') ?>">
  <link rel="icon" href="/img/logo.PNG" type="image/png">
  <script type="text/javascript">
    function noBack(){window.history.forward()}
    noBack();
    window.onload=noBack;
    window.onpageshow=function(evt){if(evt.persisted)noBack()}
    window.onunload=function(){void(0)}
  </script>
  <title> LOUDCSSYF </title>
  </head>
  <body onselectstart="return true" oncontextmenu="return false" ondragstart="return false" onMouseOver="window.status=''; return true;">
    <input type="hidden" id="user-type" value="{{Auth::user()->type}}">
    <input type="hidden" id="user-localisation" value="{{Auth::user()->localisation}}">
    <input type="hidden" id="username" value="{{Auth::user()->username}}">

    <div id="app">
      <serial-search></serial-search>
      <deblocage-cga></deblocage-cga>
      <feedback></feedback>
      <home-component></home-component>
      <tools the-user="{{Auth::user()->type}}"></tools>
      <historique-reactivation-materiel :view-modal="true"></historique-reactivation-materiel>
      <router-view class="content-block"></router-view>
    </div>
    <div class="uk-section uk-section-muted uk-padding-small uk-margin-top uk-visible@m ">
      <div class="uk-container uk-container-large">
        <p class="uk-text-center">
          &copy; Copyright {{date('Y')}} Loudcssyf-Sarl,Layedist v2.3 by Smartech
        </p>
      </div>
    </div>

    <script type="application/javascript" src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
    <!-- UIkit JS -->
    <script type="application/javascript" src="{{asset('js/uikit.min.js')}}"></script>
    <script type="application/javascript" src="{{asset('js/uikit-icons.min.js')}}"></script>

    <script type="application/javascript" src="{{mix('js/app.js')}}?<?php echo filemtime('js/app.js') ?>" ></script>
    <script type="application/javascript" src="{{ mix('js/store.js') }}" ></script>
    <!-- <script type="application/javascript" src="{{ mix('js/echo.js') }}" ></script> -->
  </body>
</html>

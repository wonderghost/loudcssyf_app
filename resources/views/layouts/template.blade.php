<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
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
  <title>Loudcssyf</title>
  </head>
  <body>
    <input type="hidden" id="user-type" value="{{Auth::user()->type}}">
    <input type="hidden" id="user-localisation" value="{{Auth::user()->localisation}}">
    <input type="hidden" id="username" value="{{Auth::user()->username}}">
    <div id="loader">
  		<div class="uk-border-rounded uk-dark uk-width-1-2@s uk-width-1-3@m uk-align-center uk-background-default uk-margin-xlarge-top uk-padding uk-visible@m uk-flex uk-flex-middle uk-flex-center" style="margin-top : 20% !important;">
  			<div class="uk-margin-right" uk-spinner></div>  Patientez svp ...
  		</div>
  		<div class="uk-border-rounded uk-dark uk-width-1-2@s uk-width-1-3@m uk-align-center uk-background-default uk-margin-xlarge-top uk-padding uk-hidden@m uk-flex uk-flex-middle uk-flex-center" style="margin-top : 50% !important;">
  			<div class="uk-margin-right" uk-spinner></div>  Patientez svp ...
  		</div>
  	</div>
    <div id="app">
      <home-component></home-component>
      @yield('content')
    </div>
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
  </body>
</html>

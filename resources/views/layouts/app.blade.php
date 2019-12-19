<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/css/uikit.min.css" />
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
</head>
<body>
  <!-- loader -->
	<div id="loader">
		<div class="uk-border-rounded uk-dark uk-width-1-1@s uk-width-1-3@m uk-align-center uk-background-default uk-margin-xlarge-top uk-padding uk-flex uk-flex-middle uk-flex-center" style="margin-top : 10% !important;">
			<div class="uk-margin-right" uk-spinner></div>  Patientez svp ...
		</div>
	</div>
    <!-- NAVBAR-->
<div class="uk-navbar-container" uk-sticky uk-navbar>
    <div class="uk-navbar-center">
        <a href="" class="uk-navbar-item uk-logo">Loudcssyf-Sarl</a>
    </div>
</div>
<!-- // -->
@yield('content')
    <!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<!-- UIkit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/js/uikit.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.3/js/uikit-icons.min.js"></script>
<script type="text/javascript">
  $(function () {
    $('form').on('submit',function (e) {

			window.scrollTo({
				top : 0
			})
			$("#loader").show()

		})
  })
</script>
</body>
</html>

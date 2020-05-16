<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation de Saisie</title>
</head>
<body>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deblocage de Compte Cga</title>
    <style>
        .uk-navbar-container {
            /* border: solid 1px black; */
            padding : 1%;
        }
        .uk-logo {
            margin-left : 40%;
            text-decoration : none;
        }
        .container {
            /* border : solid 1px #ddd; */
            margin-left : 2%;
            margin-right : 2%;
        }
        body {
            background : #fff;
            margin : 0;
            padding : 0;
        }
        .table {
            border : solid 1px #ddd;
            margin-left : 18%;
        }
        .table-header{
            margin-left : 40%;
        }
    </style>
</head>
<body>
    <div class="">
        <div class="container">
            <nav class="uk-navbar-container" uk-navbar>
                <div class="uk-navbar-center">
                    <a class="uk-navbar-item uk-logo">LOUDCSSYF-SARL</a>
                </div>
            </nav>
            <!-- BODY -->
            <!-- CORPS DU MESSAGE -->
            <p>
                {!!$data['comment']!!}
            </p>
            <!-- // -->
            <h4 class="table-header">Erreur de Saisies</h4>
            <table class="table" border="1">
                <thead>
                    <tr style="background : blue ; color : #fff">
                        <th>Numero Distributeur</th>
                        <th>Numero abonne</th>
                        <th>Saisies erron&eacute;es</th>
                        <th>Saisies correctes</th>
                        <th>Date de la saisie</th>
                        <th>Modification O/N</th>
                        <th>Annulation O/N</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align : center">{{$data['num_dist']}}</td>
                        <td style="text-align : center">{{$data['num_abonne']}} </td>
                        <td style="text-align : center">{{$data['saisie_errone']}}</td>
                        <td style="text-align : center">{{$data['saisie_correcte']}}</td>
                        <td style="text-align : center">{{$data['date_saisie']}}</td>
                        <td style="text-align : center">{{$data['modification_state']}}</td>
                        <td style="text-align : center">{{$data['annulation_state']}}</td>
                    </tr>
                </tbody>
            </table>
            <!-- // -->
        </div>
    </div>
</body>
</html>
</body>
</html>
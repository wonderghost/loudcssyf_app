<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deblocage de Compte Cga</title>
    <style>
        .uk-navbar-container {
            border: solid 1px black;
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
            <p>{!!$data['comment']!!} </p>
            <!-- // -->
            <table class="table" border="1">
                <thead>
                    <tr style="background : blue ; color : #fff">
                        <th>Pays</th>
                        <th>Distributeur <br> Vendeur</th>
                        <th>Login <br> Compte Utilisateur</th>
                        <th>Nom-Prenom</th>
                        <th>Reinitialisation <br> Changement de mot de passe obligatoire</th>
                        <th>Deblocage <br> Sans changement de mot de passe</th>
                        <th>Message d'erreur qui s'affiche lors de la connexion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>GUINEE CONAKRY</td>
                        <td style="text-align : center">{{$user->agence()->num_dist}}</td>
                        <td style="text-align : center">{{$data['compte_user']}}</td>
                        <td style="text-align : center">{{$data['nom_prenom']}} </td>
                        <td style="text-align : center">OUI</td>
                        <td style="text-align : center">NON</td>
                        <td style="color : red">Votre compte est bloqu&eacute;.<br>Contacter votre administrateur</td>
                    </tr>
                </tbody>
            </table>
            <!-- // -->
        </div>
    </div>
</body>
</html>
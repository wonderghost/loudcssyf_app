<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande Canal Plus</title>
    <style>
        html,body {
            margin : 0px !important;
            padding : 0px !important;
        }
        .container {
            margin-top : 0 !important;
            margin : 10% 10% auto;
            border : 2px solid #000;
            padding : 1% ;
        }
        .logo {
            
        }

        .prix-tva {
            flex : 1;
            text-align : right;
        }
        .content > div {
            margin-top : .5% !important;
            /* border : solid 1px #000; */
            padding : .5% !important;
        }

        .table {
            width:100%;
            border : solid 1px #000;
            text-align : left;
        }
        .table-header {
            /* display:flex; */
            /* width:100%; */
            /* background:#000; */
            /* padding:($half-spacing-unit * 1.5) 0; */
        }

        .table-row {
            width:100%;
            border : solid 1px #000;
            text-align : left;
        }

        .table-data{
            flex: 1 1 20%;
            text-align:center;
        }

    </style>
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo"><img src="{{asset('/img/logo.PNG')}}" width="50" height="50"></div>
            <div class="prix-tva">
                <div>Taux TVA : 18%</div>
                <div>Prix de vente kit boutique TTC normal : 50 000</div>
            </div>
        </nav>
        <div class="content">
            <div>
                <span>Nom du Distributeur : </span><span></span>
            </div>
            <div>
                <span>NUMDIST GROSSISTE : </span><span></span>
            </div>
            <div>
                <span>Adresse : </span><span></span>
            </div>
            <div>
                <span>N<sup>o</sup>  RCCM : </span><span></span>
            </div>
            <div>
                <span>N<sup>o</sup> NIF/CLE DE TVA : </span><span></span>
            </div>
            <div>
                <span>BON DE COMMANDE A CANAL + GUINEE N<sup>o</sup></span><span></span>
            </div>
            <div>
                <span>DATE : </span><span></span>
            </div>
            <!-- TABLEAUX -->
            <div>
                <table border="1" class="table">
                    <thead class="table-header">
                        <tr>
                            <th>PRODUITS COMMANDES</th>
                            <th>QTE</th>
                            <th>HT</th>
                            <th>TVA 18%</th>
                            <th>PRIX TTC</th>
                            <th>MONTANT TTC</th>
                        </tr>
                    </thead>
                    <tbody class="table-data">
                        <tr class="table-row">
                            <td>Credit Point de Vente</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>40,000,000</td>
                        </tr>
                        <tr class="table-row">
                            <td>Paraboles</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>Terminal HD Z4</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>Kit Complet</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- // -->
        </div>
    </div>
</body>
</html>
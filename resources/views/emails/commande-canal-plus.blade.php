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
            margin-bottom : .5% !important;
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

        td:nth-child(6), td:nth-child(5) , td:nth-child(4) , td:nth-child(3){
            text-align : center;
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
                        <tr class="table-row">
                            <td>Blocs d'alimentaion</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>Telecommande SX2</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>TOTAL COMMANDE TTC (MATERIEL)</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>5 000 000</td>
                        </tr>
                        <tr class="table-row">
                            <td>SUBVENTION</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>MARGE Z4</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr class="table-row">
                            <td>Pour information le montant de la subvention est le suivant : </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>4 000 000</td>
                        </tr>
                        <tr class="table-row">
                            <td></td>
                            <td></td>
                            <td>TAUX</td>
                            <td>HT</td>
                            <td>TVA 18%</td>
                            <td>MONTANT TTC</td>
                        </tr>
                        <tr class="table-row">
                            <td>COMMISSION</td>
                            <td></td>
                            <td>0%</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                        <tr class="table-row">
                            <td>TOTAL A PAYER</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>4 000 000</td>
                        </tr>
                        <tr class="table-row">
                            <td>MONTANT DU CREDIT WEB A ATTRIBUER</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>4 000 000</td>
                        </tr>
                    </tbody>
                </table>
                <div>
                    <span>Condition de reglement : Comptant</span>
                </div>
                <div>
                    <span>Compte de paiement Canal+ Guinee : </span>
                    <span>Banque : ECOBANK GUINEE , Code Banque : GN010 , Code Guichet : 001</span>
                    <span>Numero Compte : 0010124621733904 , SWIFT : ECOCGNCN , RIB : 010001001006424746 </span>
                </div>
            </div>
            <!-- // -->
        </div>
    </div>
    <div class="container">
        
    </div>
</body>
</html>

// ###
function lisibilite_nombre(nbr) {
 var nombre = ''+nbr; var retour = '';
  var count=0;
  for(var i=nombre.length-1 ; i>=0 ; i--) {
  	if(count!=0 && count % 3 == 0)
  	 retour = nombre[i]+' '+retour ;
  	  else retour = nombre[i]+retour ;
  	   count++;
  	}
  	// alert('nb : '+nbr+' => '+retour);
  	 return retour;
  	}
//
var $adminPage = {
  editButtonClick : function () {
    $(".edit-button").on('click',function() {
      $(location).attr('href','/admin/edit-material/'+$(this).attr('id'));
    });
  },
  onLoading : function (flag) {
    if(flag) {
      $('.loader').show(300)
    } else {
      $('.loader').hide(300)
    }
  },
  showListMaterial : function (data) {
    var tr = []
      for(var i =0 ;i < data.length ; i++) {
        tr [i] = $("<tr></tr>");
        var td = [];
        for ( var j= 0 ; j< 8 ; j++) {
          td [j] = $("<td></td>");
          tr[i].append(td[j])
        }

        td[0].text(data[i].libelle)
        td[1].text(data[i].quantite_centrale)
        td[2].text(lisibilite_nombre(data[i].prix_initial))
        td[3].text(lisibilite_nombre(data[i].prix_vente))
        var ht = data[i].prix_vente - (data[i].prix_vente/1.18)
        var tva = data[i].prix_vente/1.18
        td[4].text(lisibilite_nombre(ht.toFixed(0)))
        td[5].text(lisibilite_nombre(tva.toFixed(0)))
        td[6].text(data[i].marge)
        var edit = $("<button></button>") , span = $("<span></span>");
        span.attr("uk-icon","icon:pencil")

        edit.attr('id',data[i].reference)
        edit.addClass('uk-button uk-button-primary uk-border-rounded edit-button')
        edit.text('edit')
        edit.append(span)
        td[7].append(edit)
        $("#list-material").append(tr[i])
      }
      $adminPage.editButtonClick();
  },
	makeForm : function (formToken,url,reference) {
		var form = $("<form></form>");form.attr('action',url);form.attr('method','post');
			var token = $("<input/>");
			var ref = $("<input/>");
			ref.attr('type','hidden');
			ref.attr('name','ref');
			ref.val(reference);
			token.attr('type','hidden');
			token.attr('name','_token');
			token.val(formToken);
			form.append(token);
			form.append(ref);
			return form;
	},

	getDataFormAjax : function (reference,formToken,url,tab,table,options,dashOption=false) {
		var quantite = null;
		if(reference) {
			var form=$adminPage.makeForm(formToken,url,reference);
			form.on('submit',function (e) {
				e.preventDefault();
				$.ajax({
					url : $(this).attr('action'),
					type : $(this).attr('method'),
					dataType:'json',
					data : $(this).serialize()
				})
				.done(function (data) {
					$adminPage.createTableRow(data,tab,table,options,dashOption);
					$adminPage.showImage();
					$adminPage.addItemToCommand($('.add-button'));
					// $('.edit-button').hide();
				})
				.fail(function (data) {
					return data
				});
			});
		}
			form.submit();
	}

};
// bloquer un utilisateur
$adminPage.blockUser = function ($formToken,$url,username) {
	var form = $adminPage.makeForm($formToken,$url,username);
	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url : $(this).attr('action'),
			type : $(this).attr('method'),
			dataType : 'json',
			data : $(this).serialize()
		})
		.done(function(data) {
			if(data && data == 'done') {
				UIkit.modal.alert("Utilisateur bloquer").then(function() {
					$(location).attr('href','');
				});
			}
		})
		.fail(function(data) {
			console.log(data);
		});
	});
	form.submit();
}
// debloquer un utilisateur
$adminPage.unblockUser = function ($formToken,$url,username) {
	var form = $adminPage.makeForm($formToken,$url,username);
	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url : $(this).attr('action'),
			type : $(this).attr('method'),
			dataType : 'json',
			data : $(this).serialize()
		})
		.done(function(data) {
			if(data && data == 'done') {
				UIkit.modal.alert("Utilisateur debloquer").then(function() {
					$(location).attr('href','');
				});
			}
		})
		.fail(function(data) {
			console.log(data);
		});
	});
	form.submit();
}

		 $adminPage.getAction = function (options=1,td,link,dashOption=false) {
		 		var linkEdit = $("<a></a>");linkEdit.addClass('uk-button uk-button-link uk-text-capitalize');
				var linkDetails = linkEdit.clone();
		 	if(options==1) {
		 		// make edit button
						linkEdit.text('Edit');
						linkEdit.addClass('edit-button');
						linkDetails.text('Details');
						linkEdit.attr('href','edit-item/'+link);
					 	td[5].append(linkEdit);
					 	linkDetails.attr('href','item/'+link);
		 	} else {
		 		// make add button
		 		linkEdit.text('add');
		 		linkEdit.addClass('add-button');
		 		linkEdit.attr('id',link);
			 	td[5].append(linkEdit);
		 		// linkEdit.remove();
		 		if(dashOption) {
		 			linkEdit.remove();
		 		}
		 		linkDetails.attr('href','/gerant/item/'+link);
		 	}
			linkDetails.text('Details');
			td[6].append(linkDetails);
		 };


$adminPage.createTableData = function (sdata,champs=null,table) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
						}
						td[0].text(sdata[i].libelle);
						td[0].addClass('uk-text-bold');
						td[1].text(sdata[i].quantite);
						td[2].text(sdata[i].prix_initial);
						td[3].text(sdata[i].prix_unitaire);
						td[4].text(sdata[i].ht);
						td[5].text(sdata[i].tva);
						td[6].text(sdata[i].marge);
						//
						var span = $("<span></span>");
						var linkEdit = $("<button></button>");linkEdit.addClass('uk-border-rounded');
						linkEdit.attr('type','button');
						linkEdit.addClass('uk-button uk-button-primary edit-button');
						linkEdit.attr('id',sdata[i].reference);

						linkEdit.text('edit')

						span.attr('uk-icon','icon:pencil;ratio:.8');
						linkEdit.append(span);

						td[7].append(linkEdit);
						// td[4].append(img);
						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}
						// console.log(list[i]);
						table.append(list[i]);
					}
					$("td:empty").remove();
		 };
// CREATION DE LA LISTE DES CLIENTS
$adminPage.createTableClient = function (sdata,champs=null,table) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
						}
						td[0].text(sdata[i].nom);
						td[1].text(sdata[i].prenom);
						td[2].text(sdata[i].email);
						td[2].addClass('uk-text-bold');
						td[3].text(sdata[i].phone);
						td[4].text(sdata[i].adresse);
						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };
// =====
// CREATION DE LA LISTE DES RECRUTEMENTS
$adminPage.createTableRecrutement = function (sdata,champs=null,table,linkDetails) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
							// td[j].addClass('uk-animation-shake');
						}
						// td[6].text(sdata[i].material);
						// LIENS POUR LE DETAIL DES VENTES A TRAVERS LE NUMERO CLIENTS
						var a 	=	$("<a></a>");
						a.attr('href','');
						a.addClass('uk-button uk-button-link uk-text-bold');
						a.text(sdata[i].clients);
						td[0].append(a);

						// td[0].addClass('uk-text-bold');
						// td[0].text(sdata[i].clients);

						td[1].text(sdata[i].nom);
						td[2].text(sdata[i].prenom);
						td[4].text(sdata[i].material);
						td[3].text(sdata[i].formule);
						// td[3].addClass('uk-text-success');

						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };

$adminPage.createTableSearchAbonnement = function (sdata,champs=null,table,linkDetails) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
							// td[j].addClass('uk-animation-shake');
						}
						// td[6].text(sdata[i].material);
						// LIENS POUR LE DETAIL DES VENTES A TRAVERS LE NUMERO CLIENTS
						var a 	=	$("<a></a>");
						a.attr('href','');
						a.addClass('uk-button uk-button-link uk-text-bold');
						a.text(sdata[i].clients);
						td[0].append(a);

						// td[0].addClass('uk-text-bold');
						// td[0].text(sdata[i].clients);

						td[1].text(sdata[i].nom);
						td[2].text(sdata[i].prenom);
						td[4].text(sdata[i].material);
						td[3].text(sdata[i].formule);
						// td[3].addClass('uk-text-success');

						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };

//
$adminPage.createTableInventaire = function (sdata,champs=null,table) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
						}
						td[0].text(sdata[i].libelle);
						td[0].addClass('uk-text-bold');
						td[1].text(sdata[i].quantite);
						td[2].text(sdata[i].prix_unitaire);
						td[3].text(sdata[i].ht);
						td[4].text(sdata[i].tva);
						//
						var span = $("<span></span>");
						var linkEdit = $("<button></button>");linkEdit.addClass('uk-border-rounded');
						linkEdit.attr('type','button');
						linkEdit.addClass('uk-button-primary');

						linkEdit.text('edit')

						span.attr('uk-icon','icon:pencil;ratio:.8');
						linkEdit.append(span);

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
					$("td:empty").remove();
		 };
		 //
$adminPage.createTableDataCommand = function (sdata,champs=null,table) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						list[i].addClass('uk-animation-shake');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
						}
						td[0].text(sdata[i].libelle);
						td[1].text(sdata[i].quantite);
						td[2].text(sdata[i].depot);
						if(sdata[i].origine == null) {
							td[3].text('-');
						} else {
							td[3].text(sdata[i].origine);
						}

						td[4].text(sdata[i].date);
						//
						var span = $("<span></span>");
						var linkDetails = $("<button></button>");linkDetails.addClass('uk-border-rounded');
						linkDetails.attr('type','button');
						linkDetails.addClass('uk-button uk-button-default');

						linkDetails.text('details')

						span.attr('uk-icon','icon:more;ratio:.8');
						linkDetails.append(span);

						td[5].append(linkDetails);
						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}
						// console.log(list[i]);
						table.append(list[i]);
					}
					$("td:empty").remove();
		 };


$adminPage.getListMaterial = function (formToken,url,reference,opt=true) {
// RECUPERATION DE LA LISTE DES MATERIALs
	var form = $adminPage.makeForm(formToken,url,reference);
	form.on('submit',function (e) {
		e.preventDefault();
		$.ajax({
			url : $(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType : 'json'
		})
		.done(function (data) {
			$("#loader").hide(500);
			if(!opt) {
				$adminPage.createTableData(data,['libelle','quantite','prix_initial','prix_unitaire','ht','tva','marge','edit'],$("#mat-list"));
			} else {
				$adminPage.createTableInventaire(data,['libelle','quantite','prix_unitaire','ht','tva'],$("#mat-list"));
			}
			//
			$(".edit-button").on('click',function() {
				$(location).attr('href','/admin/edit-material/'+$(this).attr('id'));
			});
			//
		})
		.fail(function (data) {
			console.log(data);
		});
	});
	form.submit();
};
//


$adminPage.getListCommand = function (formToken,url,reference) {
	var form = $adminPage.makeForm(formToken,url,reference);

	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType: 'json'
		})
		.done(function (data) {
			// $adminPage.createTableDataCommand(data,['code','date','boutique','status','cash',''],$("#list-command"));
			$adminPage.createTableDataCommand(data,['libelle','quantite','depot','origine','date','edit'],$("#mat-list"));
		})
		.fail(function (data) {
			console.log(data);
		});
	});
	form.submit();
};

$adminPage.getListRavitaillement = function (formToken,url,reference) {
	var form = $adminPage.makeForm(formToken,url,reference);

	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType: 'json'
		})
		.done(function (data) {
			console.log(data);
			$adminPage.createTableDataCommand(data,['libelle','quantite','depot','','date','',''],$("#mat-list"));
		})
		.fail(function (data) {
			console.log(data);
		});
	});
	form.submit();
};


// list des commandes
$adminPage.createTableCommandRow = function (sdata,champs=null,table,linkDetails) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
							// td[j].addClass('uk-animation-shake');
						}


						td[0].append(sdata[i].date);
						td[1].append(sdata[i].item);
						td[2].text(sdata[i].quantite);
						td[3].text(sdata[i].numero_recu);
						td[4].text(sdata[i].status);
						td[5].text(sdata[i].id_commande);
            td[5].addClass('uk-text-bold')
						if(sdata[i].status == "en attente") {
							td[4].addClass('uk-text-danger');
						} else {
							td[4].addClass('uk-text-success');
						}
						// td[3].addClass('uk-text-success')
						var a 	=	$("<a></a>");
						a.attr('href','/user/details-command/'+sdata[i].id_commande);
						a.attr('uk-icon','icon:more');
						a.addClass('uk-button-default uk-border-rounded');
						a.text('details ');
						td[6].append(a);
						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };

		 //

		 $adminPage.createTableRapportVente = function (sdata,champs=null,table) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						// list[i].addClass('uk-animation-scale-up');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
						}
						td[0].text(sdata[i].date);
						td[0].addClass('uk-text-bold');
						td[1].text(sdata[i].recrutement);
						td[1].addClass('uk-text-center');
						td[2].text(sdata[i].migration);
						td[2].addClass('uk-text-center');
						td[3].text(sdata[i].reabonnement);
						td[3].addClass('uk-text-center');
						td[4].text(sdata[i].vendeurs);
						td[5].text(sdata[i].agence);
						td[6].text(sdata[i].comission);
						//
						var span = $("<span></span>");
						var linkEdit = $("<button></button>");linkEdit.addClass('uk-border-rounded');
						linkEdit.attr('type','button');
						linkEdit.addClass('uk-button-primary');

						linkEdit.text('edit')

						span.attr('uk-icon','icon:pencil;ratio:.8');
						linkEdit.append(span);


						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}
						// console.log(list[i]);
						table.append(list[i]);
					}
					$("td:empty").remove();
		 };
		 // /


// historique des commandes pour la logistique
		 $adminPage.createTableCommandRowLogistique = function (sdata,champs=null,table,linkDetails,urlImage=null) {
		 	//
		 	table.html('');
		 	var list=[];

					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");

						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");

						}

						// LIENS POUR LE DETAIL DES VENTES A TRAVERS LE NUMERO CLIENTS
						td[0].append(sdata[i].date);
						td[1].text(sdata[i].vendeurs);
						td[2].text(sdata[i].item);
						td[3].text(sdata[i].quantite);
						td[4].text(sdata[i].parabole_a_livrer);
						td[5].text(sdata[i].status);
						// td[6].text(urlImage);

						if(sdata[i].status == "en attente") {
							td[5].addClass('uk-text-danger');
						} else {
							td[5].addClass('uk-text-success');
						}
						// td[3].addClass('uk-text-success')
						var a 	=	$("<a></a>");
						a.attr('href','#'+sdata[i].id);
						// var btnRavi = a.clone();

						a.attr('uk-icon','icon:check;ratio:.8');
						// btnRavi.attr('uk-icon','icon:plus;ratio:.8');
						// a.attr('target','_blank');
						a.addClass('uk-button-default uk-border-rounded button-confirm');
						a.attr('id',sdata[i].id);
						// btnRavi.addClass('uk-button-default uk-border-rounded');

						a.text('confirm');
            a.attr('href',sdata[i].link);
						// btnRavi.text('ravitailler');
						td[6].append(a);
						// td[9].append(btnRavi);
						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };

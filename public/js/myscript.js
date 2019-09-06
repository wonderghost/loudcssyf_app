
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

//
var $adminPage = {
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
//
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


//

$adminPage.createTableRow = function (sdata,champs=null,table,options,dashOption) {
		 	table.html('');
		 	//
		 	var list=[];
		 	// var champs = ['libelle','quantite','prix_achat','prix_unitaire','photo','edit','details'];
					for(var i=0;i<sdata.length;i++) {
						var td=[]
						list[i]=$("<tr></tr>");
						list[i].addClass('uk-animation-toggle');
						for(var j=0;j<champs.length;j++) {
							td[j] =$("<td></td>");
							td[j].addClass('uk-animation-push');
							// td[j].text(sdata[i].libelle);
							// list[i].append(td[j]);
						}
						td[0].text(sdata[i].libelle);
						td[1].text(sdata[i].quantite);
						td[3].text(sdata[i].prix_achat);
						td[2].text(sdata[i].prix_unitaire);
						//


						$adminPage.getAction(options,td,sdata[i].reference,dashOption);
						// link.attr('href','')

						//
						// var img = $('<img/>');
						// img.addClass('uk-preserve-width'); img.attr('src',"{{asset('uploads/')}}"+'/'+sdata[i].photo);
						var img = $('<span><span/>');img.addClass('uk-icon uk-icon-image item-img');
						img.attr('uk-icon-image','ratio:2');
						img.attr('id',sdata[i].photo);
						// img.attr('style',"background-image : url({{asset('uploads/')}}"+"/"+sdata[i].photo+");");
						// img.attr('src',"{{asset('uploads/')}}"+'/'+sdata[i].photo);
						td[4].append(img);
						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						if(sdata[i].date) {
							td[2].text(sdata[i].date);
							td[3].text(sdata[i].prix_unitaire);
							td[4].text(sdata[i].prix_achat);
							td[5].append(img);
						}

						table.append(list[i]);
					}
					$("td:empty").remove();


		 	//

		 };

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


$adminPage.addItemToCommand  = function (item) {

	item.on('click',function () {
		var reference = $(this).attr('id');
		UIkit.modal.prompt('Entrez la quantite',1).then(function (quantite) {
			// if(typeOf(quantite))
			quantite = parseInt(quantite);
			if(quantite && quantite > 0) {
				// console.log(quantite);
				// return 0;
				var form = $adminPage.makeForm($('#token').val(),'add-item',reference);
				var inputQuantite = $("<input/>");inputQuantite.attr('name','quantite');inputQuantite.val(quantite);
				form.append(inputQuantite);
				form.on('submit',function (e) {
					e.preventDefault();
					$.ajax({
						url : $(this).attr('action'),
						type : $(this).attr('method'),
						data : $(this).serialize(),
						dataType:'json'
					})
					.done(function (data) {

						UIkit.modal.alert(data).then(function() {
							// RECUPERATION DE LA LISTE ACTUALISEE DES PRODUITS
							$adminPage.getDataFormAjax('all',$("#token").val(),'',['libelle','quantite','prix_achat','prix_unitaire','photo','edit','details'],$("#list-item"),2);
						});
					})
					.fail(function (data) {
						console.log(data);
					});
				});

				form.submit();
			} else {
				UIkit.modal.alert('La valeur ne peut etre nulle').then(function() {
					return 0;
				});
			}
			// return 0;
			//

		});
		// console.log(item);
	});
};

$adminPage.getPanier =function (token,url) {
	var form = $adminPage.makeForm(token,url,'');
	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url:$(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType : 'json'
		})
		.done(function (data) {
			$("#panier").text(data.nb);
		})
		.fail(function (data) {
			console.log(data);
		});
	});
	form.submit();
};

$adminPage.detailsPanierOnGerant = function (token,url) {
	var form=$adminPage.makeForm(token,url,'');
	form.on('submit',function(e) {
		e.preventDefault();
		$.ajax({
			url : $(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType : 'json'
		})
		.done(function (data) {
			if(data =="indefinie") {
				UIkit.modal.alert('Aucune Commande en cours').then(function () {
					$(location).attr('href','');
				});
			}
			else {
				$("#load").fadeOut();
				$adminPage.createTableData(data.item_details,['libelle','quantite','prix_unitaire','total'],$("#details-panier"));
				$("#cash").html(data.total_cash);
			}
		})
		.fail(function(data) {
			console.log(data);
		});
	});
	form.submit();
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
							td[j].addClass('uk-animation-shake');
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
						linkEdit.addClass('uk-button-primary edit-button');
						linkEdit.attr('id',sdata[i].reference);

						linkEdit.text('edit')

						span.attr('uk-icon','icon:pencil;ratio:.8');
						linkEdit.append(span);

						td[7].append(linkEdit);


						//
						// var img = $('<span><span/>');img.addClass('uk-icon uk-icon-image item-img');
						// img.attr('uk-icon-image','ratio:2');
						// img.attr('id',sdata[i].photo);

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
							td[j].addClass('uk-animation-shake');
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
							td[j].addClass('uk-animation-shake');
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
						linkDetails.addClass('uk-button-default');

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
			console.log(data);
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

$adminPage.finaliseCommand = function (formToken,url,reference) {
	$(".btn-confirm").on('click',function() {
		var form = $adminPage.makeForm(formToken,url,reference);
		var input =$('<input/>');
		input.attr('type','hidden');
		 input.val($(this).attr('id'));
		input.attr('name','action');
		form.append(input);
		form.on('submit',function (e) {
			e.preventDefault();
			// envoi de la requete ajax
			$.ajax({
				url: $(this).attr('action'),
				type : $(this).attr('method'),
				data : $(this).serialize(),
				dataType:'json'
			})
			.done(function(data) {
				UIkit.modal.alert(data).then(function () {
					$(location).attr('href','');
				});
			})
			.fail(function(data) {
				console.log(data);
			});
		});
		form.submit();
	});
};

// RECHERCHE RAPIDE DES PRODUITS
$adminPage.findItem = function (formToken,url,reference,wordSearch) {
	var form = $adminPage.makeForm(formToken,url,reference);
	var input = $('<input/>'); input.attr('type','hidden'); input.attr('name','wordSearch');
	if(wordSearch) {
		input.val(wordSearch);
	}

	form.append(input);
	form.on('submit',function(e) {
		e.preventDefault();
		// envoi de la requet ajax
		$.ajax({
			url : $(this).attr('action'),
			type : $(this).attr('method'),
			data : $(this).serialize(),
			dataType : 'json'
		})
		.done(function (data) {
			if(data && data == "undefined") {
				$("#list-item").html("<h1>Not found</h1>");
			} else {
				$adminPage.createTableRow(data,['libelle','quantite','prix_achat','prix_unitaire','photo','edit','details'],$("#list-item"));
				$adminPage.showImage();
			}
		})
		.fail(function (data) {
			console.log(data);
		});
	});
	form.submit();
};
// ===

// FILTRER LES COMMANDES PAR DATE
$adminPage.filterByDate = function ($ref) {
	// FILTRAGE PAR DATE
		var bout = $("<input/>");
		 bout.attr('type','hidden');
		 bout.attr('name','ref');
		 bout.val($ref);
		$("#filter-date").append(bout);

		$("#filter-date").on('submit',function (e) {
			e.preventDefault();
			$.ajax({
				url : $(this).attr('action') ,
				type : $(this).attr('method'),
				data : $(this).serialize(),
				dataType : 'json'
			})
			.done(function (data) {
				console.log(data);
				$adminPage.createTableDataCommand(data,['code','date','boutique','status','cash',''],$("#list-command"),true);
			})
			.fail(function (data) {
				console.log(data);
			});
		});
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
						// td[6].text(sdata[i].material);
						// LIENS POUR LE DETAIL DES VENTES A TRAVERS LE NUMERO CLIENTS


						// td[0].addClass('uk-text-bold');
						// td[0].text(sdata[i].clients);

						td[0].append(sdata[i].item);
						td[1].text(sdata[i].quantite);
						td[2].text(sdata[i].numero_recu);
						td[3].text(sdata[i].status);
						if(sdata[i].status == "en attente") {
							td[3].addClass('uk-text-danger');
						} else {
							td[3].addClass('uk-text-success');
						}
						// td[3].addClass('uk-text-success')
						var a 	=	$("<a></a>");
						a.attr('href','/user/details-command/'+sdata[i].id_commande);
						a.attr('uk-icon','icon:more');
						a.addClass('uk-button-default uk-border-rounded');
						a.text('details ');
						td[4].append(a);
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
							td[j].addClass('uk-animation-shake');
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
						td[4].text(sdata[i].numero_recu);
						td[5].text(sdata[i].parabole_a_livrer);
						td[6].text(sdata[i].status);
						var img = $("<img/>");
						var lightbox = $("<div></div>") , a = $("<a></a>");
						a.attr('href',urlImage+'/uploads/'+sdata[i].image);
						a.attr('data-caption','Numero Recu = '+sdata[i].numero_recu);
						lightbox.attr('uk-lightbox','');
						img.addClass('uk-icon-image');
						img.attr('src',urlImage+'/uploads/'+sdata[i].image);
						a.append(img);
						lightbox.append(a);
						td[7].append(lightbox);
						// td[6].text(urlImage);

						if(sdata[i].status == "en attente") {
							td[6].addClass('uk-text-danger');
						} else {
							td[6].addClass('uk-text-success');
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
						td[8].append(a);
						// td[9].append(btnRavi);
						//

						for(var j=0;j<champs.length;j++) {
							list[i].append(td[j]);
						}

						table.append(list[i]);
					}
		 };

// VERIFICATION DE LA VALIDATION
$adminPage.verifValidation = function () {

}
//

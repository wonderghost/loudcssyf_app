var $logistique = {
  makeForm : function (formToken,url,reference = ['']) {
    var form = $("<form></form>");form.attr('action',url);form.attr('method','post');
		var token = $("<input/>");

    for(var i =0 ;i < reference.length ; i++) {

      var ref = $("<input/>");
      ref.attr('type','hidden');
      ref.attr('name','ref-'+i);
      ref.val(reference[i]);
      form.append(ref)

    }


		token.attr('type','hidden');
		token.attr('name','_token');
		token.val(formToken);
		form.append(token);
		form.append(ref);
		return form;
  }
  ,
  ListSerialNumber : function (adminPage,token,url) {

    var form = adminPage.makeForm(token,url,"")

    form.on('submit',function (e) {
      e.preventDefault()
      $.ajax({
        url : url ,
        type : $(this).attr('method'),
        dataType : 'json',
        data : $(this).serialize() ,
      })
      .done(function(data) {
        $logistique.dataList(data,$("#serial-list"))
      })
      .fail(function (data) {
        alert(data.responseJSON.message)
        $(location).attr('href',"")
      })
    })
    form.submit()
  } ,
  ListLivraisonConfirmee : function (adminPage,token,url) {
    var form = adminPage.makeForm(token,url,"")
    form.on('submit',function(e) {
      e.preventDefault()
      $.ajax({
        url : url,
        type : $(this).attr('method'),
        dataType : 'json',
        data : $(this).serialize(),
      })
      .done(function(data) {
        $logistique.dataList(data.list,$("#livraison-confirmee"))
      })
      .fail(function (data) {
        alert(data.responseJSON.message)
        $(location).attr('href',"")
      })
    })
    form.submit()
  }
  ,
  ListLivraison : function (adminPage,token,url,urlFindSerial="") { //Liste de livraison chez le gestionnaire de depot
      var form = adminPage.makeForm(token,url,"")
      form.on('submit',function(e) {
        e.preventDefault()
        $.ajax({
          url : url,
          type : $(this).attr('method'),
          dataType : 'json',
          data : $(this).serialize()
        })
        .done(function (data) {
          $logistique.dataList(data.list,$("#livraison"))
          // ajout du button confirmation
          var confirm = $("<button></button>")
          confirm.text('Confirmer')
          confirm.attr('type','button')
          confirm.attr('uk-toggle',"target : #serials")
          confirm.addClass('uk-button uk-box-shadow-small uk-button-small uk-button-primary uk-border-rounded confirm-button-livraison')
          confirm.attr('id','')
          confirm.attr('uk-icon','icon : check ; ration : 0.7')
          $('.row').append(confirm)

          // ajout des identifiants de livraison
          $('.confirm-button-livraison').each(function(index,element) {
            $(element).attr('id',data.ids[index].id)
            $(element).attr('with-serial',data.ids[index].with_serial)
            $(element).attr('quantite',data.list[index].quantite)
          })
          // action de la confirmation
          $('.confirm-button-livraison').on('click',function () {
            $logistique.actionOnConfirmButton($(this).attr('id'))
            $("#all-serials").html('')
            $("#with-serial").val($(this).attr('with-serial'))
            if($(this).attr("with-serial") == 1) {
              $("#confirm-button-livraison").attr('disabled','')
              $("#quantite").val($(this).attr('quantite'))
              $logistique.SerialInputCols($(this).attr('quantite'),$("#all-serials"))
              $logistique.inputSerialValidate(adminPage,token,urlFindSerial)
            }
          })
        })
        .fail(function(data) {
          alert(data.responseJSON.message)
          $(location).attr('href',"")
        })
      })
      form.submit()
  }
  ,
  ListLivraisonByVendeurs : function (adminPage,token,url,ref) { //List de livraison par vendeur
    var form = adminPage.makeForm(token,url,ref)
    form.on('submit',function(e){
      e.preventDefault()
      $.ajax({
        url : url ,
        type : 'post',
        dataType : 'json',
        data : $(this).serialize()
      })
      .done(function(data) {
        $logistique.dataList(data,$("#livraison"))
      })
      .fail(function(data){
        console.log(data)
      })
    })
    form.submit()
  }
,
  actionOnConfirmButton : function (id) {
    $("#livraison-id").val(id)
  }
,
  dataList : function (data,content) {

    var row = [] , cols = [] ;
    content.html('')
    data.forEach(function (element,index) {
      row[index] = $("<tr></tr>")
      var count = 0 ;
      for(var prop in element) {
        cols [count] = $("<td></td>")
        cols[count].text(element[prop])
        cols[count].addClass('col')
        row[index].append(cols[count])
        row[index].addClass('row')
        content.append(row[index])
        count++
      }
    })

  }
  ,
  SerialInputCols : function (quantite,parent) {

    var inputs = [];
    parent.html('');
    // creation des champs de saisi pour S/N
    for(var i=0; i < quantite ; i++) {
      inputs[i] = $("<input/>");
      var div = $("<div></div>");

      inputs[i].attr('type','text');

      inputs[i].attr('required','');
      inputs[i].attr('name','serial-number-'+(i+1));
      inputs[i].addClass('uk-input uk-margin-small serial-input uk-border-rounded');
      inputs[i].attr('placeholder','Serial Number-'+(i+1));
      div.append(inputs[i]);
      parent.append(div);
    }

  }
,
inputSerialValidate : function (adminPage,token,url,actionButton = $('#confirm-button-livraison')) {
  // @@@@####
  var tabSerial = [];
  // pendant le focus
  $(".serial-input").on('focus',function () {

    var _serialNow = $(this);
    if($.trim(_serialNow.val()) !== "" && $.inArray($.trim(_serialNow.val()),tabSerial) > -1) {
      // existe dans le tableau
      tabSerial.splice(tabSerial.indexOf($.trim(_serialNow.val())),1);
    }
  });
  // apres le focus (verifier le duplicat et l'existence en base de donnees )
  $(".serial-input").on('blur',function () {
    // '/user/add-material/find-serial-number'
    var form = adminPage.makeForm(token,url,$(this).val());
    var serialNow = $(this);


    // verification de l'exitence dans la base de donnees | envoi de la requete ajax
    form.on('submit',function(e) {
      e.preventDefault();
      $.ajax({
        url : $(this).attr('action'),
        type : $(this).attr('method'),
        data : $(this).serialize(),
        dataType : 'json'
      })
      .done(function (data) {
        if(data && data !== 'success') {
          // Erreur genere
          UIkit.notification({
            message : data,
            status : 'danger',
            timeout : 800
          });
          actionButton.attr('disabled','')
        } else {

          // le numero n'existe pas dans la base de donnees
          // verifier s'il n'existe pas de duplicat
          actionButton.removeAttr('disabled')
          if($.inArray($.trim(serialNow.val()),tabSerial) == -1) {
            // la valeur n'existe pas dans le tableau , il faut l'ajouter
            tabSerial.push($.trim(serialNow.val()));
          } else {
            // la valeur existe dans le tableau
            if($.trim(serialNow.val()) == "")  {
              // UIkit.modal.alert("Ce champs ne peut etre vide!");
              alert("Ce champs ne peut etre vide!")
              actionButton.attr('disabled','')
              return 0;
            }
            UIkit.modal.alert("Duplicat de numero!").then(function () {
              actionButton.attr('disabled','')
              tabSerial.splice(tabSerial.indexOf($.trim(serialNow.val())),1);
              $('.serial-input').each(function (index, element) {
                  if($.trim($(element).val()) == $.trim(serialNow.val())) {
                    $(element).val('');
                  }
              });
            });
          }

        }

      })
      .fail(function(data) {
        alert(data.responseJSON.message)
        $(location).attr('href',"")
      });
    });
    form.submit();
  });
  // ####
},
CheckSerial : function (token,url,actionButton = $('#confirm-button-livraison'),username = '') {
  // @@@@####
  var tabSerial = [];
  // pendant le focus
  $(".serial-input").on('focus',function () {
    var _serialNow = $(this);
    if($.trim(_serialNow.val()) !== "" && $.inArray($.trim(_serialNow.val()),tabSerial) > -1) {
      // existe dans le tableau
      tabSerial.splice(tabSerial.indexOf($.trim(_serialNow.val())),1);
    }

  });
  // apres le focus (verifier le duplicat et l'existence en base de donnees )
  $(".serial-input").on('blur',function () {
    // '/user/add-material/find-serial-number'
    var form = $logistique.makeForm(token,url,[$(this).val(),username]);
    var serialNow = $(this);


    // verification de l'exitence dans la base de donnees | envoi de la requete ajax
    form.on('submit',function(e) {
      e.preventDefault();
      $.ajax({
        url : $(this).attr('action'),
        type : $(this).attr('method'),
        data : $(this).serialize(),
        dataType : 'json'
      })
      .done(function (data) {
        if(data && data !== 'success') {
          // Erreur genere
          UIkit.notification({
            message : data,
            status : 'danger',
            timeout : 800
          });
          actionButton.attr('disabled','')
        } else {

          // le numero n'existe pas dans la base de donnees
          // verifier s'il n'existe pas de duplicat
          actionButton.removeAttr('disabled')
          if($.inArray($.trim(serialNow.val()),tabSerial) == -1) {
            // la valeur n'existe pas dans le tableau , il faut l'ajouter
            tabSerial.push($.trim(serialNow.val()));
          } else {
            // la valeur existe dans le tableau
            if($.trim(serialNow.val()) == "")  {
              // UIkit.modal.alert("Ce champs ne peut etre vide!");
              alert("Ce champs ne peut etre vide!")
              actionButton.attr('disabled','')
              return 0;
            }
            UIkit.modal.alert("Duplicat de numero!").then(function () {
              actionButton.attr('disabled','')
              tabSerial.splice(tabSerial.indexOf($.trim(serialNow.val())),1);
              $('.serial-input').each(function (index, element) {
                  if($.trim($(element).val()) == $.trim(serialNow.val())) {
                    $(element).val('');
                  }
              });
            });
          }

        }

      })
      .fail(function(data) {
        alert(data.responseJSON.message)
        $(location).attr('href',"")
      });
    });
    form.submit();
  });
  // ####
}
,
// liste des livraisons restantes pour validation
listLivraisonToConfirm : function (adminPage, token ,url) {
  var form = adminPage.makeForm(token,url,"")
  form.on('submit',function(e){
    e.preventDefault()
    $.ajax({
      url : url ,
      type : $(this).attr('method'),
      dataType : 'json',
      data : $(this).serialize()
    }).
    done(function (data) {
      $logistique.dataList(data.all,$("#livraison-to-validate"))
      // ajout du button validation
      var validate = $("<button></button>") , details = $("<button></button>")

      validate.text('valider')
      validate.attr('type','button')
      validate.addClass('uk-button uk-button-small uk-box-shadow-small uk-button-primary uk-border-rounded validate-button-livraison uk-text-capitalize uk-margin-right')
      validate.attr('id','')
      validate.attr('uk-toggle','target : #modal-livraison-validate')

      details.text('details')
      details.attr('uk-toggle','target : #modal-livraison-detail')
      details.addClass('uk-button uk-button-small uk-box-shadow-small uk-text-capitalize uk-button-default uk-border-rounded detail-livraison')
      // ajout du button details
      validate.attr('uk-icon',"icon : check ; ratio : 0.7")
      details.attr('uk-icon',"icon : more ; ratio : 0.7")

      $('#livraison-to-validate .row').append(validate)
      $('#livraison-to-validate .row').append(details)

      //
      $('.validate-button-livraison').each(function(index,element) {
        $(element).attr('id',data.ids[index].id)
      })
      // action au click sur le boutton validate
      $(".validate-button-livraison").on('click',function () {
        // console.log($(this).attr('id'))
        let username = this.parentNode
        $("#vendeur-name").html(username.firstChild.nextSibling.innerText)
        // ####
        $logistique.getListSerialNumberOnValidation(token,"/user/commandes/get-serial-validation",$(this).attr('id'))
        //
        $("#id_livraison").val($(this).attr('id'))
      })

      // ajout du fichier au click sur le bouton Details

      $('#livraison-to-validate .detail-livraison').each(function (index , element) {
        $(element).attr('filename',data.file[index].filename)
      })

      $('.detail-livraison').on('click',function(e) {
        $("#file-link").attr('href',"/livraison_serial_files/"+$(this).attr('filename'))
        $("#file-link").attr('download',$(this).attr('filename'))
      })
    })
    .fail(function (data){
      alert(data.responseJSON.message)
      $(location).attr('href',"/")
    })
  })
  // envoi du formulaire pour traitement de la requete
  form.submit()
}
,
// liste des livraison deja validee
listLivraisonValidee : function (token , url) {
  var form = $adminPage.makeForm(token , url, "")
  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $logistique.dataList(data.all,$("#livraison-validee"))

      var validate = $("<button></button>") , details = $("<button></button>")

      details.text('details')
      details.attr('uk-toggle','target : #modal-livraison-detail')
      details.addClass('uk-button uk-button-small uk-box-shadow-small uk-button-default uk-border-rounded detail-livraison')
      // ajout du button details

      details.attr('uk-icon',"icon : more ; ratio : 0.7")


      $('#livraison-validee .row').append(details)

      // ajout du fichier au click sur le bouton Details

      $('#livraison-validee .detail-livraison').each(function (index , element) {
        $(element).attr('filename',data.file[index].filename)
      })

      $('.detail-livraison').on('click',function(e) {
        $("#file-link").attr('href',"/livraison_serial_files/"+$(this).attr('filename'))
        $("#file-link").attr('download',$(this).attr('filename'))
      })
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"/")
    })
  })
  form.submit()
}
,
getListSerialNumberOnValidation : function (token , url , ref) { //recuperation de la liste des numero de serie lors de la validation pour l'affichage
  var form = $adminPage.makeForm(token , url ,ref)
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : url,
      type : $(this).attr('method'),
      data : $(this).serialize(),
      dataType : 'json'
    })
    .done(function (data) {
      $logistique.dataList(data,$("#serial-validate-list"))
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"/")
    })
  })
  form.submit()
},
// liste des numero de serie par vendeur
listSerialByVendeur : function (token,url,ref) {
  var form = $adminPage.makeForm(token,url,ref)

  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url,
      type : $(this).attr('method'),
      data : $(this).serialize(),
      dataType : 'json'
    })
    .done(function (data) {
      $logistique.dataList(data,$("#serials-vendeurs"))
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"")
    })
  })

  form.submit()
}
,
  getSoldeVendeur : function (token ,url) {
    var form = $adminPage.makeForm(token ,url ,"")
    form.on('submit',function(e) {
      e.preventDefault()
      $.ajax({
        url : url ,
        type : 'post',
        data : $(this).serialize(),
        dataType : 'json'
      })
      .done(function (data) {
        $logistique.dataList(data,$("#solde-vendeur"))
      })
      .fail(function (data) {
        alert(data.responseJSON.message)
        $(location).attr('href',"/")
      })
    })
    form.submit()
}
,
// recuperation des soldes vendeurs chez le gcga
  getSoldeVendeurCredit : function (token , url ) {
    var form = $adminPage.makeForm(token , url)
    form.on('submit',function(e) {
      e.preventDefault()
      $.ajax({
        url : url,
        type : 'post',
        dataType : 'json',
        data : $(this).serialize()
      })
      .done(function (data) {
        $logistique.dataList(data,$("#solde-vendeur"))
        // suppression de la colonne rex
        // $('#solde-vendeur .row .col:last-child').remove()
      })
      .fail(function(data) {
        alert(data.responseJSON.message)
        $(location).attr("href",'/')
    })
  })
  form.submit()
}
,
// recuperation de toutes les commandes chez gcga
  getCommandForCga : function (token , url) {
    var form = $adminPage.makeForm(token , url)
    form.on('submit',function(e) {
      e.preventDefault()
      $.ajax({
        url : url ,
        type : 'post',
        dataType : 'json',
        data : $(this).serialize()
      })
      .done(function (data) {
        $logistique.dataList(data.unvalidated,$("#unvalidated"))
        $logistique.dataList(data.validated,$("#validated"))

        $logistique.organizeCommandGcga(data.unvalidated)
        $logistique.organizeCommandGcga(data.validated,'validated')

        // click sur la validation
        $(".validate-button").on('click',function () {
          var row = $(this).parent().parent()
          $("#validation-montant").text(row.children().eq(2).text())
          $("#validation-vendeur").text(row.children().eq(1).text())
          $("#validation-commande").val($(this).attr('id'))
          $("#validation-type-commande").val(row.children().eq(3).text())
          //
        })
      })
      .fail(function (data) {
        alert(data.responseJSON.message)
        $(location).attr('href','/')
      })
    })
    form.submit()
  }
  ,
  organizeCommandGcga : function (data,table = 'unvalidated',container='unvalidated',secondContainer="validated") {
    data.forEach(function (element , index) {

      var div = $("<div></div>") , diva = $("<a></a>")
      div.attr('uk-lightbox','')
      diva.addClass('uk-button-default uk-border-rounded uk-box-shadow-small')
      diva.attr('href','/uploads/'+element.recu)
      diva.attr('data-caption',element.numero_recu)
      diva.text('recu')
      div.html(diva)

      var valider = $("<a></a>") , td = $("<td></td>") , retd = $("<td></td>")
      valider.addClass('uk-button uk-button-small uk-box-shadow-small uk-button-primary uk-border-rounded uk-box-shadow-small validate-button')
      valider.attr('uk-toggle','target: #modal-validation')
      valider.attr('id',element.id)
      valider.text('validez')
      td.html(valider)

      retd.html(div)
      // console.log(container)
      // console.log(secondContainer)
      if(table == 'unvalidated') {

        $("#"+container+" .row:eq("+index+")").children().eq(7).remove()
        $("#"+container+" .row").eq(index).append(retd)
        $("#"+container+" .row").eq(index).append(td)
        $("#"+container+" .row:eq("+index+")").children().eq(0).remove()
        $("#"+container+" .row:eq("+index+")").children().eq(4).addClass('uk-text-danger')

      } else {

        $("#"+secondContainer+" .row").eq(index).append(retd)
        $("#"+secondContainer+" .row:eq("+index+")").children().eq(7).remove()
        $("#"+secondContainer+" .row:eq("+index+")").children().eq(0).remove()
        $("#"+secondContainer+" .row:eq("+index+")").children().eq(4).addClass('uk-text-success')
      }

    })
  }
  ,

transactionDashboardView : function (token,url) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      // var myChart = new Chart($("#myChart"),{
      //   type : 'line',
      //   data : [10,20]
      // })
      console.log(data)
    })
    .fail(function (data) {
      console.log(data)
    })
  })
  form.submit()
}
,
getListRapportVente : function (token , url) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function(e) {
    e.preventDefault()

    $.ajax({
      url : url,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {

      $("#commission-jour").val(data.commission)
      if(data.recrutement) {
        $logistique.dataList(data.recrutement,$("#recrutement-list"))
      }
      if(data.reabonnement){
        $logistique.dataList(data.reabonnement,$("#reabonnement-list"))
      }
      if(data.migration) {
        $logistique.dataList(data.migration,$("#migration-list"))
      }
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','')
    })
  })
  form.submit()
}
,

sendPromoForm : function (form = $("#promo-form")) {

  form.on('submit',function (e) {
    e.preventDefault()
    UIkit.modal($("#modal-promo")).hide();
    $.ajax({
      url : $(this).attr('action') ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {

      UIkit.modal($("#modal-promo")).hide();
      UIkit.modal.alert("Success!").then(function () {
         $("#loader").hide()
         $(location).attr('href','')
      })
    })
    .fail(function (data) {
      UIkit.modal($("#modal-promo")).show();
      $("#loader").hide()
      if(data.responseJSON.errors) {
        var dataErrors = data.responseJSON.errors

        for(element in dataErrors){
          UIkit.notification({
              message: dataErrors[element][0],
              status: 'danger',
              timeout: 3000
          });
        }
      } else {
          UIkit.notification({
            message : data.responseJSON,
            status : 'danger',
            timeout : 3000
          })
      }
    })
  })
}
,

getPromo : function (token, url,urlCancel) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function (e){
    e.preventDefault()
    $.ajax({
      url : url,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      if(data !== 'fail') {
        $("#new-promo").hide(200)
        $('.promo-inputs').attr('disabled','')
        $("#edit-submit").attr('disabled','')
        $("#id-input").val(data.id)
        $("#debut-input").val(data.debut)
        $("#fin-input").val(data.fin)
        $("#intitule-input").val(data.intitule)
        $("#subvention-input").val(data.subvention)
        $("#description-input").val(data.description)

        //  Interruption de la promo
        $("#delete-button").on('click',function () {
          $logistique.interruptionPromo(token,urlCancel,$("#id-input").val())
        })
      } else {
        $("#actif-promo").hide(200)
      }
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','')
    })
  })
  form.submit()
}
,
interruptionPromo : function (token , url,idPromo) {
  var form = $logistique.makeForm(token,url,[idPromo])
  $logistique.sendPromoForm(form)
  UIkit.modal.confirm("Etes vous sur?").then(function() {
    form.submit()
  }, function () {
      $(location).attr('href','')
  });
},
// DASHBOARD CONFIGURATION
dataChart : function (container, data = [50,20] ,labels =["Element1","Element2"],  background = ["#123","#098"]) {
  var myChart = new Chart(container,{
    "type":"doughnut",
    "data":{
      "labels":labels,
      "datasets":[{
        "data": data,
        "backgroundColor": background
      }]
    }
  });
}
,
dataBarChart : function (container,labels=["El1","El2","El3"],data =[44,422,77],background = ["#109","#554","#981"]) {
  var myChart = new Chart(container,{
    "type":"bar",
    "data":{
      "labels": labels,
      "datasets":[{
        "label":"",
        "data": data,
        "fill":false,
        "backgroundColor": background,
        "borderColor": background,
        "borderWidth":1
      }]
    },
      "options":{

      }
    });
}
,
dataLineChart : function () {
  var myChart = new Chart(document.getElementById("chartjs-0"),{
    "type":"line",
    "data":{"labels":["January","February","March","April","May","June","July"],
    "datasets":[{
      "label":"My First Dataset",
      "data":[65,59,80,81,56,55,40],
      "fill":false,
      "borderColor":"rgb(75, 192, 192)",
      "lineTension":0.1
    }]
    },
    "options":{}});
}
,
userChart : function (token , url ) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function(e){
    e.preventDefault()
    $.ajax({
      url : $(this).attr('action'),
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $logistique.dataChart($("#userChart"),[data.v_standart,data.v_da,0],["Nos vendeurs","DA","Clients"])
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"")
    })
  })
  form.submit()
}
,
depotChart : function (token , url ) {
  var form = $logistique.makeForm(token , url )
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : $(this).attr('action'),
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {

      var labels = [] , datas = []
      data.forEach(function (element) {
        labels.push(element.depot)
        datas.push(element.quantite_materiel)
      })
      $logistique.dataBarChart($("#depotChart"),labels,datas)
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"")
    })
  })
  form.submit()
}
,


// recuperer la liste des commandes pour le compte admin
getListCommandes : function (token , url) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : $(this).attr('action'),
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {

      $logistique.dataList(data.unconfirmed,$("#non-confirm-commande"))
      $logistique.dataList(data.confirmed,$("#confirm-commande"))

      $logistique.dataList(data.livraison_unvalidate.original.all,$("#livraison-unvalidate"))
      $logistique.dataList(data.livraison_validate.original.all,$("#livraison-validate"))

      details = $("<button></button>")

      details.text('details')
      details.attr('uk-toggle','target : #modal-livraison-detail')
      details.addClass('uk-button-default uk-border-rounded detail-livraison')
      // ajout du button details

      details.attr('uk-icon',"icon : more ; ratio : 0.7")


      $('#livraison-validate .row').append(details)

      // ajout du fichier au click sur le bouton Details

      $('#livraison-validate .detail-livraison').each(function (index , element) {
        $(element).attr('filename',data.livraison_validate.original.file[index].filename)
      })

      $('.detail-livraison').on('click',function(e) {
        $("#file-link").attr('href',"/livraison_serial_files/"+$(this).attr('filename'))
        $("#file-link").attr('download',$(this).attr('filename'))
      })

      // ///

      $(".command-table .col:last-child").remove()
      $(".command-table .col:last-child").remove()

      $('.command-table .col:last-child').each(function (index , element) {
        if(element.innerText == 'en attente') {
          $(element).addClass('uk-text-danger')
        }
        else {
          $(element).addClass('uk-text-success')
        }
      })

    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
}

// list des commandes pour l 'administrateur
,
getCommandes : function (token , url) {
  var form = $logistique.makeForm(token,url)
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $logistique.dataList(data.unvalidated,$("#credit-unvalidate-commande"))
      $logistique.dataList(data.validated,$("#credit-validate-commande"))

      $logistique.organizeCommandGcga(data.unvalidated,'unvalidated','credit-unvalidate-commande','')
      $logistique.organizeCommandGcga(data.validated,'validated','','credit-validate-commande')

    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
},

// fonction de recherche rapide

ajaxSearch : function (token , url,wordSearch) {
  var form = $logistique.makeForm(token , url ,[wordSearch])
  form.on('submit',function (e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      data : $(this).serialize(),
      dataType : 'json'
    })
    .done(function (data) {
      console.log(data)
      $logistique.dataList(data,$("#list-users"))
      $logistique.organizeUsersList(data)
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
}
,
// LIST DES UTILISATEURS
usersList : function (token , url , userType = "") {
  var form = $logistique.makeForm(token, url ,[userType])
  form.on('submit',function (e) {
    $("#table-loader").show(200)
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      data : $(this).serialize(),
      dataType : 'json'
    })
    .done(function (data){
      $("#table-loader").hide(200)
      $logistique.dataList(data,$("#list-users"))
      $logistique.organizeUsersList(data)

      $('.state-action').on('click',function (e){
        var user = $(this).attr('title')

        if($(this).attr('data-state') == 'blocked') {
          UIkit.modal.confirm("Etes vous sur de vouloir debloquer cet utilisateur ?").then(function () {
            $adminPage.unblockUser(token,"/admin/unblock-user",user);
          })
        } else {
          UIkit.modal.confirm("Etes vous sur de vouloir bloquer cet utilisateur ?").then(function () {
            $adminPage.blockUser(token,"/admin/block-user",user);
          })
        }

      })

      $('.reset-action').on('click',function (e) {
        $("#users").html($(this).attr('data-localisation'))
        $("#user-id").val($(this).attr('data-username'))
      })
      })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
}
,
organizeUsersList : function (data) {
  // ajout des bouttons editer et supprimer

  data.forEach(function (element ,index) {
    var edit = $("<a></a>") , stateButton = $("<a></a>") , reset = $("<a></a>")
    edit.attr('href','edit-users/'+element.username)
    edit.attr('uk-icon','icon : pencil')
    edit.addClass('uk-button uk-margin-small uk-margin-small-left uk-button-small uk-button-primary uk-border-rounded uk-text-capitalize uk-box-shadow-small')
    edit.text("Editer")

    $("#list-users .row:eq("+index+")").append(edit)

    stateButton.addClass('uk-button uk-margin-small uk-margin-small-left uk-button-small uk-border-rounded uk-box-shadow-small uk-text-capitalize state-action')
    stateButton.attr('title',element.localisation)
    stateButton.attr('data-state',element.status)

    reset.addClass("uk-button uk-margin-small uk-margin-small-left uk-button-small uk-button-default uk-border-rounded uk-box-shadow-small uk-text-capitalize reset-action")
    reset.attr('uk-icon','icon : refresh')
    reset.attr('href',"#modal-confirm-password")
    reset.attr('uk-toggle','')
    reset.attr('title',element.localisation)
    reset.attr('data-username',element.username)
    reset.attr('data-localisation',element.localisation)
    reset.text("Reset")

    $("#list-users .row:eq("+index+")").append(reset)


    if(element.status == "unblocked") {
      $("#list-users .row:eq("+index+") .col").eq(5).addClass('uk-text-success')
      stateButton.addClass('uk-button-danger')
      stateButton.text("blocker")
      stateButton.attr('uk-icon','icon : lock')
      $("#list-users .row:eq("+index+")").append(stateButton)
    } else {
      $("#list-users .row:eq("+index+") .col").eq(5).addClass('uk-text-danger')
      stateButton.addClass('uk-button-default')
      stateButton.attr('style','background : rgba(100,200,100) ; color : #fff')
      stateButton.text('debloquer')
      stateButton.attr('uk-icon','icon : unlock')
      $("#list-users .row:eq("+index+")").append(stateButton)
    }

  })
},
// STATISTIQUE DE VENTES
venteChart : function (token , url , vendeur) {
  var form = $logistique.makeForm(token , url , [vendeur])
  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      var datas = [data.recrutement,data.reabonnement,data.migration]
      $logistique.dataBarChart($("#resume-vente"),['recrutement','reabonnement','migration'],datas)
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"/")
    })
  })
  form.submit()
},
// TOUTES LES TRANSACTIONS CHEZ LE RECOUVREUR
allTransactionForRecouvrement : function (token , url ,vendeurs = "all" , state = "all") {
  var form = $logistique.makeForm(token,url,[vendeurs,state])
  form.on('submit',function (e) {
    $('.loader').show(200)
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $('.loader').hide(200)
      $logistique.dataList(data,$("#all-transactions"))
      data.forEach(function (element , index) {
        if(element.status == 'non effectue') {
          $("#all-transactions .row:eq("+index+") .col").eq(5).addClass('uk-text-danger uk-text-center')
        } else {
          $("#all-transactions .row:eq("+index+") .col").eq(5).addClass('uk-text-success uk-text-center')
        }
      })
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
}
,
getMontantDuRecouvrement : function (token , url , vendeur) {
  var form = $logistique.makeForm(token,url , [vendeur])
  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $("#montant-du").val(lisibilite_nombre(data))
      $("#montantdu").val(data)
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
},
allRecouvrement : function (token , url , vendeurs) {
  var form = $logistique.makeForm(token,url , [vendeurs])
  form.on('submit',function(e) {
    $('.loader').show(200)
    e.preventDefault()
    $.ajax({
      url : url ,
      type : "post",
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $('.loader').hide(200)
      $logistique.dataList(data,$("#all-recouvrement"))
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href',"/")
    })
  })

  form.submit()
}
,
notificationList : function (token , url ,vendeur , urlLuAction = "") {
  var form = $logistique.makeForm(token , url ,[vendeur])
  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : 'post',
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $("#notification-count").html(data.count)
      if(data.count !== 0 ) {
        $logistique.organizeNotification(data.all,$("#notification-list"))
      } else {
        $("#notification-list").html("<span class='uk-flex uk-flex-center uk-text-meta'>Aucune Notification !<span>")
      }

      $logistique.organizeNotification(data.all_unread,$("#notification-unread"))
      $logistique.organizeNotification(data.all_read,$("#notification-read"),'read')
      // LU ACTION
      $('.lu-action').on('click',function (e) {
        $logistique.changeStateOfNotification(token , urlLuAction,$(this).attr('data-notification'))
        $(this).parent().hide(200)
      })
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
}
,
// ORGANISATION DE LA NOTIFICATION
organizeNotification : function (data , content , state = 'unread') {
  // var
  var titles = [] , descriptions = [] , container = [] , luButton = [] , date = [] , deleteButton = []
  if(data.length > 0) {
    content.html('')
  } else {
    content.html("<span class='uk-flex uk-flex-center uk-text-meta'>Aucune Notification !<span>")
  }
  data.forEach(function (element , index) {
    titles[index] = $("<dt></dt>")
    descriptions[index] = $("<dd></dd>")

    date[index] = $("<div></div>")
    luButton[index] = $("<a></a>")
    deleteButton[index] = $("<a></a>")
    date[index].addClass('uk-text-meta')

    descriptions[index].addClass('uk-text-small')

    luButton[index].addClass('uk-button-link uk-button uk-text-capitalize uk-flex uk-flex-right uk-icon-link lu-action')
    luButton[index].attr('uk-icon','icon : check ; ratio : .8')
    luButton[index].attr('data-notification',element.id)
    deleteButton[index].addClass('uk-button-link uk-button uk-text-capitalize uk-flex uk-flex-right uk-icon-link delete-action')
    deleteButton[index].attr('data-notification',element.id)
    deleteButton[index].attr('style','color : red')
    deleteButton[index].attr('uk-icon','icon : close ; ratio : .8')


    luButton[index].text('Vu')
    deleteButton[index].text('Supprimer')
    date[index].text(element.date)
    titles[index].text(element.titre)
    descriptions[index].text(element.description)
    descriptions[index].append(date[index])
    if(state == 'unread') {
      descriptions[index].append(luButton[index])
    } else {
      descriptions[index].append(deleteButton[index])
    }

    content.append(titles[index])
    content.append(descriptions[index])
  })
}
,
changeStateOfNotification : function (token , url , idNotification) {
  var form = $logistique.makeForm(token , url , [idNotification])
  form.on('submit',function(e) {
    e.preventDefault()
    $.ajax({
      url : url ,
      type : "post",
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data){
      if(data == "done") {
        UIkit.notification({
            message: 'Success!',
            status: 'success',
            pos: 'top-center',
            timeout: 1000
        });
      }
    })
    .fail(function (data) {
      alert(data.responseJSON.message)
      $(location).attr('href','/')
    })
  })
  form.submit()
},
// envoi de la demande de paiement des commission
payCommission : function () {
  $("#pay-commission-form").on('submit',function (e) {
    $("#loader").show(200)
    UIkit.modal($("#paiement-commission")).hide(200)
    e.preventDefault()
    $.ajax({
      url : $(this).attr('action'),
      type : $(this).attr('method'),
      dataType : 'json',
      data : $(this).serialize()
    })
    .done(function (data) {
      $("#loader").hide(200)
      UIkit.modal.alert("Success!").then(function () {
        $(location).attr('href','')
      })
    })
    .fail(function (data) {
      console.log(data.responseJSON)
      // return 0
      if(data.responseJSON && data.responseJSON.errors) {
        $("#loader").hide(200)
        UIkit.modal($("#paiement-commission")).show(200)
        for (var element in data.responseJSON.errors) {
          UIkit.notification({
            message: data.responseJSON.errors[element],
            status: 'danger',
            pos: 'top-center',
            timeout: 2000
          })
        }
      } else {
        $("#loader").hide(200)
        UIkit.modal($("#paiement-commission")).show(200)
        UIkit.notification({
          message: data.responseJSON,
          status: 'danger',
          pos: 'top-center',
          timeout: 2000
        })
    }
    })
  })
}


}

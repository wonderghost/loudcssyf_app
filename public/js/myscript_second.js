
var $logistique = {
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
          confirm.addClass('uk-button-primary uk-border-rounded confirm-button-livraison')
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
      inputs[i].addClass('uk-input uk-margin-small serial-input');
      inputs[i].attr('placeholder','Serial Number-'+(i+1));
      div.append(inputs[i]);
      parent.append(div);
    }

  }
,
inputSerialValidate : function (adminPage,token,url) {
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
          $("#confirm-button-livraison").attr('disabled','')
        } else {

          // le numero n'existe pas dans la base de donnees
          // verifier s'il n'existe pas de duplicat
          $("#confirm-button-livraison").removeAttr('disabled')
          if($.inArray($.trim(serialNow.val()),tabSerial) == -1) {
            // la valeur n'existe pas dans le tableau , il faut l'ajouter
            tabSerial.push($.trim(serialNow.val()));
          } else {
            // la valeur existe dans le tableau
            if($.trim(serialNow.val()) == "")  {
              // UIkit.modal.alert("Ce champs ne peut etre vide!");
              alert("Ce champs ne peut etre vide!")
              $("#confirm-button-livraison").attr('disabled','')
              return 0;
            }
            UIkit.modal.alert("Duplicat de numero!").then(function () {
              $("#confirm-button-livraison").attr('disabled','')
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
      validate.addClass(' uk-button-primary uk-border-rounded validate-button-livraison uk-margin-right')
      validate.attr('id','')
      validate.attr('uk-toggle','target : #modal-livraison-validate')

      details.text('details')
      details.attr('uk-toggle','target : #modal-livraison-detail')
      details.addClass('uk-button-default uk-border-rounded detail-livraison')
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
      $(location).attr('href',"")
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
      details.addClass('uk-button-default uk-border-rounded detail-livraison')
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
  organizeCommandGcga : function (data,table = 'unvalidated') {
    data.forEach(function (element , index) {

      var div = $("<div></div>") , diva = $("<a></a>")
      div.attr('uk-lightbox','')
      diva.addClass('uk-button-default uk-border-rounded uk-box-shadow-small')
      diva.attr('href','/uploads/'+element.recu)
      diva.attr('data-caption',element.numero_recu)
      diva.text('recu')
      div.html(diva)

      var valider = $("<a></a>") , td = $("<td></td>") , retd = $("<td></td>")
      valider.addClass('uk-button-primary uk-border-rounded uk-box-shadow-small validate-button')
      valider.attr('uk-toggle','target: #modal-validation')
      valider.attr('id',element.id)
      valider.text('validez')
      td.html(valider)

      retd.html(div)

      if(table == 'unvalidated') {

        $("#unvalidated .row:eq("+index+")").children().eq(7).remove()
        $("#unvalidated .row").eq(index).append(retd)
        $("#unvalidated .row").eq(index).append(td)
        $("#unvalidated .row:eq("+index+")").children().eq(0).remove()

      } else {

        $("#validated .row").eq(index).append(retd)
        $("#validated .row:eq("+index+")").children().eq(7).remove()
        $("#validated .row:eq("+index+")").children().eq(0).remove()

      }

    })
  }



}

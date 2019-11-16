
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

  ListLivraison : function (adminPage,token,url) { //Liste de livraison chez le gestionnaire de depot
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
              $logistique.SerialInputCols($(this).attr('quantite'),$("#all-serials"))
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

}

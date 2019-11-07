
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

  ListLivraison : function (adminPage,token,url) {
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
          $logistique.dataList(data,$("#livraison"))
          // ajout du button confirmation
          var confirm = $("<a></a>")
          confirm.text('Confirmer')
          confirm.addClass('uk-button-primary uk-border-rounded ')
          confirm.attr('uk-icon','icon : check ; ration : 0.7')
          $('.row').append(confirm)
        })
        .fail(function(data) {
          console.log(data)
          alert(data.responseJSON.message)
          $(location).attr('href',"")
        })
      })
      form.submit()
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
}

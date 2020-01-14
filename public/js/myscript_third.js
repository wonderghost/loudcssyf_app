
var $chatService = {
  openChatBox : function (url) {
    $("#chat-button").on('click',function () {
      $.get(url,function(data) {
        $("#chat-user-list").html('')
        data.forEach(function (element , index) {
          var li = $("<li></li>") , username = $("<a></a>") , icon = $("<span></span>")
          icon.addClass('uk-button-default uk-button-small uk-padding-remove uk-border-circle')
          icon.attr('uk-icon','icon : user')
          li.addClass('')
          username.text(element.localisation)
          username.addClass('uk-button uk-button-small uk-padding-remove uk-text-capitalize uk-text-bold ')
          li.append(icon)
          li.append(username)
          $("#chat-user-list").append(li)
        })
      },'json')
    })
  }
  ,


}

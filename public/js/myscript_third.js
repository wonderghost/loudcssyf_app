
var $chatService = {
  openChatBox : function (url) {
    $(".chat-buttons").on('click',function () {
      $.get(url,function(data) {
        $("#chat-user-list").html('')
        $("#chat-user-list-responsive").html('')
        data.forEach(function (element , index) {
          var li = $("<li></li>") , username = $("<a></a>") , icon = $("<span></span>")
          icon.addClass('uk-margin-small')
          icon.attr('uk-icon','icon : user ; ratio : .8')
          li.addClass('')
          username.text(element.localisation)
          username.addClass('uk-button uk-button-small uk-padding-remove uk-text-capitalize uk-text-bold ')
          li.append(icon)
          li.append(username)
          $(".chat-user-lists").append(li)
        })
      },'json')
    })
  }
  ,


}

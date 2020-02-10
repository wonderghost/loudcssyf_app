import Echo from  'laravel-echo'

window.Pusher = require('pusher-js')

window.Echo = new Echo({
  broadcaster : 'pusher',
  key : 'b476b70191cad8842947',
  cluster : 'eu',
  forceTLS : true
})
//
// var channel = Echo.channel('my-channel')
// channel.listen('.my-event',function (data) {
//   alert(JSON.stringify(data))
// })

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'pusher',
  key: 'local', // valeur factice
  cluster: 'mt1', // ✅ OBLIGATOIRE même si tu ne l’utilises pas vraiment
  wsHost: window.location.hostname,
  wsPort: 6001,
  forceTLS: false,
  disableStats: true,
  encrypted: false,
  enabledTransports: ['ws'],
})

export default echo

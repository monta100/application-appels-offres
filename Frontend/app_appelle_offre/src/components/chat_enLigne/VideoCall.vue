<template>
  <div class="video-call">
    <div class="videos">
      <video ref="localVideo" autoplay muted playsinline class="video" />
      <video ref="remoteVideo" autoplay playsinline class="video" />
    </div>

    <div class="controls">
      <button @click="shareScreen">🖥️ Partager mon écran</button>
      <button @click="hangUp">📴 Raccrocher</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, defineEmits } from 'vue'
import api from '@/Http/api'
import echo from '@/Ressource/echo'

const emit = defineEmits(['hangup'])
const localVideo = ref(null)
const remoteVideo = ref(null)
const localStream = ref(null)
const remoteStream = ref(null)
const peer = ref(null)
const currentUser = ref(null)
const interlocutorId = ref(null)
const isCaller = ref(false)
const SIGNAL_CHANNEL = 'video-signal'

const setupPeerConnection = () => {
  peer.value = new RTCPeerConnection({
    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
  })

  peer.value.onicecandidate = (event) => {
    if (event.candidate) {
      sendSignal('ice-candidate', event.candidate)
    }
  }

  peer.value.ontrack = (event) => {
    if (!remoteStream.value) {
      remoteStream.value = new MediaStream()
      remoteVideo.value.srcObject = remoteStream.value
    }
    remoteStream.value.addTrack(event.track)
  }

  localStream.value.getTracks().forEach(track => {
    peer.value.addTrack(track, localStream.value)
  })
}

const sendSignal = (type, data) => {
  api.post('/video-call/signal', {
    from: currentUser.value.idUser,
    to: interlocutorId.value,
    type,
    data
  })
}

const handleSignal = async (signal) => {
  const { type, data } = signal

  if (type === 'offer') {
    setupPeerConnection()
    await peer.value.setRemoteDescription(new RTCSessionDescription(data))
    const answer = await peer.value.createAnswer()
    await peer.value.setLocalDescription(answer)
    sendSignal('answer', answer)
  }

  if (type === 'answer') {
    await peer.value.setRemoteDescription(new RTCSessionDescription(data))
  }

  if (type === 'ice-candidate') {
    try {
      await peer.value.addIceCandidate(new RTCIceCandidate(data))
    } catch (err) {
      console.error('Erreur ICE :', err)
    }
  }
}

const startLocalCamera = async () => {
  localStream.value = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
  localVideo.value.srcObject = localStream.value
}

const shareScreen = async () => {
  try {
    const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true })
    const screenTrack = screenStream.getVideoTracks()[0]

    const sender = peer.value.getSenders().find(s => s.track?.kind === 'video')

    if (sender) {
      // 🔁 Remplacer la piste vidéo locale par celle du partage d’écran
      await sender.replaceTrack(screenTrack)
      localVideo.value.srcObject = screenStream
      localStream.value = screenStream

      // 🔁 Envoyer une nouvelle offre pour notifier le remote peer
      const offer = await peer.value.createOffer()
      await peer.value.setLocalDescription(offer)
      sendSignal('offer', offer)

      // 🔄 Revenir à la caméra une fois le partage d'écran terminé
      screenTrack.onended = async () => {
        const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
        const cameraTrack = cameraStream.getVideoTracks()[0]
        const audioTrack = cameraStream.getAudioTracks()[0]

        await sender.replaceTrack(cameraTrack)
        localVideo.value.srcObject = cameraStream
        localStream.value = cameraStream

        // Réinitialiser l'audio si nécessaire
        const audioSender = peer.value.getSenders().find(s => s.track?.kind === 'audio')
        if (audioSender && audioTrack) {
          await audioSender.replaceTrack(audioTrack)
        }

        // 🔁 Refaire une offre pour revenir à la caméra
        const offerBack = await peer.value.createOffer()
        await peer.value.setLocalDescription(offerBack)
        sendSignal('offer', offerBack)
      }
    } else {
      console.warn("🎥 Aucun sender vidéo trouvé dans la peer connection")
    }
  } catch (err) {
    console.error('Erreur partage écran :', err)
    alert("Impossible de partager l'écran.")
  }
}


const hangUp = () => {
  localStream.value?.getTracks().forEach(track => track.stop())
  peer.value?.close()
  peer.value = null
  emit('hangup')
}

onMounted(async () => {
  const res = await api.get('/user')
  currentUser.value = res.data

  interlocutorId.value = localStorage.getItem('interlocutorId')
  isCaller.value = localStorage.getItem('isCaller') === 'true'

  await startLocalCamera()
  setupPeerConnection()

  if (isCaller.value) {
    const offer = await peer.value.createOffer()
    await peer.value.setLocalDescription(offer)
    sendSignal('offer', offer)
  }

  echo.channel(`${SIGNAL_CHANNEL}.${currentUser.value.idUser}`)
    .listen('.SignalReceived', e => {
      handleSignal(e)
    })
})

onBeforeUnmount(() => {
  hangUp()
})
</script>

<style scoped>
.video-call {
  padding: 1rem;
  background: #fefefe;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.videos {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1rem;
}
.video {
  width: 320px;
  height: 240px;
  background: #000;
  border-radius: 8px;
  object-fit: cover;
}
.controls {
  display: flex;
  justify-content: center;
  gap: 1rem;
}
button {
  padding: 0.6rem 1.2rem;
  background: #ff7900;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
button:hover {
  background: #cc6200;
}
</style>

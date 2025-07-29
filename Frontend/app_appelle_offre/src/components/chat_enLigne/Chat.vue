<template>
  <div v-if="!inCall">
    <Navbar />
    <div class="chat-wrapper">
      <!-- Sidebar Membres -->
 <!-- Sidebar Membres -->
<aside class="sidebar">
<div class="sidebar-header">
  <i class="fas fa-users icon"></i>
  <h4>Communauté de l’application</h4>
</div>
  <ul>
    <li
      v-for="user in users"
      :key="user.idUser"
      :class="{ active: selectedUser?.idUser === user.idUser }"
      class="user-item"
    >
      <div class="user-card" @click="selectUser(user)">
        <img :src="user.photo || defaultAvatar" alt="avatar" class="avatar-img" />

        <div class="user-name">{{ user.nom }} {{ user.prenom }}</div>
        <div class="user-role">{{ user.role }}</div>
        
      </div>

      <!-- Bouton appel 📞 à gauche -->
      <button
        class="call-btn"
        @click="sendCallRequest(user.idUser)"
        :disabled="!currentUser"
        title="Appeler"
      >
        📞
      </button>
    </li>
  </ul>
</aside>


      <!-- Zone de discussion -->
      <main class="chat-area">
      <header v-if="selectedUser" class="chat-header">
  <div class="chat-header-left">
    <img :src="selectedUser.photo || defaultAvatar" alt="avatar" class="chat-avatar" />
    <div>
      <h5>{{ selectedUser.nom }} {{ selectedUser.prenom }}</h5>
      <small>{{ selectedUser.role }}</small>
    </div>
  </div>

  <button
    class="call-header-btn"
    @click="sendCallRequest(selectedUser.idUser)"
    :disabled="!currentUser"
    title="Appeler"
  >
    📞
  </button>
</header>


        <section class="chat-messages" ref="chatScroll">
          <div
            v-for="msg in messages"
            :key="msg.idMessage"
            class="msg-container"
            :class="msg.sender_id === currentUser.idUser ? 'sent' : 'received'"
          >
            <div class="msg-content">
              <div v-if="msg.content">{{ msg.content }}</div>

              <template v-if="msg.file_path">
                <div v-if="msg.file_path.endsWith('.pdf')">
                  <a :href="`http://localhost:8000/storage/${msg.file_path}`" target="_blank">
                    Voir le PDF
                  </a>
                </div>
                <div v-else>
                  <img
                    :src="`http://localhost:8000/storage/${msg.file_path}`"
                    alt="fichier"
                    class="attached-image"
                  />
                </div>
              </template>
            </div>
          </div>
        </section>

        <footer v-if="selectedUser" class="chat-footer">
          <form @submit.prevent="sendMessage" class="chat-input">
            <label class="file-attach">
              📎
              <input type="file" @change="handleFile" accept="image/*,.pdf" hidden />
            </label>

            <div v-if="fileToSend" class="file-preview">
              <div v-if="fileToSend.type === 'application/pdf'">
                📎 Fichier PDF sélectionné : {{ fileToSend.name }}
              </div>
              <div v-else-if="fileToSend.type.startsWith('image/')">
                <img :src="previewUrl" alt="aperçu" class="attached-image" />
              </div>
              <div v-else>
                📎 Fichier sélectionné : {{ fileToSend.name }}
              </div>
            </div>

            <input v-model="newMessage" type="text" placeholder="Écrire un message..." />
            <button type="submit">Envoyer</button>
          </form>
        </footer>
      </main>
    </div>

    <!-- MODALE D’APPEL ENTRANT -->
    <div v-if="showCallModal" class="modal-overlay">
      <div class="modal-content">
        <h3>📞 Appel entrant</h3>
        <p>L'utilisateur ID {{ incomingCall?.callerId }} vous appelle.</p>
        <div class="modal-buttons">
          <button @click="acceptCall">✅ Accepter</button>
          <button @click="rejectCall">❌ Refuser</button>
        </div>
      </div>
    </div>
  </div>

  <!-- APPEL VIDÉO -->
  <div v-else>
    <VideoCall  @hangup="inCall = false"/>
  </div>

  <audio ref="ringtone" :src="ringtoneSrc" preload="auto" />

</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '@/Http/api'
import Navbar from '../Navbar.vue'
import echo from '@/Ressource/echo'
import VideoCall from './VideoCall.vue'
import defaultAvatar from '@/Backoffice/assets/img/default-avatar.jpg'

const users = ref([])
const messages = ref([])
const currentUser = ref(null)
const selectedUser = ref(null)
const newMessage = ref('')
const chatScroll = ref(null)
const fileToSend = ref(null)
const previewUrl = ref(null)
const incomingCall = ref(null) // { callerId: 2 }
const showCallModal = ref(false)
const inCall = ref(false)

const ringtone = ref(null)
const ringtoneSrc = '/audio/ring.mp3' // Assure-toi que ce fichier existe dans /public/audio/
const fetchCurrentUser = async () => {
  const { data } = await api.get('/user')
  currentUser.value = data
}

const fetchUsers = async () => {
  try {
    const { data } = await api.get('/users')
    users.value = Array.isArray(data) ? data : data.users || []
    users.value = users.value.filter(u => u.idUser !== currentUser.value.idUser)
  } catch (error) {
    console.error('Erreur lors du fetch des utilisateurs :', error)
  }
}

const selectUser = async (user) => {
  selectedUser.value = user
  try {
    const { data } = await api.get(`/messages/${user.idUser}`)
    messages.value = data
    await nextTick(() => {
      chatScroll.value.scrollTop = chatScroll.value.scrollHeight
    })
  } catch (error) {
    console.error('Erreur lors du fetch des messages :', error)
  }
}
const sendMessage = async () => {
  if (!newMessage.value.trim() && !fileToSend.value) return

  const formData = new FormData()
  formData.append('receiver_id', selectedUser.value.idUser)
  if (newMessage.value.trim()) formData.append('content', newMessage.value)
  if (fileToSend.value) formData.append('file', fileToSend.value)

  try {
    const res = await api.post('/messages', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    messages.value.push(res.data)
    newMessage.value = ''
    fileToSend.value = null

    await nextTick(() => {
      chatScroll.value.scrollTop = chatScroll.value.scrollHeight
    })
  } catch (error) {
    console.error('Erreur lors de l’envoi :', error)
  }
}

const handleFile = (event) => {
  const file = event.target.files[0]
  if (!file) return
  fileToSend.value = file

  // Aperçu si image
  if (file.type.startsWith('image/')) {
    previewUrl.value = URL.createObjectURL(file)
  } else {
    previewUrl.value = null
  }
}


onMounted(async () => {
  await fetchCurrentUser()
  await fetchUsers()

  echo.channel('public-chat')
    .listen('MessageSent', (e) => {
      if (selectedUser.value && e.sender.idUser === selectedUser.value.idUser) {
        messages.value.push({
          idMessage: e.idMessage,
          content: e.content,
          sender_id: e.sender.idUser,
          file_path: e.file_path, 

        })
        nextTick(() => {
          chatScroll.value.scrollTop = chatScroll.value.scrollHeight
        })
      }
      
    }
  )



  // 🎧 Écoute des appels entrants (après avoir currentUser)
  echo.channel(`video-call.${currentUser.value.idUser}`)
    .listen('CallRequested', (e) => {
      console.log("📞 Appel entrant de :", e.callerId)
      incomingCall.value = e
      showCallModal.value = true
    })
echo.channel(`video-call.${currentUser.value.idUser}`)
  .listen('CallAccepted', (e) => {
    console.log("✅ Appel accepté par :", e.receiverId)
    inCall.value = true
  })

  .listen('CallRequested', (e) => {
  incomingCall.value = e
  showCallModal.value = true
  playRingtone()
})


})
const sendCallRequest = async (receiverId) => {
  if (!currentUser.value || !receiverId) return

  try {
    await api.post('/video-call/request', {
      caller_id: currentUser.value.idUser,
      receiver_id: receiverId
    })
    alert('Appel lancé !')
  } catch (err) {
    console.error("Erreur lors de l’appel :", err)
  }
}
const acceptCall = async () => {

    stopRingtone()

  showCallModal.value = false
  inCall.value = true

  // Informer l’appelant que l’appel est accepté
  await api.post('/video-call/accept', {
    caller_id: incomingCall.value.callerId,
    receiver_id: currentUser.value.idUser
  })
}



const rejectCall = () => {

    stopRingtone()

  showCallModal.value = false
  incomingCall.value = null
}



const playRingtone = () => {
  ringtone.value?.play().catch(err => {
    console.error("❌ Erreur lecture sonnerie :", err)
  })
}

const stopRingtone = () => {
  ringtone.value?.pause()
  ringtone.value.currentTime = 0
}




</script>

<style scoped>
.chat-wrapper {
  display: flex;
  height: 90vh;
  background: #fefefe;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
}

.sidebar {
  width: 270px;
  background: #fff;
  border-right: 1px solid #e1e1e1;
  padding: 1rem;
  overflow-y: auto;
}

.sidebar-header {
  background-color: #fff5e5; /* Orange très clair */
  padding: 1rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.sidebar-header h4 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.sidebar-header .icon {
  color: #ff7900;
  font-size: 1.5rem;
}
.user-item {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  padding: 0.6rem;
  margin-bottom: 0.4rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
}

.user-item:hover,
.user-item.active {
  background-color: #ff7900;
  color: white;
}

.avatar-img {
  width: 80px;
  height: 80px;
  object-fit: cover;         /* ✅ Remplit parfaitement le cadre sans déformer */
  border-radius: 50%;
  border: 2px solid #ff7900;
  margin-bottom: 0.5rem;
}


.user-info .name {
  font-weight: 600;
  color: #222;
}

.user-info .role {
  font-size: 12px;
  color: #777;
}

.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f6f6f6;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  padding: 10px 20px;
  border-bottom: 1px solid #eee;
}


.chat-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.chat-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.chat-messages {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.call-header-btn {
  background-color: #ff7f00;
  color: white;
  border: none;
  border-radius: 50%;
  width: 42px;
  height: 42px;
  font-size: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 5px rgba(0,0,0,0.2);
  transition: background 0.3s;
}
.call-header-btn:hover {
  background-color: #e67300;
}
.call-floating-btn {
  transition: transform 0.2s ease;
}
.call-floating-btn:hover {
  transform: scale(1.1);
}

.msg-container {
  max-width: 65%;
  padding: 0.7rem 1rem;
  border-radius: 1.2rem;
  word-wrap: break-word;
  font-size: 14px;
  line-height: 1.4;
}

.sent {
  align-self: flex-end;
  background-color: #ff7900;
  color: white;
  border-bottom-right-radius: 0;
}

.received {
  align-self: flex-start;
  background-color: #ffffff;
  color: #333;
  border-bottom-left-radius: 0;
}

.chat-footer {
  padding: 0.8rem;
  border-top: 1px solid #ddd;
  background: white;
}

.chat-input {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chat-input input {
  flex: 1;
  padding: 0.6rem 1rem;
  border-radius: 999px;
  border: 1px solid #ccc;
  font-size: 14px;
  outline: none;
}

.chat-input button {
  padding: 0.6rem 1.3rem;
  background: #ff7900;
  border: none;
  color: white;
  border-radius: 999px;
  cursor: pointer;
  transition: 0.2s;
}

.chat-input button:hover {
  background: #cc6200;
}

.file-attach {
  cursor: pointer;
  font-size: 20px;
  color: #888;
}
.attached-image {
  max-width: 200px;
  max-height: 150px;
  margin-top: 6px;
  border-radius: 8px;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
.file-preview {
  margin-bottom: 0.5rem;
  background: #f0f0f0;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 14px;
  position: relative;
}

.remove-file {
  background: transparent;
  border: none;
  color: red;
  font-size: 18px;
  cursor: pointer;
  position: absolute;
  top: 5px;
  right: 8px;
}
.call-btn {
  background: #ff7900;
  color: white;
  border: none;
  border-radius: 50%;
  padding: 10px 13px;
  font-size: 18px;
  cursor: pointer;
  transition: background 0.2s;
}

.call-btn:hover {
  background: #cc6200;
}


.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  width: 320px;
  text-align: center;
}

.modal-buttons {
  display: flex;
  justify-content: space-around;
  margin-top: 1rem;
}

.modal-buttons button {
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.modal-buttons button:first-child {
  background: #28a745;
  color: white;
}

.modal-buttons button:last-child {
  background: #dc3545;
  color: white;
}

</style>

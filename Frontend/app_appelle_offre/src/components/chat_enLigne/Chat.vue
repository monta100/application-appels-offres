<template>
  <Navbar />
  <div class="chat-wrapper">
    <!-- Sidebar Membres -->
    <aside class="sidebar">
      <h4>Membres de l'application</h4>
      <ul>
        <li
          v-for="user in users"
          :key="user.idUser"
          :class="{ active: selectedUser?.idUser === user.idUser }"
          @click="selectUser(user)"
          class="user-item"
        >
          <img :src="user.photo || defaultAvatar" alt="avatar" class="avatar" />
          <div class="user-info">
            <span class="name">{{ user.nom }} {{ user.prenom }}</span>
            <span class="role">{{ user.role }}</span>
          </div>
        </li>
      </ul>
    </aside>

    <!-- Zone de discussion -->
    <main class="chat-area">
      <header v-if="selectedUser" class="chat-header">
        <img :src="selectedUser.photo || defaultAvatar" alt="avatar" class="chat-avatar" />
        <div>
          <h5>{{ selectedUser.nom }} {{ selectedUser.prenom }}</h5>
          <small>{{ selectedUser.role }}</small>
        </div>
      </header>

      <section class="chat-messages" ref="chatScroll">
        <div
          v-for="msg in messages"
          :key="msg.idMessage"
          class="msg-container"
          :class="msg.sender_id === currentUser.idUser ? 'sent' : 'received'"
        >
<div class="msg-content">
  <!-- Texte du message -->
  <div v-if="msg.content">{{ msg.content }}</div>

  <!-- Pièce jointe (si disponible) -->
  <template v-if="msg.file_path">
    <div v-if="msg.file_path.endsWith('.pdf')">
<a :href="`http://localhost:8000/storage/${msg.file_path}`" target="_blank">Voir le PDF</a>
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

          <!-- Aperçu fichier sélectionné -->
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
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import api from '@/Http/api'
import Navbar from '../Navbar.vue'
import echo from '@/Ressource/echo'
const users = ref([])
const messages = ref([])
const currentUser = ref(null)
const selectedUser = ref(null)
const newMessage = ref('')
const chatScroll = ref(null)
const defaultAvatar = "@/Backoffice/assets/img/default-avatar.jpg"           
const fileToSend = ref(null)
const previewUrl = ref(null)

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
    })
})
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

.sidebar h4 {
  margin-bottom: 1rem;
  color: #333;
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

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  background: #eee;
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
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #ddd;
}

.chat-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
  background: #eee;
}

.chat-messages {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
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

</style>

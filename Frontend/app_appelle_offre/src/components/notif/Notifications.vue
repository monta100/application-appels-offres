<template>
  <div class="notification-wrapper position-relative">
    <button @click="toggleDropdown" class="btn btn-light rounded-circle shadow-sm notif-icon">
      🔔
      <span v-if="unreadCount" class="badge-notif">{{ unreadCount }}</span>
    </button>

    <div v-if="showDropdown" class="notif-dropdown shadow animate__animated animate__fadeIn">
      <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
        <h6 class="mb-0 fw-bold text-dark">🔔 Notifications</h6>
        <button v-if="notifications.length" class="btn btn-sm text-muted" @click="markAllAsRead">🧹</button>
      </div>

      <div class="notif-list">
        <div
          v-for="notif in notifications"
          :key="notif.id"
          class="notif-item d-flex align-items-start gap-2"
        >
          <span class="notif-icon-type" :class="getIconClass(notif.type)"></span>
          <div>
            <strong>{{ notif.title }}</strong>
            <p class="mb-1 small text-muted">{{ notif.message }}</p>
            <span class="text-muted small">{{ formatTime(notif.created_at) }}</span>
          </div>
        </div>

        <div v-if="!notifications.length" class="text-center text-muted py-3">
          Aucune notification
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/Http/api'
import echo from '@/Ressource/echo'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
dayjs.extend(relativeTime)

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

// 🔒 Récupère l'utilisateur depuis le localStorage (en toute sécurité)
let user = null
try {
  user = JSON.parse(localStorage.getItem('user'))
} catch (e) {
  console.error("⚠️ Erreur parsing user from localStorage", e)
}

const userId = user?.idUser || null // adapte si c'est juste `id`

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

const formatTime = (date) => {
  return dayjs(date).fromNow()
}

const getIconClass = (type) => {
  switch (type) {
    case 'appel': return 'text-primary fa fa-file-alt'
    case 'soumission': return 'text-success fa fa-file-signature'
    case 'contrat': return 'text-warning fa fa-file-contract'
    default: return 'text-muted fa fa-info-circle'
  }
}

const markAllAsRead = async () => {
  try {
    await api.post('/notifications/mark-all-read')
    unreadCount.value = 0
  } catch (err) {
    console.error(err)
  }
}

// ✅ Chargement initial + écoute
onMounted(async () => {
  if (!userId) {
    console.warn('⚠️ userId non défini. Notifications non chargées.')
    return
  }

  try {
    const res = await api.get(`/notifications`)
    notifications.value = res.data
    unreadCount.value = res.data.filter(n => !n.is_read).length
  } catch (err) {
    console.error('❌ Erreur chargement notifications', err)
    return // stop ici pour éviter l’erreur suivante
  }

  // ✅ Écoute temps réel uniquement si userId défini
  try {
echo.channel('notifications')
      .listen('NotificationEvent', (e) => {
        if (e.notification.user_id === userId) {
          notifications.value.unshift(e.notification)
          unreadCount.value++
        }
      })
  } catch (err) {
    console.error('❌ Erreur d’abonnement Echo :', err)
  }
})
</script>


<style scoped>
.notification-wrapper {
  position: relative;
}

.notif-icon {
  font-size: 1.4rem;
  position: relative;
  transition: transform 0.2s;
}

.badge-notif {
  position: absolute;
  top: -5px;
  right: -6px;
  background: #dc3545;
  color: white;
  border-radius: 50%;
  font-size: 0.7rem;
  padding: 2px 6px;
}

.notif-dropdown {
  position: absolute;
  top: 40px;
  right: 0;
  background: #fff;
  width: 320px;
  max-height: 400px;
  border-radius: 10px;
  overflow: hidden;
  z-index: 9999;
}

.notif-list {
  max-height: 340px;
  overflow-y: auto;
  padding: 10px;
}

.notif-item {
  padding: 10px;
  border-bottom: 1px solid #f1f1f1;
}

.notif-icon-type {
  font-size: 1.2rem;
  margin-top: 3px;
}
</style>

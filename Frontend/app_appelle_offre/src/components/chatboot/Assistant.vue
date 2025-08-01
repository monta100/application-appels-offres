<template>
  <Navbar />
  <div class="assistant-layout">
    <div class="chatbot-side">
      <div class="chat-header">
        <img src="/assets/images/bot-icon.png" alt="Avatar bot" class="bot-avatar" />
        <div class="header-texts">
          <h1 class="bot-name">Bienvenue, je suis <span class="kopilo-name">Kopilo</span> 👋</h1>
          <p class="bot-subtitle">Gagne du temps sur l’application — Kopilo t’assiste à chaque étape.</p>
        </div>
      </div>

      <Chatbot v-model="userInput" />
    </div>

    <div class="aide-toggle">
      <button @click="toggleAide" class="toggle-btn">
        <i class="fa" :class="showAide ? 'fa-eye-slash' : 'fa-eye'"></i>
        {{ showAide ? 'Masquer' : 'Afficher' }} l’aide
      </button>
    </div>

    <transition name="fade">
      <div v-if="showAide" class="aide-side">
        <AssistantAide @insert="userInput = $event" />
      </div>
    </transition>
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue';
import Chatbot from './Chatbot.vue';
import AssistantAide from './AssistantAide.vue';

export default {
  components: { Navbar, Chatbot, AssistantAide },
  data() {
    return {
      userInput: '',
      showAide: true
    };
  },
  methods: {
    toggleAide() {
      this.showAide = !this.showAide;
    }
  }
};
</script>

<style scoped>
.assistant-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  padding: 2rem;
  background: #f9f9f9;
  min-height: 100vh;
}

.kopilo-name {
  color: #ff6f00;
  font-weight: bold;
}

.chatbot-side {
  flex: 2;
  min-width: 600px;
}

.aide-side {
  flex: 1;
  min-width: 280px;
  max-width: 400px;
  position: sticky;
  top: 2rem;
    transition: all 0.3s ease-in-out;

}
.aide-toggle {
  position: fixed;
  top: 80px;
  right: 30px;
  z-index: 1000;
  transition: transform 0.4s ease, opacity 0.4s ease;
}
.aide-hidden {
  transform: translateX(120%);
  opacity: 0;
  pointer-events: none;
}
.chat-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.bot-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background-color: #fff;
  border: 2px solid #ff6f00;
  padding: 6px;
}

.header-texts {
  display: flex;
  flex-direction: column;
}

.bot-name {
  font-size: 1.7rem;
  font-weight: 700;
  color: #ff6f00;
  margin: 0;
}

.bot-subtitle {
  font-size: 1rem;
  color: #666;
  margin-top: 4px;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
.toggle-btn {
  background: #ff6f00;
  color: #fff;
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  transition: background 0.3s ease;
}

.toggle-btn:hover {
  background: #e65100;
}


</style>

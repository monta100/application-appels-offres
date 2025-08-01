<template>
  <div class="chatbot-wrapper">
    <div class="chatbot-box">
      <h2 class="title">Assistant Intelligent</h2>

      <div class="chat-window">
        <div class="chat-history">
          <div
            v-for="(entry, index) in responseHistory"
            :key="index"
            class="message-block"
          >
            <div class="user-bubble">🧑‍💻 {{ entry.question }}</div>
            <div class="bot-bubble" v-if="entry">
              <img src="/assets/images/bot-icon.png" class="bot-avatar" alt="Bot Avatar" />

              <p><strong>🤖
                <span v-if="entry.type === 'deadline' && entry.data?.deadline">
                  L’appel d’offre se termine le {{ formatDate(entry.data.deadline) }}.
                </span>
                <span v-else-if="entry.type === 'date_debut' && entry.data?.date_debut">
                  L’appel d’offre commence le {{ formatDate(entry.data.date_debut) }}.
                </span>
                <span v-else-if="entry.message">
                  {{ entry.message }}
                </span>
                <span v-else>
                  (Pas de réponse disponible)
                </span>
              </strong></p>

              <div v-if="entry.type === 'appels_recents' && Array.isArray(entry.data?.appels)">
                <div
                  v-for="appel in entry.data.appels"
                  :key="appel.idAppel"
                  class="card card-appel"
                >
                  <h5>{{ appel.titre }}</h5>
                  <p>{{ appel.description }}</p>
                  <ul>
                    <li><strong>Budget :</strong> {{ appel.budget }} DT</li>
                    <li>
                      <strong>Du :</strong>
                      {{ formatDate(appel.date_debut) }} au
                      {{ formatDate(appel.date_fin) }}
                    </li>
                  </ul>
                </div>
              </div>

            

              <div v-else-if="entry.type === 'contrat'">
                <p v-if="entry.data?.contrat_generé">
                  ✅ Contrat généré :
                  <a :href="entry.data.contrat?.pdf_url" target="_blank">Voir le PDF</a>
                </p>
                <p v-else>❌ Aucun contrat généré pour cette soumission.</p>
              </div>

              <div v-else-if="entry.type === 'appel_offre_brouillon'">
                <p>🤖 <strong>Appel d'offre généré en brouillon.</strong></p>
                <p>
                  👉 <a :href="entry.data?.lien_modification || 'http://localhost:5173/appelles'" 
                        target="_blank" class="link-orange">
                    Complétez-le maintenant
                  </a>
                </p>
              </div>

              <div v-else-if="entry.type === 'aide_redaction'">

                <p><strong>Prix proposé :</strong> {{ entry.data.prixPropose }} DT</p>
                <p><strong>Description :</strong> {{ entry.data.description }}</p>
                <p><strong>Temps :</strong> {{ entry.data.temps_realisation }}</p>
              </div>
            </div>
          </div>

          <div v-if="loading" class="typing-bubble">
            <img src="/assets/images/bot-icon.png" class="bot-avatar" alt="Bot Avatar" />
            <div class="dots">
              <span>.</span><span>.</span><span>.</span>
            </div>

          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="input-box">
          <input
            v-model="userInput"
            placeholder="Ex: Quels sont les appels d’offres publiés récemment ?"
            required
          />
          <button type="submit" :disabled="loading">
            <span v-if="loading" class="spinner"></span>
            <span v-else>Envoyer <i class="fa fa-paper-plane"></i></span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/Http/api';

export default {
  data() {
    return {
      userInput: '',
      loading: false,
      responseHistory: [],
    };
  },
  methods: {
 async handleSubmit() {
  this.loading = true;

  try {
    const isCreationAppel = this.userInput.toLowerCase().includes('créer un appel');
    const endpoint = isCreationAppel
      ? '/chatbot/create-appel-offre'
      : '/chatbot/unified';

    const token = localStorage.getItem('token');

    const res = await api.post(endpoint, {
      message: this.userInput,
    }, {
      headers: {
        ...(isCreationAppel && token ? { Authorization: `Bearer ${token}` } : {}),
      }
    });

    const { success, type, message, error, data } = res.data;

this.responseHistory.push({
  question: this.userInput,
  type: success ? (type || '') : 'erreur',
  message: success
    ? (type === 'appel_cree' ? '' : (message || this.generateFallbackMessage(type, res.data.data)))
    : (error || 'Erreur inconnue'),
  data: success ? res.data.data : null,
});

  } catch (err) {
    console.error('❌ Erreur API :', err);
    this.responseHistory.push({
      question: this.userInput,
      type: 'erreur',
      message: 'Erreur serveur',
      data: null,
    });
  } finally {
    this.userInput = '';
    this.loading = false;
  }
},


    formatDate(dateStr) {
      const d = new Date(dateStr);
      return d.toLocaleDateString('fr-FR');
    },

    generateFallbackMessage(type, data) {
      if (type === 'deadline' && data?.deadline) {
        return `L’appel d’offre se termine le ${this.formatDate(data.deadline)}.`;
      }

      if (type === 'date_debut' && data?.date_debut) {
        return `L’appel d’offre commence le ${this.formatDate(data.date_debut)}.`;
      }

      if (type === 'contrat') {
        return data?.contrat_generé
          ? 'Un contrat a été généré.'
          : 'Aucun contrat n’a été généré.';
      }

      return null;
    },
  },
};
</script>

<style scoped>
.chatbot-wrapper {
  background: linear-gradient(to right, #fff3e0, #ffe0b2);
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.chatbot-box {
  background: #ffffff;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 720px;
  display: flex;
  flex-direction: column;
  height: 90vh;
}
.title {
  color: #ff6f00;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  text-align: center;
}
.chat-window {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
}
.chat-history {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background: #fffaf4;
  border-radius: 12px;
  margin-bottom: 1rem;
  box-shadow: inset 0 0 6px rgba(0,0,0,0.05);
}
.message-block {
  margin-bottom: 1.4rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.user-bubble, .bot-bubble {
  padding: 0.9rem 1.2rem;
  border-radius: 14px;
  margin-bottom: 0.4rem;
  max-width: 85%;
  line-height: 1.5;
  font-size: 0.95rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  position: relative;
}
.user-bubble {
  background-color: #ffe0b2;
  align-self: flex-end;
  color: #4e342e;
}
.bot-bubble {
  background-color: #ffffff;
  border: 1px solid #f0f0f0;
  align-self: flex-start;
  color: #263238;
}
.bot-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-bottom: 4px;
}
.typing-bubble {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.2rem;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 14px;
  width: fit-content;
  margin-bottom: 1rem;
}
.typing-bubble .dots span {
  font-size: 2rem;
  animation: blink 1s infinite ease-in-out;
}
.typing-bubble .dots span:nth-child(2) {
  animation-delay: 0.2s;
}
.typing-bubble .dots span:nth-child(3) {
  animation-delay: 0.4s;
}
@keyframes blink {
  0%, 100% { opacity: 0.2; }
  50% { opacity: 1; }
}
.input-box {
  display: flex;
  gap: 0.6rem;
  padding-top: 0.5rem;
}
input {
  flex: 1;
  padding: 0.8rem;
  border-radius: 10px;
  border: 1px solid #ccc;
  font-size: 1rem;
  background-color: #fffefc;
}
button {
  background: #ff6f00;
  color: white;
  padding: 0.8rem 1.2rem;
  border: none;
  border-radius: 10px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s ease;
}
button:hover {
  background: #e65100;
}
.spinner {
  width: 18px;
  height: 18px;
  border: 3px solid #fff;
  border-top: 3px solid transparent;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
  vertical-align: middle;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.card-appel {
  background: #ffffff;
  border: 1px solid #eee;
  padding: 1rem;
  margin-top: 0.5rem;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.03);
}
.link-orange {
  color: #ff6f00;
  text-decoration: underline;
  font-weight: bold;
}
.link-orange:hover {
  opacity: 0.8;
}
</style>

<template>
  <div class="chatbot-wrapper">
    <div class="chatbot-box">
      <h2 class="title">Assistant Intelligent</h2>

      <div class="chat-window">
        <div class="chat-history">
          <div v-for="(entry, index) in responseHistory" :key="index" class="message-block">
            <!-- Question utilisateur -->
            <div class="user-bubble">🧑‍💻 {{ entry.question }}</div>

            <!-- Réponse assistant -->
            <div class="bot-bubble" v-if="entry.message">
              <p><strong>🤖 {{ entry.message }}</strong></p>

              <!-- Appels récents -->
              <div v-if="entry.type === 'appels_recents' && Array.isArray(entry.data)">
                <div v-for="appel in entry.data" :key="appel.idAppel" class="card card-appel">
                  <h5>{{ appel.titre }}</h5>
                  <p>{{ appel.description }}</p>
                  <ul>
                    <li><strong>Budget :</strong> {{ appel.budget }} DT</li>
                    <li>
                      <strong>Du :</strong>
                      {{ formatDate(appel.date_debut) }} au {{ formatDate(appel.date_fin) }}
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Soumission -->
              <div v-else-if="entry.type === 'soumission'">
                <p><strong>Prix proposé :</strong> {{ entry.data.prixPropose }} DT</p>
                <p><strong>Description :</strong> {{ entry.data.description }}</p>
                <p><strong>Temps de réalisation :</strong> {{ entry.data.temps_realisation }}</p>
              </div>

              <!-- Deadline -->
              <div v-else-if="entry.type === 'deadline'">
                <p><strong>Date limite :</strong> {{ entry.data.deadline }}</p>
                <p><strong>Expirée :</strong> {{ entry.data.expired ? 'Oui' : 'Non' }}</p>
              </div>

              <!-- Contrat -->
              <div v-else-if="entry.type === 'contrat'">
                <p v-if="entry.data.contrat_generé">
                  ✅ Contrat généré :
                  <a :href="entry.data.contrat.pdf_url" target="_blank">Voir le PDF</a>
                </p>
                <p v-else>❌ Aucun contrat généré pour cette soumission.</p>
              </div>

              <!-- Appel créé -->
              <div v-else-if="entry.type === 'appel_cree'">
                <p>✅ Appel d'offre créé avec succès :</p>
                <pre>{{ JSON.stringify(entry.data, null, 2) }}</pre>
              </div>

              <!-- Aide soumission -->
              <div v-else-if="entry.type === 'aide_soumission'">
                <p><strong>Prix proposé :</strong> {{ entry.data.prixPropose }} DT</p>
                <p><strong>Description :</strong> {{ entry.data.description }}</p>
                <p><strong>Temps :</strong> {{ entry.data.temps_realisation }}</p>
              </div>

              <!-- Texte libre ou erreur -->
              <div v-else>
                <p>{{ entry.data }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulaire d'envoi -->
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
        const res = await api.post('/chatbot/unified', {
          message: this.userInput,
        });

        console.log('✅ Réponse reçue :', res.data);

        const { success, type, message, data, error } = res.data;

        this.responseHistory.push({
          question: this.userInput,
          type: success ? (type || '') : 'erreur',
          message: success ? (message || 'Réponse vide') : (error || 'Erreur inconnue'),
          data: success ? (data || null) : null,
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
  },
};
</script>

<style scoped>
.chatbot-wrapper {
  background: #fff8f1;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.chatbot-box {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: 700px;
  display: flex;
  flex-direction: column;
  height: 90vh;
}
.title {
  color: #ff7a00;
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 1rem;
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
  padding: 1rem;
  background: #fff4e6;
  border-radius: 8px;
  margin-bottom: 1rem;
}
.message-block {
  margin-bottom: 1.2rem;
}
.user-bubble, .bot-bubble {
  padding: 0.75rem;
  border-radius: 12px;
  margin-bottom: 0.5rem;
  max-width: 90%;
  line-height: 1.4;
}
.user-bubble {
  background-color: #ffecd1;
  align-self: flex-end;
}
.bot-bubble {
  background-color: #ffffff;
  border: 1px solid #eee;
  align-self: flex-start;
}
.input-box {
  display: flex;
  gap: 0.5rem;
}
input {
  flex: 1;
  padding: 0.75rem;
  border-radius: 8px;
  border: 1px solid #ccc;
}
button {
  background: #ff7a00;
  color: white;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  min-width: 120px;
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
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.card-appel {
  background: #fff;
  border: 1px solid #eee;
  padding: 1rem;
  margin-top: 0.5rem;
  border-radius: 8px;
}
</style>

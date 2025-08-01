<template>

  <div class="chatbot-wrapper">
    <div class="chatbot-box">
<div class="title-banner">
  <i class="fas fa-robot"></i> Assistant Intelligent
</div>

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
  <span v-if="entry.type === 'date_fin' && entry.data?.date_fin">
    L’appel d’offre se termine le {{ formatDate(entry.data.date_fin) }}.
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

  <p :class="entry.contrat_generé ? 'text-success fw-bold' : 'text-danger fw-bold'">
    <!-- Afficher le lien si contrat généré + lien dispo -->
   
  </p>
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
    if (data?.contrat_generé) {
      const lien = data?.lien ?? 'http://localhost:5173/Soumission_chosi';
      return `✅ Un contrat a bien été généré. Merci de consulter la page des contrats : ${lien}`;
    } else {
      return '❌ Aucun contrat n’a été généré pour cet appel d’offre.';
    }
  }


if (type === 'date_debut' && data?.date_debut) {
  return `📅 L’appel d’offre commence le ${this.formatDate(data.date_debut)}.`;
}

if (type === 'deadline' && data?.deadline) {
  return `📅 L’appel d’offre se termine le ${this.formatDate(data.deadline)}.`;
}

// Nouveau fallback général (si jamais type === 'date' mais sans sous-type précis)
if (type === 'date') {
  return data?.error ?? "❌ Impossible d'obtenir la date de l’appel d’offre.";
}



      return null;
    },
  },
};
</script>
<style>

</style>
<style scoped>
.chatbot-wrapper {

   min-height: 100vh;
  background-color: #fef9f5;
  background-image: 
    radial-gradient(circle at top left, #ffedd5 12%, transparent 40%),
    radial-gradient(circle at bottom right, #ffe8cc 15%, transparent 40%);
  background-repeat: no-repeat;
  background-attachment: fixed;
  padding: 2rem;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}
.chatbot-box {
  background: #ffffff;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
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
.title-banner {
  background-color: #ffcc80;
  color: #4e342e;
  padding: 10px 20px;
  border-radius: 10px 10px 0 0;
  font-weight: 600;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

</style>

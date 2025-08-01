<template>
  <div class="help-section">
    <h3 class="title-help">💡 Comment bien formuler votre demande ?</h3>
    <p class="desc-help">
      ⚠️ <strong>Important :</strong> notre assistant comprend mieux les demandes <strong>claires et structurées</strong>.  
      Évite les phrases vagues comme « Crée projet web » et inspire-toi des exemples ci-dessous pour avoir une réponse optimale.
    </p>

    <ul class="examples-help">
      <li v-for="(ex, index) in exemples" :key="index" class="example-item" @click="$emit('insert', ex)">
        💬 {{ ex }}
      </li>
    </ul>

    <div class="tips">
      ⚠️ <strong>Conseil :</strong> Sois précis. Par exemple : <em>« Crée un appel d'offre pour la refonte d’un site web »</em>
      est préférable à <em>« Crée projet web »</em>.
    </div>
  </div>
</template>

<script>
export default {
  name: 'AssistantAide',
  emits: ['insert'],
  data() {
    return {
      exemples: [
        "Créer un appel d'offre de création d’un site web de marketing. Le budget est autour de X dinars.",
        "Quelles sont les appels d’offres ajoutés récemment ?",
        "Aide-moi à faire une soumission pour l'appel Appel-offre_nom",
        "Est-ce qu’un contrat a été généré pour l’appel Appel-offre_nom ?",
        "Quand se termine l’appel Appel-offre_nom ?",
        "Quand commence l’appel Appel-offre_nom ?"
      ]
    };
  }
};
</script>

<style scoped>
.help-section {
  background-color: #fff7ee; /* beige doux */
  border: 1px solid #ffd8a8; /* bordure orange clair */
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 6px 16px rgba(255, 145, 0, 0.1);
  font-family: 'Segoe UI', sans-serif;
  transition: all 0.3s ease-in-out;
}

.title-help {
  color: #ff7a00;
  font-size: 1.25rem;
  font-weight: bold;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.title-help i {
  font-size: 1.2rem;
}

.desc-help {
  font-size: 0.95rem;
  margin-bottom: 18px;
  color: #4a4a4a;
}

.examples-help {
  list-style: none;
  padding: 0;
  margin: 0;
}

.example-item {
  padding: 12px 16px;
  background-color: #fff;
  border: 1px solid #ffddb5;
  border-radius: 10px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: background-color 0.2s ease, border-color 0.2s ease;
  font-size: 0.95rem;
  color: #333;
}

.example-item:hover {
  background-color: #fff3e0;
  border-color: #ffbc80;
}

.tips {
  margin-top: 20px;
  background-color: #fff3d6;
  border-left: 4px solid #ff9800;
  padding: 12px;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #5c5c5c;
}
</style>


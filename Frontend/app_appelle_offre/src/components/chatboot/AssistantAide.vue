<template>
  <div class="help-section">
    <h3 class="title-help">🔎 Besoin d'aide pour formuler votre demande ?</h3>

    <p class="desc-help">Voici quelques exemples de requêtes que vous pouvez poser à l’assistant :</p>

    <ul class="examples-help">
      <li> Ce modèle est sensible aux formulations. Pour garantir une réponse optimale et éviter toute interruption, merci de structurer vos demandes comme dans l'exemple ci-dessous </li>
      <li>💬 Créer un appel d'offre de creation d un  site web de marketing . Le budget est autour de X dinars et cela doit commencer dans les prochaines semaines</li>
      <li>💬 Quelles sont les appelles offre qui sont ajouter récemment</li>
      <li>💬Aide-moi à faire une soumission pour l'appel "Appel-offre_nom"</li>
      <li>💬 Quelle est la deadline de l’appel d’offre #456 ?</li>
      <li>💬 Quels sont les appels d’offres publiés récemment ?</li>
      <li>💬 Y a-t-il un contrat généré pour la soumission #789 ?</li>
    </ul>

    <div class="tips">
      ⚠️ <strong>Conseil :</strong> soyez précis et évitez les phrases vagues ou incomplètes comme « Crée projet web ».
    </div>
  </div>
</template>

<script>
export default {
  name: 'AssistantAide',
};
</script>

<style scoped>
.help-section {
  background: #fff7ed;
  border-left: 5px solid orange;
  padding: 20px;
  border-radius: 8px;
  margin-top: 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}
.title-help {
  color: #ff6600;
  font-size: 18px;
  margin-bottom: 10px;
}
.desc-help {
  margin-bottom: 10px;
}
.examples-help {
  list-style: none;
  padding-left: 0;
}
.examples-help li {
  margin-bottom: 6px;
  padding-left: 10px;
}
.tips {
  margin-top: 12px;
  background-color: #fff3cd;
  padding: 10px;
  border-left: 4px solid #ffc107;
  border-radius: 4px;
  font-size: 14px;
}
</style>

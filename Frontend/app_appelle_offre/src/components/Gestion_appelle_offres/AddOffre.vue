<template>
  <!-- Bouton ouverture du modal -->
  <button
    class="btn btn-primary rounded-pill px-4"
    data-bs-toggle="modal"
    data-bs-target="#modalAddOffre"
  >
    <i class="fa fa-plus me-1"></i> Ajouter un appel d’offre
  </button>

  <!-- Modal -->
  <div class="modal fade" id="modalAddOffre" tabindex="-1" aria-labelledby="modalAddOffreLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form @submit.prevent="submitForm">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Nouvel appel d’offre</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Titre</label>
                <input v-model="form.titre" type="text" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Budget (TND)</label>
                <input v-model="form.budget" type="number" class="form-control" required />
              </div>
              <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea v-model="form.description" class="form-control" rows="3" required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Date de début</label>
                <input v-model="form.date_debut" type="date" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">Date de fin</label>
                <input v-model="form.date_fin" type="date" class="form-control" required />
              </div>
              <div class="col-md-12">
                <label class="form-label">Domaine</label>
                <select v-model="form.idDomaine" class="form-select" required>
                  <option disabled value="">-- Sélectionnez un domaine --</option>
                  <option v-for="domaine in domaines" :key="domaine.idDomaine" :value="domaine.idDomaine">
                    {{ domaine.nom }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-success">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/Http/api';
import { useStore } from 'vuex'

const store = useStore()
const user = computed(() => store.state.auth.user)

const form = ref({
  titre: '',
  description: '',
  date_debut: '',
  date_fin: '',
  budget: '',
  idDomaine: '',
  idUser: null // sera affecté au moment du submit
})

const domaines = ref([])

const submitForm = async () => {
  try {
    form.value.idUser = user.value.id // affectation au user connecté
    await api.post('http://localhost:8000/api/appels', form.value)
    console.log(form.value)

    alert('Appel d’offre ajouté avec succès')
    window.location.reload()
  } catch (err) {
    console.error('Erreur:', err)
  }
}

onMounted(async () => {
  // Charger la liste des domaines
  try {
    const res = await api.get('http://localhost:8000/api/domaines')
    domaines.value = res.data
  } catch (err) {
    console.error('Erreur chargement domaines:', err)
  }
})
</script>

<style scoped>
.modal-content {
  border-radius: 1rem;
}
</style>

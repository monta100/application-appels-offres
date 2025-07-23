<template>
  <!-- Bouton d'ouverture -->
  <button
    class="btn btn-orange rounded-pill px-4 mb-3"
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
          <div class="modal-header">
            <div class="logo">
              <img src="/assets/images/logo.png" alt="Logo" />
            </div>
            <h5 class="modal-title">Nouvel Appel D’offre</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
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
  idUser: null
})

const domaines = ref([])

const submitForm = async () => {
  try {
    form.value.idUser = user.value.id
    await api.post('http://localhost:8000/api/appels', form.value)
    alert('✅ Appel d’offre ajouté avec succès')
    window.location.reload()
  } catch (err) {
    alert('❌ Une erreur est survenue lors de l’ajout.')
    console.error(err)
  }
}

onMounted(async () => {
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
  border-radius: 1.25rem;
  border: 1px solid #f0f0f0;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #fff;
  overflow: hidden;
}

.modal-header {
  background-color: #ffe8d8;
  padding: 1rem 2rem;
  border-bottom: 1px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.modal-header .logo {
  position: absolute;
  left: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
}

.modal-header .logo img {
  height: 32px;
  width: auto;
}

.modal-header .modal-title {
  font-weight: 600;
  font-size: 1.4rem;
  color: #2c3e50;
  margin: 0;
}

.modal-header .btn-close {
  position: absolute;
  right: 1.5rem;
  top: 50%;
  transform: translateY(-50%);
}

.modal-body {
  padding: 2rem;
}

.modal-body .form-label {
  font-weight: 500;
  color: #555;
  margin-bottom: 0.3rem;
}

.modal-body .form-control,
.modal-body .form-select {
  border-radius: 0.6rem;
  border: 1px solid #ccc;
  padding: 0.55rem 0.75rem;
  transition: all 0.3s ease-in-out;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
  border-color: #ff6600;
  box-shadow: 0 0 0 0.15rem rgba(255, 102, 0, 0.25);
}

.modal-footer {
  padding: 1.2rem 2rem;
  border-top: 1px solid #eee;
  background-color: #fff9f4;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.btn {
  padding: 0.5rem 1.5rem;
  border-radius: 2rem;
  font-weight: 500;
  font-size: 1rem;
}

.btn-outline-secondary {
  border: 1px solid #ccc;
  color: #444;
  background: #fff;
  transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
  background: #f2f2f2;
}

.btn-success {
  background-color: #ff6600;
  border: none;
  color: white;
  transition: all 0.3s ease;
}

.btn-success:hover {
  background-color: #e65c00;
}

/* Bouton principal orange */
.btn-orange {
  background-color: #ff6600;
  border: none;
  color: white;
  transition: all 0.3s ease;
}

.btn-orange:hover {
  background-color: #e65c00;
}

</style>

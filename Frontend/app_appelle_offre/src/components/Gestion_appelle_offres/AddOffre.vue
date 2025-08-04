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

              <!-- Titre -->
              <div class="col-md-6">
                <label class="form-label">Titre</label>
                <input v-model="form.titre" type="text" class="form-control" />
                <div class="text-danger" v-if="form.titre && (form.titre.length < 3 || form.titre.length > 90)">
                  Le titre doit contenir entre 3 et 90 caractères.
                </div>
              </div>

              <!-- Budget -->
              <div class="col-md-6">
                <label class="form-label">Budget (TND)</label>
                <input v-model.number="form.budget" type="number" class="form-control" />
                <div class="text-danger" v-if="form.budget !== '' && form.budget <= 0">
                  Le budget doit être supérieur à 0.
                </div>
              </div>

              <!-- Description -->
              <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                <div class="text-danger" v-if="form.description && (form.description.length < 3)">
                  La description doit contenir entre 3
                </div>
              </div>

              <!-- Date début -->
              <div class="col-md-6">
                <label class="form-label">Date de début</label>
                <input v-model="form.date_debut" type="date" class="form-control" />
                <div class="text-danger" v-if="form.date_debut && !isDebutValid">
                  La date de début doit être entre J+5 et dans les 12 mois à venir.
                </div>
              </div>

              <!-- Date fin -->
              <div class="col-md-6">
                <label class="form-label">Date de fin</label>
                <input v-model="form.date_fin" type="date" class="form-control" />
                <div class="text-danger" v-if="form.date_fin && !isFinValid">
                  La date de fin doit être après la date de début et avant l’année 2027.
                </div>
              </div>

              <!-- Domaine -->
              <div class="col-md-12">
                <label class="form-label">Domaine</label>
                <select v-model="form.idDomaine" class="form-select">
                  <option disabled value="">-- Sélectionnez un domaine --</option>
                  <option v-for="domaine in domaines" :key="domaine.idDomaine" :value="domaine.idDomaine">
                    {{ domaine.nom }}
                  </option>
                </select>
                <div class="text-danger" v-if="form.idDomaine === ''">Le domaine est requis.</div>
              </div>

            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-success" :disabled="!isFormValid">
              Ajouter
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>



<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/Http/api'
import { useStore } from 'vuex'

// Store et utilisateur
const store = useStore()
const user = computed(() => store.state.auth.user)

// Formulaire
const form = ref({
  titre: '',
  description: '',
  date_debut: '',
  date_fin: '',
  budget: '',
  idDomaine: '',
  idUser: null
})

// Domaines (chargés dynamiquement)
const domaines = ref([])

onMounted(async () => {
  try {
    const res = await api.get('http://localhost:8000/api/domaines')
    domaines.value = res.data
  } catch (err) {
    console.error('Erreur chargement domaines:', err)
  }
})

// Fonctions de validation
const isTitreValid = computed(() =>
  form.value.titre.length >= 3 && form.value.titre.length <= 90
)

const isBudgetValid = computed(() => form.value.budget > 0)

const isDescriptionValid = computed(() =>
  form.value.description.length >= 3
)

const isDebutValid = computed(() => {
  if (!form.value.date_debut) return false
  const debut = new Date(form.value.date_debut)
  const today = new Date()
  const minDate = new Date(today)
  minDate.setDate(today.getDate() + 5)
  const maxDate = new Date(today)
  maxDate.setMonth(today.getMonth() + 12)
  return debut >= minDate && debut <= maxDate
})

const isFinValid = computed(() => {
  if (!form.value.date_fin || !form.value.date_debut) return false
  const fin = new Date(form.value.date_fin)
  const debut = new Date(form.value.date_debut)
  const maxFin = new Date('2027-01-01')
  return fin > debut && fin < maxFin
})

const isDomaineValid = computed(() => form.value.idDomaine !== '')

const isFormValid = computed(() =>
  isTitreValid.value &&
  isBudgetValid.value &&
  isDescriptionValid.value &&
  isDebutValid.value &&
  isFinValid.value &&
  isDomaineValid.value
)

// Soumission du formulaire
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

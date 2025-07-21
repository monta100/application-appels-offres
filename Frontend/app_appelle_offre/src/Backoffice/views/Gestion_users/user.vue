<template>
  <div class="d-flex justify-content-center mb-4">
    <div class="input-group" style="max-width: 500px;">
      <input
        v-model="searchTerm"
        type="text"
        class="form-control border-primary"
        placeholder="🔍 Rechercher par nom, prénom ou email"
      />
      <button class="btn btn-outline-primary" @click="searchTerm = ''">❌</button>
    </div>
  </div>

  <div class="container mt-4">
    <h3 class="mb-3 text-center text-primary">Liste des Utilisateurs Inscrits</h3>

    <!-- Statistiques -->
    <div class="text-center mb-4">
      <span class="badge bg-primary me-2">Total : {{ users.length }}</span>
      <span class="badge bg-success me-2">Participants : {{ totalByRole('participant') }}</span>
      <span class="badge bg-warning text-dark me-2">Représentants : {{ totalByRole('representant') }}</span>
      <span class="badge bg-danger">Admins : {{ totalByRole('admin') }}</span>
    </div>

    <!-- Spinner -->
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
    </div>

    <!-- Tableau -->
    <div v-else>
      <table class="table table-bordered table-hover shadow-sm rounded text-center">
        <thead class="table-dark">
            
          <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Rôle</th>
            <th>Date de création</th>
            <th>Etat</th>

          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) in paginatedUsers" :key="user.idUser">
            <td>{{ (currentPage - 1) * usersPerPage + index + 1 }}</td>
            <td>{{ user.nom }}</td>
            <td>{{ user.prenom }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.telephone || 'Non fourni' }}</td>
            <td>
              <span :class="getRoleBadge(user.role)">
                {{ user.role || 'Non défini' }}
              </span>
            </td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
  <button
    :class="user.is_active ? 'btn btn-sm btn-success' : 'btn btn-sm btn-secondary'"
    @click="toggleActivation(user)"
  >
    {{ user.is_active ? 'Activé' : 'Désactivé' }}
  </button>
</td>

          </tr>

          
        </tbody>
      </table>

      <!-- Pagination -->
      <nav class="d-flex justify-content-center mt-4">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Précédent</a>
          </li>
          <li
            v-for="page in totalPages"
            :key="page"
            class="page-item"
            :class="{ active: currentPage === page }"
          >
            <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Suivant</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import api from '@/Http/api';

const users = ref([]);
const loading = ref(true);
const searchTerm = ref('');
const currentPage = ref(1);
const usersPerPage = 5;

const toggleActivation = async (user) => {
  try {
    const response = await api.patch(`/users/${user.idUser }/toggle-active`);
    user.is_active = response.data.is_active;
  } catch (err) {
    console.error('Erreur lors du changement de statut :', err);
  }
};

// Récupération des utilisateurs
const fetchUsers = async () => {
  try {
    const response = await api.get('/users');
    users.value = response.data;
  } catch (err) {
    console.error('Erreur lors de la récupération des utilisateurs:', err);
  } finally {
    loading.value = false;
  }
};

// Format date
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('fr-FR');
};

// Filtrage
const filteredUsers = computed(() => {
  if (!searchTerm.value) return users.value;
  return users.value.filter(user =>
    (user.nom + user.prenom + user.email).toLowerCase().includes(searchTerm.value.toLowerCase())
  );
});

// Pagination
const totalPages = computed(() => Math.ceil(filteredUsers.value.length / usersPerPage));

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * usersPerPage;
  return filteredUsers.value.slice(start, start + usersPerPage);
});

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Badge rôle
const getRoleBadge = (role) => {
  switch (role) {
    case 'admin':
      return 'badge bg-danger';
    case 'participant':
      return 'badge bg-success';
    case 'representant':
      return 'badge bg-warning text-dark';
    default:
      return 'badge bg-secondary';
  }
};

// Statistique par rôle
const totalByRole = (role) => {
  return users.value.filter(u => u.role === role).length;
};

onMounted(fetchUsers);
</script>

<style scoped>
.container {
  max-width: 1000px;
}

.badge {
  font-size: 0.9em;
  padding: 0.5em 0.7em;
}
</style>

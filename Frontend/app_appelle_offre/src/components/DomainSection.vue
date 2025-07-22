<template>
  <section class="domaines-section py-5 bg-light text-center">
    <div class="container">
      <h2 class="mb-4">Domaines d'intervention</h2>
      <div class="row g-4">
        <div class="col-md-3" v-for="(d, i) in domaines" :key="i">
          <div class="card shadow-sm p-3 border-0 h-100">
            <div class="icon mb-3">
              <i :class="d.icon" style="font-size: 2.5rem; color: #f39c12;"></i>
            </div>
            <h5>{{ d.title }}</h5>
            <p class="text-muted">{{ d.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const domaines = ref([
  {
    title: 'Informatique',
    icon: 'fa fa-laptop',
    description: 'Développement web, logiciels, cybersécurité.'
  },
  {
    title: 'Construction',
    icon: 'fa fa-home',
    description: 'BTP, travaux publics, rénovation.'
  },
  {
    title: 'Marketing',
    icon: 'fa fa-bullhorn',
    description: 'Publicité, communication digitale, branding.'
  },
  {
    title: 'Design',
    icon: 'fa fa-paint-brush',
    description: 'UI/UX design, graphisme, identité visuelle.'
  }
])
</script>

<style scoped>
.domaines-section h2 {
  font-weight: 700;
}
.card {
  transition: transform 0.3s ease;
}
.card:hover {
  transform: translateY(-5px);
}
</style>

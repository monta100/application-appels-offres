<template>
  <div class="card p-4 d-flex justify-content-between align-items-center" style="background: linear-gradient(90deg, #1e1e6b, #2f2f91); color: white;">
    <div class="d-flex align-items-center justify-content-between w-100">
      <div class="d-flex align-items-center">
        <i class="fas fa-bolt me-2"></i>
        <div>
          <h6 class="mb-0 fw-bold">ACTIVITÉ GLOBALE</h6>
          <small>Basé sur les actions de cette semaine</small>
        </div>
      </div>
      <h4 class="fw-bold mb-0">{{ score }}%</h4>
    </div>
    <div class="progress mt-3 w-100" style="height: 6px;">
      <div class="progress-bar bg-white" :style="{ width: score + '%' }"></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    score: {
      type: Number,
      required: true
    }
  }
}
</script>

<style scoped>
.card {
  border-radius: 12px;
}
</style>

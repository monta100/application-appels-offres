<template>
  <section class="stats-section py-5 bg-white shadow-sm">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-3 col-6 mb-4 stat-box">
          <div class="stat-card">
            <i class="fa fa-bullhorn icon"></i>
            <h2 class="fw-bold">+250</h2>
            <p class="text-muted">Appels d'offres publiés</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-4 stat-box">
          <div class="stat-card">
            <i class="fa fa-building icon"></i>
            <h2 class="fw-bold">+120</h2>
            <p class="text-muted">Entreprises inscrites</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-4 stat-box">
          <div class="stat-card active">
            <i class="fa fa-user-tie icon"></i>
            <h2 class="fw-bold">+300</h2>
            <p class="text-muted">Prestataires actifs</p>
          </div>
        </div>
        <div class="col-md-3 col-6 stat-box">
          <div class="stat-card">
            <i class="fa fa-smile icon"></i>
            <h2 class="fw-bold">98%</h2>
            <p class="text-muted">Taux de satisfaction</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.stat-card {
  background: #fff;
  border-radius: 16px;
  padding: 30px 15px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.stat-card.active {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border: 1px solid #ddd;
}

.icon {
  font-size: 32px;
  color: #f7941d;
  margin-bottom: 10px;
}
</style>

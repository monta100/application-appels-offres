<template>
  <section class="cta-section">
    <!-- SVG décoratif (haut) -->
    <svg class="svg-top" viewBox="0 0 1440 320">
      <path
        fill="#f9fafb"
        d="M0,160L80,170.7C160,181,320,203,480,186.7C640,171,800,117,960,101.3C1120,85,1280,107,1360,117.3L1440,128V0H0Z"
      />
    </svg>

    <div class="cta-content">
      <div class="icon-container">
        <i class="fas fa-users"></i>
      </div>
      <h2>Rejoignez-nous dès maintenant !</h2>
      <p>
        Inscrivez-vous pour accéder aux appels d'offres et collaborer avec des
        entreprises fiables.
      </p>
      <router-link to="/sign-up" class="cta-button">S'inscrire</router-link>
      <p class="testimonial">
        Déjà plus de <strong>300 prestataires</strong> nous ont fait confiance.
      </p>
    </div>
  </section>
</template>

<style scoped>
.cta-section {
  position: relative;
  background: linear-gradient(135deg, #f12711, #f5af19);
  padding: 80px 20px;
  color: #fff;
  text-align: center;
  overflow: hidden;
}

.svg-top {
  position: absolute;
  top: -1px;
  left: 0;
  width: 100%;
  height: 60px;
}

.cta-content {
  max-width: 800px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.icon-container {
  font-size: 48px;
  margin-bottom: 20px;
}

.cta-content h2 {
  font-size: 32px;
  font-weight: 800;
  margin-bottom: 16px;
}

.cta-content p {
  font-size: 18px;
  margin-bottom: 24px;
}

.cta-button {
  display: inline-block;
  background: white;
  color: #f12711;
  font-weight: bold;
  padding: 12px 30px;
  border-radius: 30px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.cta-button:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.testimonial {
  margin-top: 20px;
  font-style: italic;
  font-size: 16px;
  opacity: 0.9;
}
</style>

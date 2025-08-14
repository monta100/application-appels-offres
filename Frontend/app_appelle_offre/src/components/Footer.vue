<template>
  <footer class="footer-area section-padding">
    <div class="footer-widget">
      <div class="container">
        <div class="row align-items-start">
          
          <!-- Logo + Slogan -->
          <div class="col-xl-4 col-lg-4 mb-4">
            <h2 class="fw-bold text-white">Comport</h2>
            <p class="slogan">Le portail intelligent des opportunités</p>
            <p class="desc">
              Plateforme professionnelle de gestion des appels d’offres, 
              des soumissions et des contrats, avec IA intégrée et communication en temps réel.
            </p>
          </div>

          <!-- Contact -->
          <div class="col-xl-3 col-lg-3 mb-4">
            <h5 class="text-white fw-bold mb-3">Contact</h5>
            <ul class="footer-list">
              <li>📍 Tunis, Tunisie</li>
              <li>📧 <a href="mailto:contact@comport.com">contact@comport.com</a></li>
              <li>📞 +216 12 345 678</li>
            </ul>
            <div class="social-icons mt-3">
              <a href="#"><i class="fa fa-linkedin"></i></a>
              <a href="#"><i class="fa fa-facebook"></i></a>
              <a href="#"><i class="fa fa-twitter"></i></a>
            </div>
          </div>

          <!-- Univers -->
          <div class="col-xl-5 col-lg-5">
            <h5 class="text-white fw-bold mb-3">Notre univers</h5>
            <div class="feed">
              <img
                v-for="(img, index) in feedImages"
                :key="index"
                :src="img"
                alt="feed image"
              />
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Bas de page -->
    <div class="footer-bottom text-center mt-4">
      <span>&copy; {{ currentYear }} Comport — Tous droits réservés.</span>
    </div>
  </footer>
</template>

<script setup>
const currentYear = new Date().getFullYear()
const feedImages = Array.from({ length: 8 }, (_, i) =>
  `/assets/images/feed${i + 1}.jpg`
)
</script>

<style scoped>
.footer-area {
  background: #0a0a1a;
  color: #bbb;
  padding-top: 2rem;
}
.slogan {
  font-size: 0.95rem;
  color: #ff7900;
  margin-bottom: 1rem;
}
.desc {
  font-size: 0.85rem;
  line-height: 1.5;
}
.footer-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.footer-list li {
  margin-bottom: 0.5rem;
}
.feed {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 5px;
}
.feed img {
  width: 100%;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
}
.social-icons a {
  display: inline-block;
  margin-right: 8px;
  font-size: 1.2rem;
  color: #fff;
}
.social-icons a:hover {
  color: #ff7900;
}
.footer-bottom {
  border-top: 1px solid #222;
  padding: 1rem 0;
  font-size: 0.85rem;
  color: #888;
}
</style>

<template>
  <footer class="footer-area section-padding">
    <div class="footer-widget">
      <div class="container">
        <div class="row">
          <!-- Produits -->
          <div class="col-xl-2 col-lg-3">
            <div class="single-widget-home mb-5 mb-lg-0">
              <h3 class="mb-4">Top Products</h3>
              <ul>
                <li class="mb-2"><a href="#">Managed Website</a></li>
                <li class="mb-2"><a href="#">Managed Reputation</a></li>
                <li class="mb-2"><a href="#">Power Tools</a></li>
                <li><a href="#">Marketing Service</a></li>
              </ul>
            </div>
          </div>

          <!-- Newsletter -->
          <div class="col-xl-5 offset-xl-1 col-lg-6">
            <div class="single-widget-home mb-5 mb-lg-0">
              <h3 class="mb-4">Newsletter</h3>
              <p class="mb-4">You can trust us. We only send promo offers.</p>
              <form @submit.prevent>
                <input type="email" placeholder="Your email here" required />
                <button type="submit" class="template-btn">Subscribe Now</button>
              </form>
            </div>
          </div>

          <!-- Instagram Feed -->
          <div class="col-xl-3 offset-xl-1 col-lg-3">
            <div class="single-widge-home">
              <h3 class="mb-4">Instagram Feed</h3>
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
    </div>

    <!-- Copyright + Réseaux -->
    <div class="footer-copyright">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 col-md-6">
            <span>
              &copy; {{ currentYear }} All rights reserved |
              Template made with <i class="fa fa-heart-o"></i> by
              <a href="https://colorlib.com" target="_blank">Colorlib</a>
            </span>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="social-icons">
              <ul>
                <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                <li><a href="#"><i class="fa fa-dribbble"></i></a></li>
                <li><a href="#"><i class="fa fa-behance"></i></a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
// Année actuelle
const currentYear = new Date().getFullYear()

// Images statiques depuis public/assets/images
const feedImages = Array.from({ length: 8 }, (_, i) =>
  `/assets/images/feed${i + 1}.jpg`

)



import { onMounted } from 'vue'

onMounted(() => {
  // Vérifie que jQuery est bien chargé
  if (typeof window.$ === 'function') {
    // Relance OwlCarousel
    $('.employee-slider').owlCarousel({
      loop: true,
      margin: 20,
      autoplay: true,
      autoplayTimeout: 2000,
      autoplayHoverPause: true,
      nav: false,
      dots: true,
      responsiveClass: true,
      responsive: {
        0: { items: 1 },
        576: { items: 1 },
        768: { items: 1 },
        992: { items: 2 }
      }
    })

    // Active Nice Select si besoin
    $('select').niceSelect()

    // Active WOW.js
    new WOW().init()
  } else {
    console.warn('jQuery non chargé. Les effets du template ne seront pas appliqués.')
  }
})

</script>

<style scoped>
/* Tu peux ajouter ici des styles personnalisés si besoin */
</style>

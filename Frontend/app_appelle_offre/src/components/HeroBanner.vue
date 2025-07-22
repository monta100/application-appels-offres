<template>
  <section class="banner-area">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-6 px-0">
          <div class="banner-bg"></div>
        </div>
        <div class="col-lg-6 align-self-center">
          <div class="banner-text">
          <h1>
          Publiez ou trouvez votre <span>appel d’offre</span> en toute simplicité
          </h1>
          <p class="py-3">
         Une plateforme intelligente pour connecter les entreprises à des prestataires qualifiés et gérer efficacement les appels d’offres.
          </p>
         <a href="#" class="secondary-btn">Découvrir la plateforme<span class="flaticon-next"></span></a>
        </div>

        </div>
      </div>
    </div>
  </section>
  
</template>

<script>
export default {
  name: "HeroBanner",
};
</script>

<style scoped>
.banner-bg {
  background-image: url("/assets/images/banner-bg.jpg");
  background-size: cover;
  background-position: center;
  height: 100%;
  min-height: 400px;
}
</style>

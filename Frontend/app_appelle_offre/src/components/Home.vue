<template>
  <div>
    <Navbar />
    <HeroBanner />
    <StatsSection />
    <DomainSection />
    <JoinNowSection />


    <Footer />
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import HeroBanner from '@/components/HeroBanner.vue'
import Footer from '@/components/Footer.vue'
import StatsSection from './StatsSection.vue'
import DomainSection from '@/components/DomainSection.vue'
import JoinNowSection from './JoinNowSection.vue'
export default {
  components: {
    Navbar,
    HeroBanner,
    Footer,
    StatsSection,
    DomainSection,
    JoinNowSection

  }
}
</script>

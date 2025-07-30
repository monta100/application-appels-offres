<template>
        <Navbar />

  <div class="container">
    <h1>🤖 Assistant intelligent</h1>
    <Chatbot />
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import Chatbot from './Chatbot.vue';
export default {
  components: { Chatbot,Navbar }
};
</script>

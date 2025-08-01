<template>
        <Navbar />

  <div class="container">
    <h1>🤖 Assistant intelligent</h1>
    <Chatbot />
    <AssistantAide />
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import Chatbot from './Chatbot.vue';
import AssistantAide from './AssistantAide.vue';
export default {
  components: { Chatbot,Navbar,AssistantAide }
};
</script>

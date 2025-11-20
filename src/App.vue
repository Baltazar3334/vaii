<script setup>
import { ref } from 'vue' // Pridame ref
import { RouterLink, RouterView } from 'vue-router'
import QuizCreatorModal from '@/components/quiz/QuizCreatorModal.vue' // Import modalu

// Stav pre zobrazenie modalu
const showCreateModal = ref(false)
</script>

<template>
  <header>
    <div class="wrapper">
      <nav>
        <RouterLink to="/">Home</RouterLink>
        <!-- Zmena z RouterLink na <a> s click eventom -->
        <a href="#" @click.prevent="showCreateModal = true">Create Quiz</a>
        <RouterLink to="/play">Play Quiz</RouterLink>
        <RouterLink to="/profile">Profile</RouterLink>
        <RouterLink to="/login" style="color: #8b5cf6;">Login</RouterLink>
      </nav>
    </div>
  </header>

  <RouterView />

  <!-- Modal component - zobrazí sa len ak je showCreateModal true -->
  <QuizCreatorModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
  />
</template>

<style scoped>
header {
  line-height: 1.5;
  max-height: 100vh;
  background-color: #f8f9fa; /* Light gray background */
  padding: 1rem;
  border-bottom: 1px solid #ddd;
}

nav {
  width: 100%;
  font-size: 18px;
  text-align: center;
}

/* Upravíme selektor aby fungoval aj na obyčajné <a> v navigácii */
nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
  color: #2c3e50;
  text-decoration: none;
  cursor: pointer; /* Pridaný kurzor */
}

nav a:first-of-type {
  border: 0;
}

nav a.router-link-exact-active {
  color: #42b883; /* Vue Green */
  font-weight: bold;
}

nav a:hover {
  background-color: transparent;
  color: #42b883;
}
</style>
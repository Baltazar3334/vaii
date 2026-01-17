<script setup>
import { ref, onMounted, provide } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import QuizCreatorModal from '@/components/quiz/QuizCreatorModal.vue'

const router = useRouter()
const showCreateModal = ref(false)
const currentUser = ref(null)

// Function to check if user is logged in
const checkUser = () => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      currentUser.value = JSON.parse(userStr)
    } catch (e) {
      currentUser.value = null
    }
  } else {
    currentUser.value = null
  }
}

// Logout function
const handleLogout = () => {
  localStorage.removeItem('user')
  currentUser.value = null
  router.push('/')
}

// Check user on mount
onMounted(() => {
  checkUser()
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})

// Provide this function to children so AuthView can call it after login
provide('updateUser', checkUser)
</script>

<template>
  <header>
    <div class="wrapper">
      <nav>
        <RouterLink to="/">Home</RouterLink>
        
        <!-- Show Create Quiz only if logged in -->
        <a v-if="currentUser" href="#" @click.prevent="showCreateModal = true">Create Quiz</a>
        
        <!-- Show Profile only if logged in -->
        <RouterLink v-if="currentUser" to="/profile">Profile</RouterLink>
        <RouterLink v-if="currentUser" to="/settings">Settings</RouterLink>

        <!-- Show Login only if NOT logged in -->
        <RouterLink v-if="!currentUser" to="/login" style="color: #8b5cf6;">Login</RouterLink>
        
        <!-- Show Logout if logged in -->
        <a v-else href="#" @click.prevent="handleLogout" style="color: #ef4444;">Logout ({{ currentUser.username }})</a>
      </nav>
    </div>
  </header>

  <RouterView />

  <QuizCreatorModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
  />
</template>

<style scoped>
/* ... existing styles ... */
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


nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
  color: #2c3e50;
  text-decoration: none;
  cursor: pointer;
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
<script setup>
import { ref, onMounted, provide } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import QuizCreatorModal from '@/components/quiz/QuizCreatorModal.vue'

// Inicializácia routera a stavov pre modálne okno a prihláseného používateľa
const router = useRouter()
const showCreateModal = ref(false)
const currentUser = ref(null)

// Funkcia na kontrolu a načítanie údajov o používateľovi z localStorage
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

// Vymazanie údajov používateľa pri odhlásení a návrat na úvodnú stránku
const handleLogout = () => {
  localStorage.removeItem('user')
  currentUser.value = null
  router.push('/')
}

// Spustenie počiatočných kontrol a nastavenie vizuálnej témy pri načítaní
onMounted(() => {
  checkUser()
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})

// Poskytovanie metód a reaktívnych signálov pre vnorené komponenty
provide('updateUser', checkUser)
const refreshSignal = ref(0)
const triggerRefresh = () => { refreshSignal.value++ }
provide('refreshSignal', refreshSignal)
provide('triggerRefresh', triggerRefresh)
</script>

<template>
  <header>
    <div class="wrapper">
      <!-- Hlavná navigačná lišta s podmieneným zobrazením prvkov -->
      <nav>
        <RouterLink to="/">Home</RouterLink>

        <!-- Akcia na otvorenie tvorcu kvízov prístupná len prihláseným -->
        <a v-if="currentUser" href="#" @click.prevent="showCreateModal = true">Create Quiz</a>

        <RouterLink v-if="currentUser" to="/profile">Profile</RouterLink>
        <RouterLink v-if="currentUser" to="/settings">Settings</RouterLink>
        <RouterLink to="/stats">Statistics</RouterLink>

        <!-- Prepínanie medzi odkazom na Login a informáciou o používateľovi s Logoutom -->
        <RouterLink v-if="!currentUser" to="/login" style="color: #8b5cf6;">Login</RouterLink>

        <a v-else href="#" @click.prevent="handleLogout" style="color: #ef4444;">Logout ({{ currentUser.username }})</a>
      </nav>
    </div>
  </header>

  <!-- Dynamický výstup aktuálnej stránky na základe routingu -->
  <RouterView />

  <!-- Globálne modálne okno pre vytváranie nových kvízov -->
  <QuizCreatorModal
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @saved="triggerRefresh"
  />
</template>

<style scoped>
/* Štýlovanie fixnej hlavičky v hornej časti obrazovky */
header {
  line-height: 1.5;
  max-height: 100vh;
  background-color: var(--header-bg);
  padding: 1rem;
  border-bottom: 1px solid var(--color-border);
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

nav {
  width: 100%;
  font-size: 18px;
  text-align: center;
}

/* Vizuálne oddelenie odkazov v navigácii */
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

/* Zvýraznenie aktívnej cesty v menu */
nav a.router-link-exact-active {
  color: #42b883;
  font-weight: bold;
}

nav a:hover {
  background-color: transparent;
  color: #42b883;
}
</style>
<script setup>
import { ref, onMounted, inject, watch, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import QuizCreatorModal from '@/components/quiz/QuizCreatorModal.vue'

// Inicializácia routera a systémov na obnovu dát
const router = useRouter()
const route = useRoute()
const refreshSignal = inject('refreshSignal')

// Výpočet ID profilu a kontrola, či používateľ prezerá vlastný profil
const profileId = computed(() => route.params.id)
const loggedInUser = JSON.parse(localStorage.getItem('user') || '{}')
const isOwnProfile = computed(() => !profileId.value || parseInt(profileId.value) === parseInt(loggedInUser.id))

// Reaktívne stavy pre dáta používateľa a zoznam kvízov
const user = ref({ id: null, username: 'Guest', initial: '?', stats: { created: 0, plays: 0, questions: 0 } })
const userQuizzes = ref([])
const isLoading = ref(true)

// Stavy pre modálne okná (mazanie a úprava)
const showDeleteModal = ref(false)
const quizToDelete = ref(null)
const showEditModal = ref(false)
const quizToEdit = ref(null)

// Funkcia na načítanie kvízov a štatistík používateľa z API
const fetchUserQuizzes = async (userId) => {
  if (!userId) return
  isLoading.value = true
  try {
    const response = await fetch(`http://localhost:8000/backend/api.php?action=get_user_quizzes&user_id=${userId}`, {
      credentials: 'include'
    })
    const result = await response.json()
    if (result.success) {
      // Ak nie je vlastný profil, filtrujú sa len verejné kvízy
      userQuizzes.value = isOwnProfile.value
          ? result.quizzes
          : result.quizzes.filter(q => parseInt(q.is_public) === 1)

      // Aktualizácia informácií o používateľovi a jeho štatistík
      user.value.username = result.username
      user.value.avatar_url = result.avatar_url
      user.value.initial = result.username.charAt(0).toUpperCase()
      user.value.stats.created = userQuizzes.value.length
      user.value.stats.plays = result.total_plays || 0
      user.value.stats.questions = result.quizzes.reduce((sum, quiz) => sum + parseInt(quiz.question_count || 0), 0)
    }
  } catch (error) {
    console.error("Failed to load quizzes:", error)
  } finally {
    isLoading.value = false
  }
}

// Otvorenie potvrdzovacieho okna pre zmazanie kvízu
const confirmDelete = (quiz) => {
  quizToDelete.value = quiz
  showDeleteModal.value = true
}

// Odoslanie požiadavky na zmazanie kvízu do backendu
const handleDelete = async () => {
  if (!quizToDelete.value) return
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=delete_quiz', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ quiz_id: quizToDelete.value.id, user_id: user.value.id }),
      credentials: 'include'
    })
    const res = await response.json()
    if (res.success) {
      // Odstránenie kvízu zo lokálneho stavu po úspešnom zmazaní
      userQuizzes.value = userQuizzes.value.filter(q => q.id !== quizToDelete.value.id)
      showDeleteModal.value = false
      fetchUserQuizzes(profileId.value || loggedInUser.id)
    }
  } catch (e) { console.error(e) }
}

// Príprava dát pre modálne okno na úpravu kvízu
const openEditModal = (quiz) => {
  quizToEdit.value = quiz
  showEditModal.value = true
}

// Manuálna obnova dát po uložení zmien
const handleRefresh = () => {
  fetchUserQuizzes(profileId.value || loggedInUser.id)
}

// Sledovanie signálov na obnovu a zmien v URL parametri ID
watch(refreshSignal, handleRefresh)
watch(() => route.params.id, (newId) => {
  fetchUserQuizzes(newId || loggedInUser.id)
})

// Načítanie dát pri prvom pripojení komponentu
onMounted(() => {
  const targetId = profileId.value || loggedInUser.id
  if (targetId) {
    fetchUserQuizzes(targetId)
  } else {
    // Presmerovanie na login, ak nie je k dispozícii žiadne ID
    router.push('/login')
  }
})
</script>
<template>
  <div class="profile-container">
    <!-- Horný panel s informáciami o používateľovi a štatistikami -->
    <div class="profile-header">
      <div class="header-wrapper">
        <div class="user-welcome">
          <div class="welcome-text">
            <h1>{{ isOwnProfile ? 'Welcome back,' : 'Profile of' }} {{ user.username }}!</h1>
            <p>{{ isOwnProfile ? 'Manage your quizzes and track your progress' : 'Explore quizzes from this creator' }}</p>
          </div>
          <!-- Zobrazenie avataru alebo iniciály mena -->
          <div class="avatar">
            <img v-if="user.avatar_url" :src="user.avatar_url" alt="Avatar" class="avatar-img" @error="user.avatar_url = null" />
            <span v-else>{{ user.initial }}</span>
          </div>
        </div>

        <!-- Mriežka so súhrnnými štatistikami -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.created }}</span>
              <span class="stat-label"> Quizzes {{ isOwnProfile ? 'Created' : '' }}</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.plays }}</span>
              <span class="stat-label"> Total Plays</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">@</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.questions }}</span>
              <span class="stat-label"> Questions Written</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sekcia so zoznamom kvízov -->
    <div class="content-container">
      <div class="section-header">
        <h2>{{ isOwnProfile ? 'Your Quizzes' : 'Public Quizzes' }}</h2>
      </div>

      <div v-if="isLoading" class="loading-state">Loading quizzes...</div>
      <div v-else-if="userQuizzes.length === 0" class="empty-state">
        <h3>No quizzes yet</h3>
      </div>

      <!-- Zobrazenie kariet s kvízmi -->
      <div v-else class="quiz-grid">
        <div v-for="quiz in userQuizzes" :key="quiz.id" class="quiz-card">
          <!-- Ovládacie prvky pre majiteľa profilu -->
          <div v-if="isOwnProfile" class="actions-container">
            <button class="action-btn edit-btn" @click="openEditModal(quiz)" title="Edit Quiz">✎</button>
            <button class="action-btn delete-btn" @click="confirmDelete(quiz)" title="Delete Quiz">×</button>
          </div>

          <div class="card-top">
            <h3>{{ quiz.title }}</h3>
            <p class="description">{{ quiz.description || 'No description provided.' }}</p>
          </div>
          <div class="card-bottom">
            <!-- Informačné štítky o kvíze -->
            <div class="badges-row">
              <span class="badge questions">{{ quiz.question_count }} Questions</span>
              <span class="badge plays">{{ quiz.plays_count || 0 }} Plays</span>
              <span v-if="isOwnProfile" class="badge status" :class="parseInt(quiz.is_public) === 1 ? 'public' : 'private'">
                {{ parseInt(quiz.is_public) === 1 ? 'Public' : 'Private' }}
              </span>
            </div>
            <button class="play-btn" @click="router.push({ name: 'play', query: { id: quiz.id } })">Play</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modálne okno pre editor kvízov -->
    <QuizCreatorModal
        v-if="showEditModal"
        :editData="quizToEdit"
        @close="showEditModal = false"
        @saved="handleRefresh"
    />

    <!-- Potvrdzovacie okno pre zmazanie -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="delete-card">
        <h3>Delete Quiz?</h3>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showDeleteModal = false">Cancel</button>
          <button class="btn-delete" @click="handleDelete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Základné rozloženie profilu */
.profile-container { min-height: 100vh; background-color: var(--color-background-soft); color: var(--color-text); font-family: 'Inter', sans-serif; }
/* Vizuálna hlavička s prechodom */
.profile-header { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); padding: 3rem 2rem 5rem; color: white; display: flex; justify-content: center; }
.header-wrapper { max-width: 1000px; width: 100%; }
/* Rozloženie uvítania a avataru */
.user-welcome { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; }
.welcome-text h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: white; }
.welcome-text p { color: rgba(255, 255, 255, 0.8); }
.avatar {
  width: 80px; height: 80px;
  background-color: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem;
  font-weight: 600;
  overflow: hidden;
  flex-shrink: 0;
}
.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Štýlovanie štatistických kariet */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.stat-card { background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; backdrop-filter: blur(5px); }
.stat-value { font-size: 1.25rem; font-weight: 700; color: white; }
.stat-label { font-size: 0.8rem; color: rgba(255, 255, 255, 0.7); }

/* Kontajner pre hlavný obsah */
.content-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
.section-header h2 { font-size: 1.5rem; color: var(--color-text); font-weight: 600; }

/* Mriežka a karty kvízov */
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
.quiz-card { position: relative; background: var(--card-bg); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.quiz-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

/* Textové prvky karty */
.description {
  font-size: 0.9rem; color: var(--color-text); opacity: 0.7; line-height: 1.4;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: 2.8em;
}

/* Tlačidlá akcií v rohu karty */
.actions-container { position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; }
.action-btn { background: transparent; border: none; cursor: pointer; color: var(--color-text); opacity: 0.4; font-size: 1.2rem; transition: opacity 0.2s; }
.action-btn:hover { opacity: 1; }
.delete-btn:hover { color: #ef4444; }
.edit-btn:hover { color: #3b82f6; }

/* Štýlovanie štítkov a spodnej časti karty */
.card-top h3 {
  color: var(--color-text);
  margin-bottom: 0.5rem;
  padding-right: 50px;
  display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-all;
}
.card-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; gap: 1rem; }
.badges-row { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.badge { background-color: var(--color-background-soft); color: var(--color-text); font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; font-weight: 600; border: 1px solid var(--color-border); }

/* Farebné varianty štítkov */
.badge.status.public { color: #10b981; background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.2); }
.badge.status.private { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.2); }
.badge.plays { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); border-color: rgba(139, 92, 246, 0.2); }

/* Hlavné tlačidlo pre hranie */
.play-btn { background: linear-gradient(135deg, #8b5cf6, #3b82f6); color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: transform 0.2s; flex-shrink: 0; }
.play-btn:hover { transform: scale(1.05); }

/* Responzivita pre menšie obrazovky */
@media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
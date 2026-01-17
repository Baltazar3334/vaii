<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import QuizCreatorModal from '@/components/quiz/QuizCreatorModal.vue'

const router = useRouter()
const user = ref({ id: null, username: 'Guest', initial: '?', stats: { created: 0, plays: 0, questions: 0 } })
const userQuizzes = ref([])
const isLoading = ref(true)

// Stavy pre modaly
const showDeleteModal = ref(false)
const quizToDelete = ref(null)
const showEditModal = ref(false)
const quizToEdit = ref(null)

const fetchUserQuizzes = async (userId) => {
  try {
    const response = await fetch(`http://localhost:8000/backend/api.php?action=get_user_quizzes&user_id=${userId}`)
    const result = await response.json()
    if (result.success) {
      userQuizzes.value = result.quizzes
      user.value.stats.created = result.quizzes.length
      user.value.stats.questions = result.quizzes.reduce((sum, quiz) => sum + parseInt(quiz.question_count || 0), 0)
    }
  } catch (error) {
    console.error("Failed to load quizzes:", error)
  } finally {
    isLoading.value = false
  }
}

// Logika mazania
const confirmDelete = (quiz) => {
  quizToDelete.value = quiz
  showDeleteModal.value = true
}

const handleDelete = async () => {
  if (!quizToDelete.value) return
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=delete_quiz', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ quiz_id: quizToDelete.value.id, user_id: user.value.id })
    })
    if ((await response.json()).success) {
      userQuizzes.value = userQuizzes.value.filter(q => q.id !== quizToDelete.value.id)
      showDeleteModal.value = false
    }
  } catch (e) { console.error(e) }
}

// Logika úpravy - otvorí náš vylepšený QuizCreatorModal
const openEditModal = (quiz) => {
  quizToEdit.value = quiz
  showEditModal.value = true
}

const handleRefresh = () => {
  fetchUserQuizzes(user.value.id)
}

onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    const userData = JSON.parse(userStr)
    user.value = { ...user.value, ...userData, initial: userData.username.charAt(0).toUpperCase() }
    fetchUserQuizzes(userData.id)
  } else {
    router.push('/login')
  }
})
</script>

<template>
  <div class="profile-container">
    <!-- Header Section -->
    <div class="profile-header">
      <div class="header-wrapper">
        <div class="user-welcome">
          <div class="welcome-text">
            <h1>Welcome back, {{ user.username }}!</h1>
            <p>Manage your quizzes and track your progress</p>
          </div>
          <div class="avatar">{{ user.initial }}</div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.created }}</span>
              <span class="stat-label"> Quizzes Created</span>
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

    <!-- Main Content Section -->
    <div class="content-container">
      <div class="section-header">
        <h2>Your Quizzes</h2>
        <p>Create, edit, and manage your quiz collection</p>
      </div>

      <div v-if="isLoading" class="loading-state">Loading your quizzes...</div>
      <div v-else-if="userQuizzes.length === 0" class="empty-state">
        <div class="trophy-icon">🏆</div>
        <h3>No quizzes yet</h3>
        <p>Start creating your first quiz to share with others!</p>
      </div>

      <div v-else class="quiz-grid">
        <div v-for="quiz in userQuizzes" :key="quiz.id" class="quiz-card">
          <div class="actions-container">
            <button class="action-btn edit-btn" @click="openEditModal(quiz)" title="Edit Quiz">✎</button>
            <button class="action-btn delete-btn" @click="confirmDelete(quiz)" title="Delete Quiz">×</button>
          </div>

          <div class="card-top">
            <h3>{{ quiz.title }}</h3>
            <p class="description">{{ quiz.description || 'No description provided.' }}</p>
          </div>
          <div class="card-bottom">
            <span class="badge">{{ quiz.question_count }} Questions</span>
            <button class="play-btn" @click="router.push({ name: 'play', query: { id: quiz.id } })">Play</button>
          </div>
        </div>
      </div>
    </div>

    <!-- POUŽITIE UNIVERZÁLNEHO MODALU PRE EDITÁCIU -->
    <QuizCreatorModal
        v-if="showEditModal"
        :editData="quizToEdit"
        @close="showEditModal = false"
        @saved="handleRefresh"
    />

    <!-- Jednoduchý Delete Modal -->
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
/* Pôvodné štýly zostávajú, len farby idú cez premenné */
.profile-container { min-height: 100vh; background-color: var(--color-background-soft); color: var(--color-text); font-family: 'Inter', sans-serif; }
.profile-header { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); padding: 3rem 2rem 5rem; color: white; display: flex; justify-content: center; }
.header-wrapper { max-width: 1000px; width: 100%; }
.user-welcome { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem; }
.welcome-text h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: white; }
.welcome-text p { color: rgba(255, 255, 255, 0.8); }
.avatar { width: 60px; height: 60px; background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 600; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.stat-card { background-color: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; backdrop-filter: blur(5px); }
.stat-value { font-size: 1.25rem; font-weight: 700; color: white; }
.stat-label { font-size: 0.8rem; color: rgba(255, 255, 255, 0.7); }

.content-container { max-width: 1000px; margin: 2rem auto; padding: 0 2rem; }
.section-header h2 { font-size: 1.5rem; color: var(--color-text); font-weight: 600; }
.section-header p { color: var(--color-text); opacity: 0.7; }

.quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
.quiz-card { position: relative; background: var(--card-bg); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.quiz-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

.description {
  font-size: 0.9rem; color: var(--color-text); opacity: 0.7; line-height: 1.4;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; min-height: 2.8em;
}

.actions-container { position: absolute; top: 10px; right: 10px; display: flex; gap: 5px; }
.action-btn { background: transparent; border: none; cursor: pointer; color: var(--color-text); opacity: 0.4; font-size: 1.2rem; transition: opacity 0.2s; }
.action-btn:hover { opacity: 1; }
.delete-btn:hover { color: #ef4444; }
.edit-btn:hover { color: #3b82f6; }

.card-top h3 { color: var(--color-text); margin-bottom: 0.5rem; padding-right: 50px; }
.card-bottom { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; }
.badge { background-color: var(--color-background-soft); color: var(--color-text); font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; }
.play-btn { background: linear-gradient(135deg, #8b5cf6, #3b82f6); color: white; border: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }

.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.delete-card { background: var(--card-bg); color: var(--color-text); padding: 2rem; border-radius: 12px; width: 90%; max-width: 400px; text-align: center; }
.modal-actions { display: flex; gap: 10px; margin-top: 1.5rem; }
.btn-cancel { flex: 1; padding: 10px; background: none; border: 1px solid var(--color-border); color: var(--color-text); border-radius: 6px; cursor: pointer; }
.btn-delete { flex: 1; padding: 10px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }

@media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
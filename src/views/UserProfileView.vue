<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const user = ref({
  id: null,
  username: 'Guest',
  initial: '?',
  stats: {
    created: 0,
    plays: 0,
    questions: 0
  }
})

// State for quizzes list
const userQuizzes = ref([])
const isLoading = ref(true)

// State for Delete Modal
const showDeleteModal = ref(false)
const quizToDelete = ref(null)

const fetchUserQuizzes = async (userId) => {
  // ... existing fetch logic ...
  try {
    const response = await fetch(`http://localhost:8000/backend/get_user_quizzes.php?user_id=${userId}`)
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

// --- DELETE LOGIC ---

// Open modal and set the quiz target
const confirmDelete = (quiz) => {
  quizToDelete.value = quiz
  showDeleteModal.value = true
}

// Cancel deletion
const cancelDelete = () => {
  quizToDelete.value = null
  showDeleteModal.value = false
}

// Perform deletion
const handleDelete = async () => {
  if (!quizToDelete.value) return

  try {
    const response = await fetch('http://localhost:8000/backend/delete_quiz.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        quiz_id: quizToDelete.value.id,
        user_id: user.value.id
      })
    })

    const result = await response.json()

    if (result.success) {
      // Remove from local list
      userQuizzes.value = userQuizzes.value.filter(q => q.id !== quizToDelete.value.id)
      // Update stats
      user.value.stats.created = userQuizzes.value.length
      user.value.stats.questions -= parseInt(quizToDelete.value.question_count || 0)
      
      showDeleteModal.value = false
      quizToDelete.value = null
    } else {
      alert("Error deleting quiz: " + result.message)
    }
  } catch (error) {
    console.error("Delete failed:", error)
    alert("Failed to delete quiz")
  }
}

onMounted(() => {
  // ... existing onMounted logic ...
  const userStr = localStorage.getItem('user')
  if (userStr) {
    const userData = JSON.parse(userStr)
    user.value.id = userData.id
    user.value.username = userData.username
    user.value.initial = userData.username.charAt(0).toUpperCase()
    fetchUserQuizzes(userData.id)
  } else {
    router.push('/login')
  }
})
</script>

<template>
  <div class="profile-container">
    <!-- ... Header Section (unchanged) ... -->
    <div class="profile-header">
      <div class="header-wrapper">
        <div class="user-welcome">
          <div class="welcome-text">
            <h1>Welcome back, {{ user.username }}!</h1>
            <p>Manage your quizzes and track your progress</p>
          </div>
          <div class="avatar">
            {{ user.initial }}
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">🏆</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.created }}</span>
              <span class="stat-label">Quizzes Created</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.plays }}</span>
              <span class="stat-label">Total Plays</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">@</div>
            <div class="stat-info">
              <span class="stat-value">{{ user.stats.questions }}</span>
              <span class="stat-label">Questions Written</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Section -->
    <div class="content-container">
      <!-- ... Section Header (unchanged) ... -->
      <div class="section-header">
        <h2>Your Quizzes</h2>
        <p>Create, edit, and manage your quiz collection</p>
      </div>

      <!-- Loading & Empty States (unchanged) -->
      <div v-if="isLoading" class="loading-state">
        <p>Loading your quizzes...</p>
      </div>

      <div v-else-if="userQuizzes.length === 0" class="empty-state">
        <div class="trophy-icon">🏆</div>
        <h3>No quizzes yet</h3>
        <p>Start creating your first quiz to share with others!</p>
      </div>

      <!-- Quiz List Grid -->
      <div v-else class="quiz-grid">
        <div v-for="quiz in userQuizzes" :key="quiz.id" class="quiz-card">
          
          <!-- Delete Button (Absolute positioned) -->
          <button class="delete-btn" @click="confirmDelete(quiz)" title="Delete Quiz">
            ×
          </button>

          <div class="card-top">
            <h3>{{ quiz.title }}</h3>
            <p class="description">{{ quiz.description || 'No description provided.' }}</p>
          </div>
          <div class="card-bottom">
            <span class="badge">{{ quiz.question_count }} Questions</span>
            <button class="play-btn">Play</button>
          </div>
        </div>
      </div>

    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="cancelDelete">
      <div class="delete-card">
        <div class="warning-icon">⚠️</div>
        <h3>Delete Quiz?</h3>
        <p>Are you sure you want to delete "<strong>{{ quizToDelete?.title }}</strong>"? This action cannot be undone.</p>
        
        <div class="modal-actions">
          <button class="btn-cancel" @click="cancelDelete">Cancel</button>
          <button class="btn-delete" @click="handleDelete">Yes, Delete</button>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* ... existing styles ... */
.profile-container {
  min-height: 100vh;
  background-color: #f9fafb;
  font-family: 'Inter', sans-serif;
}

.profile-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
  padding: 3rem 2rem 5rem;
  color: white;
  display: flex;
  justify-content: center;
}

.header-wrapper {
  max-width: 1000px;
  width: 100%;
}

.user-welcome {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2.5rem;
}

.welcome-text h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.welcome-text p {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
}

.avatar {
  width: 60px;
  height: 60px;
  background-color: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 600;
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.stat-card {
  background-color: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  backdrop-filter: blur(5px);
}

.stat-icon {
  width: 40px;
  height: 40px;
  background-color: rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
}

.content-container {
  max-width: 1000px;
  margin: 2rem auto;
  padding: 0 2rem;
}

.section-header {
  margin-bottom: 1.5rem;
}

.section-header h2 {
  font-size: 1.5rem;
  color: #1f2937;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.section-header p {
  color: #6b7280;
  font-size: 0.95rem;
}

.empty-state, .loading-state {
  background: white;
  border-radius: 16px;
  padding: 4rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.loading-state {
  color: #6b7280;
}

.trophy-icon {
  width: 64px;
  height: 64px;
  background-color: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #9ca3af;
  margin-bottom: 1.5rem;
}

.empty-state h3 {
  color: #374151;
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  font-size: 0.95rem;
}

/* Quiz Grid Styles */
.quiz-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.quiz-card {
  position: relative; /* Needed for delete button positioning */
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 180px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.quiz-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Delete Button Styles */
.delete-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  color: #9ca3af;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.delete-btn:hover {
  color: #ef4444;
  background-color: #fee2e2;
}

.card-top h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
  padding-right: 20px; /* Prevent text from overlapping delete btn */
}

.description {
  font-size: 0.9rem;
  color: #6b7280;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1.5rem;
}

.badge {
  background-color: #f3f4f6;
  color: #4b5563;
  font-size: 0.75rem;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.play-btn {
  background: linear-gradient(135deg, #8b5cf6, #3b82f6);
  color: white;
  border: none;
  padding: 6px 16px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
}

.play-btn:hover {
  opacity: 0.9;
}

/* Delete Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(3px);
}

.delete-card {
  background: white;
  width: 90%;
  max-width: 400px;
  padding: 2rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  animation: popIn 0.2s ease-out;
}

@keyframes popIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.warning-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.delete-card h3 {
  font-size: 1.25rem;
  color: #1f2937;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.delete-card p {
  color: #6b7280;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  border: 1px solid #d1d5db;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-delete {
  padding: 0.75rem 1.5rem;
  border: none;
  background: #ef4444;
  color: white;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-delete:hover {
  opacity: 0.9;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .user-welcome {
    flex-direction: column-reverse;
    gap: 1rem;
  }
}
</style>

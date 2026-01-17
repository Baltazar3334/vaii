<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Herné stavy
const quiz = ref(null)
const questions = ref([])
const currentQuestionIndex = ref(0)
const selectedOption = ref(null)
const score = ref(0)
const isFinished = ref(false)
const isLoading = ref(true)

// Časomiera
const timer = ref(0)
const timerInterval = ref(null)

const currentQuestion = computed(() => questions.value[currentQuestionIndex.value])
const progress = computed(() => ((currentQuestionIndex.value) / questions.value.length) * 100)

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const startTimer = () => {
  timerInterval.value = setInterval(() => {
    timer.value++
  }, 1000)
}

const loadQuiz = async () => {
  const quizId = route.query.id
  if (!quizId) {
    router.push('/')
    return
  }

  try {
    // ZMENA: Voláme api.php s parametrom action a id
    const response = await fetch(`http://localhost:8000/backend/api.php?action=get_quiz_details&id=${quizId}`)
    const result = await response.json()

    if (result.success && result.quiz) {
      quiz.value = result.quiz
      questions.value = result.questions || []

      if (questions.value.length > 0) {
        startTimer()
      } else {
        alert("This quiz has no questions.")
        router.push('/')
      }
    } else {
      alert(result.message || "Quiz not found!")
      router.push('/')
    }
  } catch (e) {
    console.error("Error loading quiz:", e)
    alert("Failed to connect to the server.")
  } finally {
    isLoading.value = false
  }
}

const handleAnswer = (index) => {
  if (selectedOption.value !== null) return // Zabrániť viacnásobnému kliknutiu

  selectedOption.value = index

  // Kontrola správnosti (correct_option_index z DB)
  if (index === parseInt(currentQuestion.value.correct_option_index)) {
    score.value++
  }

  // Krátka pauza, aby hráč videl, čo klikol
  setTimeout(() => {
    if (currentQuestionIndex.value < questions.value.length - 1) {
      currentQuestionIndex.value++
      selectedOption.value = null
    } else {
      finishQuiz()
    }
  }, 600)
}

const finishQuiz = () => {
  clearInterval(timerInterval.value)
  isFinished.value = true
}

onMounted(loadQuiz)
onUnmounted(() => clearInterval(timerInterval.value))
</script>

<template>
  <div class="player-container">
    <div v-if="isLoading" class="status-box">Loading quiz...</div>

    <div v-else-if="!isFinished && quiz" class="quiz-active">
      <div class="game-header">
        <div class="quiz-info">
          <h1>{{ quiz.title }}</h1>
          <div class="stats-row">
            <span>Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}</span>
            <span class="timer">⏱ {{ formatTime(timer) }}</span>
          </div>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: progress + '%' }"></div>
        </div>
      </div>

      <div class="question-card" v-if="currentQuestion">
        <h2 class="question-text">{{ currentQuestion.question_text }}</h2>

        <div class="options-list">
          <button
              v-for="(opt, idx) in currentQuestion.options"
              :key="idx"
              class="option-btn"
              :class="{
              'selected': selectedOption === idx,
              'correct': selectedOption !== null && idx === parseInt(currentQuestion.correct_option_index),
              'wrong': selectedOption === idx && idx !== parseInt(currentQuestion.correct_option_index)
            }"
              @click="handleAnswer(idx)"
              :disabled="selectedOption !== null"
          >
            {{ opt }}
          </button>
        </div>
      </div>
    </div>

    <!-- RESULT SCREEN -->
    <div v-else-if="isFinished" class="result-card">
      <div class="trophy">🎊</div>
      <h1>Quiz Complete!</h1>
      <p class="score-text">You scored <strong>{{ score }}</strong> out of <strong>{{ questions.length }}</strong></p>

      <div class="final-stats">
        <div class="stat">
          <span class="label">Time taken:</span>
          <span class="value">{{ formatTime(timer) }}</span>
        </div>
        <div class="stat">
          <span class="label">Accuracy:</span>
          <span class="value">{{ Math.round((score/questions.length) * 100) }}%</span>
        </div>
      </div>

      <button class="home-btn" @click="router.push('/')">Back to Home</button>
    </div>
  </div>
</template>

<style scoped>
.player-container {
  min-height: calc(100vh - 70px);
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  font-family: 'Inter', sans-serif;
}

.quiz-active {
  width: 100%;
  max-width: 700px;
}

.game-header {
  background: white;
  padding: 1.5rem;
  border-radius: 12px 12px 0 0;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
}

.game-header h1 { font-size: 1.25rem; color: #1f2937; margin-bottom: 0.5rem; }

.stats-row {
  display: flex;
  justify-content: space-between;
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.timer { color: #8b5cf6; }

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  transition: width 0.3s ease;
}

.question-card {
  background: white;
  padding: 2.5rem;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
}

.question-text {
  font-size: 1.5rem;
  color: #111827;
  margin-bottom: 2rem;
  text-align: center;
}

.options-list {
  display: grid;
  gap: 1rem;
}

.option-btn {
  padding: 1rem 1.5rem;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s;
  font-weight: 500;
}

.option-btn:hover:not(:disabled) {
  border-color: #8b5cf6;
  background: #f5f3ff;
}

.option-btn.selected { border-color: #8b5cf6; background: #f5f3ff; }
.option-btn.correct { background: #d1fae5; border-color: #10b981; color: #065f46; }
.option-btn.wrong { background: #fee2e2; border-color: #ef4444; color: #991b1b; }

/* Result Card */
.result-card {
  background: white;
  padding: 3rem;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
  max-width: 450px;
  width: 100%;
}

.trophy { font-size: 4rem; margin-bottom: 1rem; }
.score-text { font-size: 1.2rem; color: #4b5563; margin-bottom: 2rem; }

.final-stats {
  background: #f3f4f6;
  padding: 1.5rem;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.stat {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.stat .label { color: #6b7280; }
.stat .value { font-weight: 700; color: #1f2937; }

.home-btn {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}
</style>
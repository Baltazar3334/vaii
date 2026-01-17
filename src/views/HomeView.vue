<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const refreshSignal = inject('refreshSignal')
const searchQuery = ref('')
const quizzes = ref([])
const isLoading = ref(true)

const fetchQuizzes = async () => {
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=get_all_quizzes')
    const result = await response.json()
    if (result.success) quizzes.value = result.quizzes
  } catch (e) {
    console.error(e)
  } finally {
    isLoading.value = false
  }
}

const filteredQuizzes = computed(() => {
  if (!searchQuery.value) return quizzes.value
  const term = searchQuery.value.toLowerCase()
  return quizzes.value.filter(quiz =>
      quiz.title.toLowerCase().includes(term) ||
      quiz.description.toLowerCase().includes(term)
  )
})

onMounted(fetchQuizzes)
watch(refreshSignal, fetchQuizzes)
</script>

<template>
  <div class="home-container">
    <div class="hero-section">
      <div class="hero-content">
        <h1><span class="icon">⚡</span> Popular Quizzes</h1>
        <p class="subtitle">Discover the most loved quizzes in the community</p>
        <div class="search-wrapper">
          <input type="text" v-model="searchQuery" placeholder="Search quizzes..." class="search-input" />
        </div>
      </div>
    </div>

    <div class="content-section">
      <div v-if="isLoading" class="status-msg">Loading quizzes...</div>

      <div v-else class="quiz-grid">
        <div v-for="quiz in filteredQuizzes" :key="quiz.id" class="quiz-card">
          <div class="card-image" v-if="quiz.image_url">
            <img :src="quiz.image_url" alt="Quiz cover" />
          </div>
          <div class="card-body">
            <h3>{{ quiz.title }}</h3>
            <p class="description">{{ quiz.description }}</p>
            <div class="meta-info">
              <span>{{ quiz.questions }} questions</span>
              <span class="separator">•</span>
              <span>by {{ quiz.author }}</span>
            </div>
          </div>
          <button class="play-btn" @click="router.push({ name: 'play', query: { id: quiz.id } })">
            ▷ Play Quiz
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home-container { display: flex; flex-direction: column; min-height: 100vh; background-color: var(--color-background-soft); }
.hero-section { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); padding: 4rem 2rem; color: white; display: flex; justify-content: center; }
.hero-content { max-width: 1000px; width: 100%; }
h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px; color: white; }
.subtitle { color: rgba(255, 255, 255, 0.8); font-size: 1rem; margin-bottom: 2rem; }

.card-image { width: 100%; height: 150px; overflow: hidden; border-bottom: 1px solid var(--color-border); }
.card-image img { width: 100%; height: 100%; object-fit: cover; }

.search-input {
  width: 100%; padding: 12px 20px; border-radius: 8px; border: none;
  background-color: rgba(255, 255, 255, 0.2); color: white; font-size: 1rem;
  outline: none; transition: background-color 0.3s;
}
.search-input::placeholder { color: rgba(255, 255, 255, 0.6); }
.search-input:focus { background-color: rgba(255, 255, 255, 0.3); box-shadow: 0 0 0 2px rgba(255,255,255,0.2); }

.content-section { padding: 3rem 2rem; display: flex; justify-content: center; }
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; max-width: 1200px; width: 100%; }

.quiz-card {
  background: var(--card-bg); border-radius: 12px; padding: 0; /* Zmenené z 1.5rem na 0 kvôli obrázku */
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column;
  justify-content: space-between; gap: 0; transition: transform 0.2s, box-shadow 0.2s;
  border: 1px solid var(--color-border); overflow: hidden;
}
.quiz-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }

.card-body { padding: 1.5rem; overflow: hidden; display: flex; flex-direction: column; }

.card-body h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--color-text);
  margin: 0 0 0.5rem 0;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-all;
}
.description {
  color: var(--color-text);
  opacity: 0.7;
  font-size: 0.9rem;
  margin-bottom: 1rem;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  min-height: 2.8em;
}
.meta-info { font-size: 0.8rem; color: var(--color-text); opacity: 0.5; display: flex; flex-wrap: wrap; gap: 5px; }
.separator { color: var(--color-border); }

.play-btn {
  background: linear-gradient(90deg, #8b5cf6, #3b82f6); color: white; border: none;
  padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;
}
.play-btn:hover { opacity: 0.9; }
.status-msg { color: var(--color-text); opacity: 0.6; }
</style>
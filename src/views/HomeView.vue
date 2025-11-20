<script setup>

import { ref, computed } from 'vue'

const searchQuery = ref('')

const quizzes = ref([
  { id: 1, title: 'Movie Trivia Extravaganza', description: 'How well do you know your favorite films?', questions: 3, plays: 2134, author: 'FilmBuff' },
  { id: 2, title: 'Math Quick Fire', description: 'Fast-paced math problems to test your mental arithmetic', questions: 5, plays: 1823, author: 'MathWhiz' },
  { id: 3, title: 'World Geography Challenge', description: 'Test your knowledge of countries, capitals, and landmarks', questions: 10, plays: 1542, author: 'GeoMaster' },
  { id: 4, title: 'Animal Kingdom Quiz', description: 'Discover fascinating facts about animals from around the world', questions: 8, plays: 1205, author: 'WildlifeFan' },
  { id: 5, title: 'Science Fundamentals', description: 'Basic science questions covering physics, chemistry, and biology', questions: 15, plays: 987, author: 'ScienceNerd' },
  { id: 6, title: 'History Through the Ages', description: 'Journey through important historical events and figures', questions: 12, plays: 756, author: 'HistoryPro' }
])


const filteredQuizzes = computed(() => {

  if (!searchQuery.value) {
    return quizzes.value
  }


  const term = searchQuery.value.toLowerCase()
  return quizzes.value.filter(quiz =>
      quiz.title.toLowerCase().includes(term) ||
      quiz.description.toLowerCase().includes(term)
  )
})
</script>

<template>
  <div class="home-container">

    <div class="hero-section">
      <div class="hero-content">
        <h1><span class="icon">⚡</span> Popular Quizzes</h1>
        <p class="subtitle">Discover the most loved quizzes in the community</p>

        <div class="search-wrapper">
          <input
              type="text"
              v-model="searchQuery"
              placeholder="Search quizzes..."
              class="search-input"
          />
        </div>
      </div>
    </div>

    <div class="content-section">
      <div class="quiz-grid">

        <div v-for="quiz in filteredQuizzes" :key="quiz.id" class="quiz-card">
          <div class="card-body">
            <h3>{{ quiz.title }}</h3>
            <p class="description">{{ quiz.description }}</p>

            <div class="meta-info">
              <span>{{ quiz.questions }} questions</span>
              <span class="separator">•</span>
              <span>👤 {{ quiz.plays }} plays</span>
              <span class="separator">•</span>
              <span>by {{ quiz.author }}</span>
            </div>
          </div>

          <button class="play-btn">
            ▷ Play Quiz
          </button>
        </div>


        <div v-if="filteredQuizzes.length === 0" style="grid-column: 1/-1; text-align: center; color: #6b7280;">
          No quizzes found matching "{{ searchQuery }}"
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>

.home-container { display: flex; flex-direction: column; min-height: 100vh; background-color: #e5e7eb; }
.hero-section { background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%); padding: 4rem 2rem; color: white; display: flex; justify-content: center; }
.hero-content { max-width: 1000px; width: 100%; }
h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 10px; }
.subtitle { color: rgba(255, 255, 255, 0.8); font-size: 1rem; margin-bottom: 2rem; }
.search-wrapper { max-width: 600px; }
.search-input { width: 100%; padding: 12px 20px; border-radius: 8px; border: none; background-color: rgba(255, 255, 255, 0.2); color: white; font-size: 1rem; outline: none; transition: background-color 0.3s; }
.search-input::placeholder { color: rgba(255, 255, 255, 0.6); }
.search-input:focus { background-color: rgba(255, 255, 255, 0.3); }
.content-section { padding: 3rem 2rem; display: flex; justify-content: center; }
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; max-width: 1200px; width: 100%; }
.quiz-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; justify-content: space-between; gap: 1.5rem; transition: transform 0.2s; }
.quiz-card:hover { transform: translateY(-5px); }
.card-body h3 { font-size: 1.1rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; }
.description { color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem; line-height: 1.4; }
.meta-info { font-size: 0.8rem; color: #9ca3af; display: flex; flex-wrap: wrap; gap: 5px; }
.separator { color: #d1d5db; }
.play-btn { background: linear-gradient(90deg, #8b5cf6, #3b82f6); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; width: 100%; text-align: center; }
.play-btn:hover { opacity: 0.9; }
</style>
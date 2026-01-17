<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const leaderboard = ref([])
const isLoading = ref(true)
const sortBy = ref('total_plays_received')

const fetchStats = async () => {
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=get_leaderboard')
    const result = await response.json()
    if (result.success) {
      leaderboard.value = result.leaderboard.map(user => ({
        ...user,
        avg_questions: user.quizzes_created > 0 ? (user.total_questions / user.quizzes_created).toFixed(1) : 0,
        popularity: user.quizzes_created > 0 ? Math.round(user.total_plays_received / user.quizzes_created) : 0
      }))
    }
  } catch (e) { console.error(e) }
  finally { isLoading.value = false }
}

const sortedLeaderboard = computed(() => {
  return [...leaderboard.value].sort((a, b) => b[sortBy.value] - a[sortBy.value])
})

const maxPlays = computed(() => Math.max(...leaderboard.value.map(u => u.total_plays_received), 1))

onMounted(fetchStats)
</script>

<template>
  <div class="stats-container">
    <div class="stats-card">
      <div class="header-section">
        <span class="badge">Live Stats</span>
        <h1>Community Leaderboard</h1>
        <p>Top creators making the most impact</p>
      </div>

      <div class="sort-bar">
        <div v-for="opt in [
          {id: 'total_plays_received', label: '🔥 Popularity', hint: 'Total times other users played your quizzes.'},
          {id: 'quizzes_created', label: '📚 Content', hint: 'Number of unique quizzes you have created.'},
          {id: 'total_questions', label: '✍️ Effort', hint: 'Total number of questions written across all your quizzes.'}
        ]" :key="opt.id" class="sort-item">
          <button 
            :class="{ active: sortBy === opt.id }" 
            @click="sortBy = opt.id"
          >
            {{ opt.label }}
          </button>
          <div class="tooltip-trigger">
            ?
            <span class="tooltip-text">{{ opt.hint }}</span>
          </div>
        </div>
      </div>

      <div v-if="isLoading" class="loader">
        <div class="spinner"></div>
        <p>Calculating rankings...</p>
      </div>

      <div v-else class="ranking-list">
        <div v-for="(user, index) in sortedLeaderboard" :key="user.username" class="rank-item">
          <div class="rank-number" :class="'pos-' + (index + 1)">
            <template v-if="index === 0">🥇</template>
            <template v-else-if="index === 1">🥈</template>
            <template v-else-if="index === 2">🥉</template>
            <template v-else>{{ index + 1 }}</template>
          </div>

          <!-- PRIDANÝ MINI AVATAR -->
          <div class="mini-avatar">
            <img v-if="user.avatar_url" :src="user.avatar_url" alt="" @error="user.avatar_url = null" />
            <span v-else>{{ user.username.charAt(0).toUpperCase() }}</span>
          </div>
          
          <div class="user-main">
            <div class="user-info">
              <!-- ZMENA: Pridaná trieda 'name-link' a router.push -->
              <span class="name name-link" @click="router.push('/profile/' + user.user_id)">
                {{ user.username }}
              </span>
              <span class="avg">Avg. {{ user.avg_questions }} questions / quiz</span>
            </div>

            <div class="progres-container">
              <div class="bar-bg">
                <div class="bar-fill" :style="{ width: (user.total_plays_received / maxPlays * 100) + '%' }"></div>
              </div>
              <span class="value">{{ user.total_plays_received }} plays</span>
            </div>
          </div>

          <div class="stats-grid">
            <div class="mini-stat">
              <span class="count">{{ user.quizzes_created }}</span>
              <span class="label">Quizzes</span>
            </div>
            <div class="mini-stat">
              <span class="count">{{ user.total_questions }}</span>
              <span class="label">Questions</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stats-container { padding: 4rem 1rem; display: flex; justify-content: center; min-height: 90vh; background-color: var(--color-background-soft); }
.stats-card { background: var(--card-bg); border-radius: 24px; width: 100%; max-width: 850px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); border: 1px solid var(--color-border); padding: 3rem; }

.header-section { text-align: center; margin-bottom: 3rem; }
.badge { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
h1 { font-size: 2.5rem; color: var(--color-text); margin: 1rem 0 0.5rem; }
p { color: var(--color-text); opacity: 0.6; }

.sort-bar { display: flex; justify-content: center; gap: 1.5rem; margin-bottom: 3rem; align-items: center; }
.sort-item { display: flex; align-items: center; gap: 0.5rem; position: relative; }

.sort-bar button { border: none; background: var(--color-background-soft); color: var(--color-text); padding: 0.6rem 1.2rem; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; font-size: 0.9rem; border: 1px solid var(--color-border); }
.sort-bar button.active { background: #8b5cf6; color: white; border-color: #8b5cf6; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3); }

/* TOOLTIP STYLES */
.tooltip-trigger {
  width: 18px;
  height: 18px;
  background: var(--color-border);
  color: var(--color-text);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: bold;
  cursor: help;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.tooltip-trigger:hover { opacity: 1; background: #8b5cf6; color: white; }

.tooltip-text {
  visibility: hidden;
  width: 160px;
  background-color: #1f2937;
  color: #fff;
  text-align: center;
  border-radius: 6px;
  padding: 8px;
  position: absolute;
  z-index: 10;
  bottom: 125%;
  left: 50%;
  margin-left: -80px;
  opacity: 0;
  transition: opacity 0.3s;
  font-size: 0.75rem;
  font-weight: normal;
  line-height: 1.2;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Šípka tooltipu */
.tooltip-text::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: #1f2937 transparent transparent transparent;
}

.tooltip-trigger:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

.rank-item { 
  display: grid; 
  grid-template-columns: 60px 50px 1fr 180px; /* Zmenené z 3 na 4 stĺpce */
  align-items: center; 
  gap: 1.5rem; 
  padding: 1.5rem; 
  border-radius: 16px; 
  margin-bottom: 1rem; 
  background: var(--color-background-soft); 
  transition: transform 0.2s; 
}
.rank-item:hover { transform: scale(1.01); background: var(--card-bg); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

.rank-number { font-size: 1.5rem; font-weight: 800; display: flex; justify-content: center; color: var(--color-text); opacity: 0.4; }
.rank-number.pos-1, .rank-number.pos-2, .rank-number.pos-3 { opacity: 1; font-size: 2rem; }

.user-main { display: flex; flex-direction: column; gap: 0.75rem; }
.name { font-size: 1.1rem; font-weight: 700; color: var(--color-text); }

.name-link {
  cursor: pointer;
  transition: color 0.2s;
}

.name-link:hover {
  color: #8b5cf6;
  text-decoration: underline;
}

.avg { font-size: 0.8rem; opacity: 0.5; color: var(--color-text); }

.progres-container { display: flex; align-items: center; gap: 1rem; }
.bar-bg { flex: 1; height: 8px; background: rgba(0,0,0,0.05); border-radius: 4px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(90deg, #8b5cf6, #3b82f6); border-radius: 4px; }
.value { font-size: 0.85rem; font-weight: 700; color: #10b981; white-space: nowrap; }

.stats-grid { display: flex; gap: 1rem; justify-content: flex-end; }
.mini-stat { text-align: center; background: var(--card-bg); padding: 0.5rem 1rem; border-radius: 10px; min-width: 80px; border: 1px solid var(--color-border); }
.count { display: block; font-weight: 800; color: var(--color-text); }
.label { font-size: 0.7rem; text-transform: uppercase; opacity: 0.5; }

.loader { text-align: center; padding: 3rem; }
.spinner { width: 40px; height: 40px; border: 4px solid rgba(139, 92, 246, 0.1); border-top-color: #8b5cf6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.mini-avatar {
  width: 40px;
  height: 40px;
  background: #8b5cf6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  overflow: hidden;
  border: 2px solid var(--color-border);
}

.mini-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

@media (max-width: 768px) {
  .stats-card { padding: 1.5rem; border-radius: 0; border: none; }
  h1 { font-size: 1.8rem; }
  
  .sort-bar { flex-direction: column; width: 100%; gap: 0.5rem; }
  .sort-item { width: 100%; justify-content: space-between; background: var(--color-background-soft); padding: 0.5rem; border-radius: 10px; }
  .sort-item button { flex: 1; border: none; background: transparent; text-align: left; }
  .sort-item button.active { background: #8b5cf6; box-shadow: none; }

  .rank-item { grid-template-columns: 40px 40px 1fr; }
  .stats-grid { display: none; }

  .mini-stat { 
    flex: 1;
    background: transparent;
    border: none;
    padding: 0;
  }

  .rank-number { font-size: 1.2rem; }
  .rank-number.pos-1, .rank-number.pos-2, .rank-number.pos-3 { font-size: 1.5rem; }
  
  .tooltip-text {
    left: auto;
    right: 0;
    margin-left: 0;
    width: 200px;
  }
  .tooltip-text::after { left: 90%; }
}

@media (max-width: 480px) {
  .stats-container { padding: 0; }
  .rank-item { border-radius: 0; margin-bottom: 1px; }
}
</style>
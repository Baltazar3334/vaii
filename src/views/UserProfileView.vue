<script setup>
import { ref } from 'vue'

const user = ref({
  username: 'ghfdg',
  initial: 'G',
  stats: {
    created: 0,
    plays: 0,
    questions: 0
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
      <div class="section-header">
        <h2>Your Quizzes</h2>
        <p>Create, edit, and manage your quiz collection</p>
      </div>

      <div class="empty-state">
        <div class="trophy-icon">🏆</div>
        <h3>No quizzes yet</h3>
        <p>Start creating your first quiz to share with others!</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-container {
  min-height: 100vh;
  background-color: #f9fafb;
  font-family: 'Inter', sans-serif;
}

.profile-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%);
  padding: 3rem 2rem 5rem; /* Extra padding at bottom for visual balance */
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

.empty-state {
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
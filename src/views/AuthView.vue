<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router' // Import router for redirection

const router = useRouter() // Initialize router
const isLogin = ref(false)

const username = ref('')
const email = ref('')
const password = ref('')
const errorMessage = ref('') // State for error messages

const handleSubmit = async () => {
  errorMessage.value = '' // Reset error

  if (isLogin.value) {
    // --- LOGIN LOGIC ---
    try {
      const response = await fetch('http://localhost:8000/backend/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: email.value,
          password: password.value
        })
      })

      const result = await response.json()

      if (result.success) {
        console.log('Login successful', result.user)
        // Save user info (e.g., to localStorage)
        localStorage.setItem('user', JSON.stringify(result.user))
        // Redirect to home or profile
        router.push('/profile')
      } else {
        errorMessage.value = result.message
      }
    } catch (error) {
      console.error('Error:', error)
      errorMessage.value = 'Server connection failed'
    }

  } else {
    // --- REGISTRATION LOGIC WOULD GO HERE ---
    console.log('Registrujem:', { username: username.value, email: email.value, password: password.value })
  }
}
</script>

<template>
  <div class="auth-container">
    <div class="header">
      <div class="logo-icon"></div>
      <h1>QuizMaker</h1>
      <p>Create, share, and play amazing quizzes</p>
    </div>

    <div class="auth-card">
      <div class="tabs">
        <button
            :class="{ active: !isLogin }"
            @click="isLogin = false"
        >Sign Up</button>
        <button
            :class="{ active: isLogin }"
            @click="isLogin = true"
        >Login</button>
        <div class="slider" :class="{ 'move-right': isLogin }"></div>
      </div>

      <form @submit.prevent="handleSubmit">
            
            <!-- Add Error Message Display -->
            <div v-if="errorMessage" style="color: red; margin-bottom: 1rem; font-size: 0.9rem;">
              {{ errorMessage }}
            </div>

            <div class="input-group" v-if="!isLogin">
          <label>Username</label>
          <input type="text" v-model="username" placeholder="Enter your username" required />
        </div>

        <div class="input-group">
          <label>Email</label>
          <input type="email" v-model="email" placeholder="Enter your email" required />
        </div>

        <div class="input-group">
          <label>Password</label>
          <input type="password" v-model="password" placeholder="Enter your password" required />
        </div>

        <button type="submit" class="submit-btn">
          {{ isLogin ? 'Login' : 'Create Account' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.auth-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
  font-family: 'Inter', sans-serif;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #8b5cf6, #3b82f6);
  border-radius: 12px;
  margin: 0 auto 1rem;
  box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
}

h1 {
  font-size: 1.8rem;
  color: #1f2937;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

p {
  color: #6b7280;
  font-size: 0.95rem;
}

.auth-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  padding: 2rem;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.5);
}

.tabs {
  display: flex;
  position: relative;
  background: #f3f4f6;
  border-radius: 10px;
  padding: 4px;
  margin-bottom: 2rem;
}

.tabs button {
  flex: 1;
  border: none;
  background: transparent;
  padding: 10px;
  z-index: 2;
  cursor: pointer;
  font-weight: 500;
  color: #6b7280;
  transition: color 0.3s;
}

.tabs button.active {
  color: #1f2937;
}

.slider {
  position: absolute;
  top: 4px;
  left: 4px;
  width: calc(50% - 4px);
  height: calc(100% - 8px);
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
  z-index: 1;
}

.slider.move-right {
  transform: translateX(100%);
}

.input-group {
  margin-bottom: 1.5rem;
  text-align: left;
}

label {
  display: block;
  font-size: 0.85rem;
  color: #4b5563;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
  font-size: 0.95rem;
  outline: none;
  transition: all 0.2s;
}

input:focus {
  border-color: #8b5cf6;
  background: white;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.submit-btn {
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  color: white;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  margin-top: 0.5rem;
}

.submit-btn:hover {
  opacity: 0.9;
}
</style>
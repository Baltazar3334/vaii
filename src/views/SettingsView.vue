<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isDarkMode = ref(localStorage.getItem('theme') === 'dark')
const showConfirmReset = ref(false)
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  const theme = isDarkMode.value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-theme', theme)
}

const handleResetAccount = async () => {
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=reset_account', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: user.value.id })
    })
    const result = await response.json()
    if (result.success) {
      alert("All your quizzes have been deleted.")
      showConfirmReset.value = false
      router.push('/profile')
    }
  } catch (e) {
    alert("Reset failed")
  }
}

onMounted(() => {
  if (!user.value.id) router.push('/login')
})
</script>

<template>
  <div class="settings-container">
    <div class="settings-card">
      <h1>Settings</h1>

      <section class="settings-section">
        <h2>Appearance</h2>
        <div class="setting-item">
          <span>Dark Mode</span>
          <label class="switch">
            <input type="checkbox" :checked="isDarkMode" @change="toggleDarkMode">
            <span class="slider round"></span>
          </label>
        </div>
      </section>

      <section class="settings-section danger-zone">
        <h2>Danger Zone</h2>
        <div class="setting-item">
          <div class="text">
            <h3>Reset Account</h3>
            <p>Delete all your created quizzes and progress. This cannot be undone.</p>
          </div>
          <button class="reset-btn" @click="showConfirmReset = true">Reset Data</button>
        </div>
      </section>
    </div>

    <!-- Confirm Modal -->
    <div v-if="showConfirmReset" class="modal-overlay" @click.self="showConfirmReset = false">
      <div class="confirm-card">
        <h3>Are you absolutely sure?</h3>
        <p>This will permanently delete all your quizzes.</p>
        <div class="actions">
          <button class="btn-secondary" @click="showConfirmReset = false">Cancel</button>
          <button class="btn-danger" @click="handleResetAccount">Yes, Delete Everything</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.settings-container { padding: 4rem 2rem; display: flex; justify-content: center; min-height: 90vh; background-color: var(--color-background); color: var(--color-text); }
.settings-card { background: var(--card-bg); padding: 2rem; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
h1 { margin-bottom: 2rem; font-size: 2rem; }
.settings-section { margin-bottom: 2.5rem; }
.settings-section h2 { font-size: 1.2rem; color: #8b5cf6; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; }
.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; }
.danger-zone { border: 1px solid #fee2e2; padding: 1.5rem; border-radius: 12px; background: #fffafb; }
.danger-zone h2 { color: #ef4444; border-color: #fecaca; }
.reset-btn { background: #ef4444; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }

/* Toggle Switch Styles */
.switch { position: relative; display: inline-block; width: 50px; height: 28px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #8b5cf6; }
input:checked + .slider:before { transform: translateX(22px); }

/* Modal */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.confirm-card { background: white; padding: 2rem; border-radius: 12px; text-align: center; max-width: 400px; color: #1f2937;}
.actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.btn-secondary { flex: 1; padding: 0.8rem; border: 1px solid #ccc; background: white; border-radius: 8px; cursor: pointer; }
.btn-danger { flex: 1; padding: 0.8rem; border: none; background: #ef4444; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; }
</style>
<script setup>
import { ref, onMounted, inject } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const updateUser = inject('updateUser')
const isDarkMode = ref(localStorage.getItem('theme') === 'dark')
const user = ref(JSON.parse(localStorage.getItem('user') || '{}'))

// Stavy pre formuláre
const avatarUrl = ref(user.value.avatar_url || '')
const newUsername = ref(user.value.username || '')
const newPassword = ref('')
const confirmPassword = ref('')
const showConfirmReset = ref(false)

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  const theme = isDarkMode.value ? 'dark' : 'light'
  localStorage.setItem('theme', theme)
  document.documentElement.setAttribute('data-theme', theme)
}

const handleUpdateAvatar = async () => {
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=update_avatar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: user.value.id, avatar_url: avatarUrl.value }),
      credentials: 'include'
    })
    if ((await response.json()).success) {
      user.value.avatar_url = avatarUrl.value
      localStorage.setItem('user', JSON.stringify(user.value))
      if (updateUser) updateUser()
      alert("Avatar updated!")
    }
  } catch (e) { alert("Failed to update avatar") }
}

const handleUpdateUsername = async () => {
  if (!newUsername.value.trim()) return
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=update_username', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: user.value.id, new_username: newUsername.value }),
      credentials: 'include'
    })
    const result = await response.json()
    if (result.success) {
      user.value.username = newUsername.value
      localStorage.setItem('user', JSON.stringify(user.value))
      if (updateUser) updateUser()
      alert("Username updated!")
    } else { alert(result.message) }
  } catch (e) { alert("Failed to update username") }
}

const handleUpdatePassword = async () => {
  if (newPassword.value.length < 6) { alert("Password must be at least 6 characters"); return; }
  if (newPassword.value !== confirmPassword.value) { alert("Passwords do not match"); return; }

  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=update_password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: user.value.id, new_password: newPassword.value }),
      credentials: 'include'
    })
    if ((await response.json()).success) {
      newPassword.value = ''
      confirmPassword.value = ''
      alert("Password updated successfully!")
    }
  } catch (e) { alert("Failed to update password") }
}

const handleResetAccount = async () => {
  try {
    const response = await fetch('http://localhost:8000/backend/api.php?action=reset_account', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: user.value.id }),
      credentials: 'include'
    })
    if ((await response.json()).success) {
      alert("All your quizzes have been deleted.")
      showConfirmReset.value = false
      router.push('/profile')
    }
  } catch (e) { alert("Reset failed") }
}

onMounted(() => {
  if (!user.value.id) router.push('/login')
})
</script>

<template>
  <div class="settings-container">
    <div class="settings-card">
      <h1>Settings</h1>

      <!-- ACCOUNT SECTION -->
      <section class="settings-section">
        <h2>Account Details</h2>
        <div class="setting-item vertical">
          <label>Username</label>
          <div class="input-row">
            <input type="text" v-model="newUsername" />
            <button class="save-btn" @click="handleUpdateUsername">Change Name</button>
          </div>
        </div>

        <div class="setting-item vertical">
          <label>Profile Picture (URL)</label>
          <div class="input-row">
            <input type="text" v-model="avatarUrl" placeholder="https://..." />
            <button class="save-btn" @click="handleUpdateAvatar">Update Photo</button>
          </div>
          <div v-if="avatarUrl" class="avatar-preview-small">
            <img :src="avatarUrl" alt="Preview" @error="avatarUrl = ''" />
          </div>
        </div>
      </section>

      <!-- SECURITY SECTION -->
      <section class="settings-section">
        <h2>Security</h2>
        <div class="setting-item vertical">
          <label>New Password</label>
          <input type="password" v-model="newPassword" placeholder="Min. 6 characters" class="full-input" />
          <label style="margin-top: 0.5rem;">Confirm New Password</label>
          <input type="password" v-model="confirmPassword" class="full-input" />
          <button class="save-btn" @click="handleUpdatePassword" style="margin-top: 1rem;">Update Password</button>
        </div>
      </section>

      <!-- APPEARANCE SECTION -->
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
            <p>Delete all your created quizzes. This cannot be undone.</p>
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
.settings-container { padding: 4rem 2rem; display: flex; justify-content: center; min-height: 90vh; background-color: var(--color-background-soft); color: var(--color-text); }
.settings-card { background: var(--card-bg); padding: 2.5rem; border-radius: 16px; width: 100%; max-width: 600px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid var(--color-border); }
h1 { margin-bottom: 2rem; font-size: 2rem; color: var(--color-text); }
.settings-section { margin-bottom: 2.5rem; }
.settings-section h2 { font-size: 1.1rem; color: #8b5cf6; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }

.setting-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
.setting-item.vertical { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
.setting-item label { font-size: 0.9rem; font-weight: 600; opacity: 0.8; }

.input-row { display: flex; gap: 0.5rem; width: 100%; }
.input-row input { flex: 1; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-soft); color: var(--color-text); }
.full-input { width: 100%; padding: 0.6rem; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-background-soft); color: var(--color-text); }

.avatar-preview-small { margin-top: 1rem; width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid #8b5cf6; }
.avatar-preview-small img { width: 100%; height: 100%; object-fit: cover; }

.save-btn { background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: opacity 0.2s; }
.save-btn:hover { opacity: 0.9; }

.danger-zone { border: 1px solid #fee2e2; padding: 1.5rem; border-radius: 12px; background: rgba(239, 68, 68, 0.05); }
.danger-zone h2 { color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
.reset-btn { background: #ef4444; color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }

/* Switch Styles */
.switch { position: relative; display: inline-block; width: 50px; height: 28px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
.slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
input:checked + .slider { background-color: #8b5cf6; }
input:checked + .slider:before { transform: translateX(22px); }

/* Modal */
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; backdrop-filter: blur(2px); }
.confirm-card { background: var(--card-bg); padding: 2rem; border-radius: 12px; text-align: center; max-width: 400px; color: var(--color-text); border: 1px solid var(--color-border); }
.actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.btn-secondary { flex: 1; padding: 0.8rem; border: 1px solid var(--color-border); background: transparent; color: var(--color-text); border-radius: 8px; cursor: pointer; }
.btn-danger { flex: 1; padding: 0.8rem; border: none; background: #ef4444; color: white; border-radius: 8px; cursor: pointer; font-weight: 600; }
</style>
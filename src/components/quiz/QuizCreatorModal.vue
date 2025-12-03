<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close'])

const quizTitle = ref('')
const quizDescription = ref('')
const questions = ref([])
const errorMessage = ref('')
const showConfirmModal = ref(false)

const handleAddQuestion = () => {
  questions.value.push({
    id: Date.now(),
    text: '',
    options: ['', '', '', ''],
    correctAnswer: 0
  })
  if (errorMessage.value) errorMessage.value = ''
}

const removeQuestion = (index) => {
  questions.value.splice(index, 1)
}

const validateForm = () => {
  errorMessage.value = ''

  if (!quizTitle.value.trim()) {
    errorMessage.value = 'Please enter a quiz title.'
    return false
  }
  if (!quizDescription.value.trim()) {
    errorMessage.value = 'Please enter a description.'
    return false
  }
  if (questions.value.length === 0) {
    errorMessage.value = 'Please add at least one question.'
    return false
  }

  for (let i = 0; i < questions.value.length; i++) {
    const q = questions.value[i]
    if (!q.text.trim()) {
      errorMessage.value = `Question ${i + 1} is empty.`
      return false
    }
    for (let j = 0; j < q.options.length; j++) {
      if (!q.options[j].trim()) {
        errorMessage.value = `Option ${j + 1} in Question ${i + 1} is empty.`
        return false
      }
    }
  }

  return true
}

const handleSaveClick = () => {
  if (validateForm()) {
    showConfirmModal.value = true
  }
}

    const confirmSave = async () => {
      const userStr = localStorage.getItem('user')
      if (!userStr) {
        alert("You must be logged in to save a quiz.")
        return
      }
      const user = JSON.parse(userStr)

      const quizData = {
        user_id: user.id,
        title: quizTitle.value,
        description: quizDescription.value,
        questions: questions.value.map(q => ({
          text: q.text,
          options: q.options,
          correctAnswer: q.correctAnswer
        }))
      }

      try {
        const response = await fetch('http://localhost:8000/backend/create_quiz.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(quizData)
        })

        const result = await response.json()

        if (result.success) {
          console.log("Quiz saved!", result)
          showConfirmModal.value = false
          emit('close')
        } else {
          alert("Error saving quiz: " + result.message)
        }
      } catch (error) {
        console.error("Network error:", error)
        alert("Failed to connect to server.")
      }
    }

    const handleCancel = () => {
  emit('close')
}
</script>


<template>
  <div class="modal-overlay" @click.self="handleCancel">
    <div class="creator-card">

      <!-- Header -->
      <div class="card-header">
        <h2>Create New Quiz</h2>
        <button class="close-btn" @click="handleCancel">×</button>
      </div>

      <!-- Form Body -->
      <div class="form-body">
        <div class="input-group">
          <label>Quiz Title</label>
          <input
              type="text"
              v-model="quizTitle"
              placeholder="Enter an engaging title"
          />
        </div>

        <div class="input-group">
          <label>Description</label>
          <textarea
              v-model="quizDescription"
              placeholder="Describe what your quiz is about"
              rows="3"
          ></textarea>
        </div>

        <!-- Questions Section -->
        <div class="questions-section">
          <div class="questions-header">
            <label>Questions ({{ questions.length }})</label>
            <button class="add-question-btn" @click="handleAddQuestion">
              + Add Question
            </button>
          </div>

          <!-- Empty State -->
          <div class="questions-empty-state" v-if="questions.length === 0">
            <p>No questions yet. Click "Add Question" to get started!</p>
          </div>

          <!-- Questions List -->
          <div class="questions-list" v-else>
            <div v-for="(question, qIndex) in questions" :key="question.id" class="question-card">

              <div class="question-header-row">
                <span class="question-number">Q{{ qIndex + 1 }}</span>
                <button class="remove-btn" @click="removeQuestion(qIndex)">Remove</button>
              </div>

              <div class="input-group sm-mb">
                <input
                    v-model="question.text"
                    placeholder="What is the question?"
                    class="question-input"
                />
              </div>

              <div class="options-grid">
                <div
                    v-for="(option, oIndex) in question.options"
                    :key="oIndex"
                    class="option-item"
                    :class="{ 'is-correct': question.correctAnswer === oIndex }"
                >
                  <input
                      type="radio"
                      :name="'correct-answer-' + question.id"
                      :value="oIndex"
                      v-model="question.correctAnswer"
                      title="Mark as correct answer"
                  />
                  <input
                      type="text"
                      v-model="question.options[oIndex]"
                      :placeholder="'Option ' + (oIndex + 1)"
                  />
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Error Message Display -->
      <div v-if="errorMessage" class="error-banner">
        ⚠️ {{ errorMessage }}
      </div>

      <!-- Footer Actions -->
      <div class="card-footer">
        <button class="save-btn" @click="handleSaveClick">
          <span class="icon">💾</span> Save Quiz
        </button>
        <button class="cancel-btn" @click="handleCancel">
          Cancel
        </button>
      </div>

    </div>

    <!-- Confirm Save Modal -->
    <div v-if="showConfirmModal" class="confirm-overlay">
      <div class="confirm-card">
        <h3>Ready to Save?</h3>
        <p>Are you sure you want to create this quiz? It will be visible to other players.</p>

        <div class="confirm-actions">
          <button class="confirm-btn-secondary" @click="showConfirmModal = false">No, keep editing</button>
          <button class="confirm-btn-primary" @click="confirmSave">Yes, create quiz</button>
        </div>
      </div>
    </div>

  </div>
</template>


<style scoped>
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
  z-index: 1000;
  backdrop-filter: blur(3px);
}

.creator-card {
  background: white;
  width: 90%;
  max-width: 800px;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  animation: modal-pop 0.3s ease-out;
}

@keyframes modal-pop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Header */
.card-header {
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f3f4f6;
  background: white;
  z-index: 10;
}

.card-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #9ca3af;
  cursor: pointer;
  line-height: 1;
  padding: 0;
}

.close-btn:hover {
  color: #6b7280;
}

/* Form Body */
.form-body {
  padding: 2rem;
  overflow-y: auto;
}

.input-group {
  margin-bottom: 1.5rem;
}
.input-group.sm-mb {
  margin-bottom: 1rem;
}

.input-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  color: #4b5563;
  margin-bottom: 0.5rem;
}

.input-group input,
.input-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background-color: #f9fafb;
  font-size: 0.95rem;
  color: #1f2937;
  transition: all 0.2s;
  font-family: inherit;
}

.input-group input:focus,
.input-group textarea:focus {
  outline: none;
  border-color: #8b5cf6;
  background-color: white;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.input-group textarea {
  resize: vertical;
  min-height: 80px;
}

/* Questions Section */
.questions-section {
  margin-top: 2rem;
  border-top: 1px solid #e5e7eb;
  padding-top: 1.5rem;
}

.questions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.add-question-btn {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
}

.questions-empty-state {
  background-color: #f9fafb;
  border: 1px dashed #d1d5db;
  border-radius: 8px;
  padding: 3rem;
  text-align: center;
  color: #6b7280;
}

/* Question Card Styles */
.question-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.question-header-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.question-number {
  font-weight: 700;
  color: #8b5cf6;
  font-size: 0.9rem;
}

.remove-btn {
  color: #ef4444;
  background: none;
  border: none;
  font-size: 0.8rem;
  cursor: pointer;
  text-decoration: underline;
}

.question-input {
  font-weight: 600;
}

.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f9fafb;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid transparent;
}

.option-item.is-correct {
  border-color: #10b981;
  background-color: #ecfdf5;
}

.option-item input[type="radio"] {
  width: auto;
  margin: 0;
  cursor: pointer;
  accent-color: #10b981;
}

/* Error Banner */
.error-banner {
  background-color: #fee2e2;
  color: #b91c1c;
  padding: 0.75rem 2rem;
  font-size: 0.9rem;
  font-weight: 500;
  border-top: 1px solid #fecaca;
  display: flex;
  align-items: center;
  gap: 8px;
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Footer */
.card-footer {
  padding: 1.5rem 2rem;
  background-color: #f9fafb;
  border-top: 1px solid #f3f4f6;
  display: flex;
  gap: 1rem;
}

.save-btn {
  flex: 1;
  background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.cancel-btn {
  background-color: #e5e7eb;
  color: #4b5563;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

.cancel-btn:hover {
  background-color: #d1d5db;
}

/* Confirm Modal Styles */
.confirm-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  backdrop-filter: blur(2px);
  border-radius: 12px; /* Aby neprekračovalo rohy rodiča */
}

.confirm-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
  animation: modal-pop 0.2s ease-out;
}

.confirm-card h3 {
  margin-bottom: 0.5rem;
  color: #1f2937;
  font-size: 1.25rem;
  font-weight: 600;
}

.confirm-card p {
  color: #6b7280;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
  line-height: 1.5;
}

.confirm-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.confirm-btn-primary {
  background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  color: white;
  border: none;
  padding: 10px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.confirm-btn-primary:hover {
  opacity: 0.9;
}

.confirm-btn-secondary {
  background: transparent;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  padding: 10px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.confirm-btn-secondary:hover {
  background: #f9fafb;
  color: #374151;
  border-color: #d1d5db;
}
</style>
<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  editData: { type: Object, default: null }
})

const emit = defineEmits(['close', 'saved'])

const isEditMode = !!props.editData
const quizTitle = ref(props.editData?.title || '')
const quizDescription = ref(props.editData?.description || '')
const isPublic = ref(props.editData ? Boolean(Number(props.editData.is_public)) : true)
const questions = ref([])
const errorMessage = ref('')
const showConfirmModal = ref(false)

// Ak upravujeme, musíme stiahnuť aj otázky z DB
const fetchQuestionsForEdit = async () => {
  if (!isEditMode) return
  try {
    const response = await fetch(`http://localhost:8000/backend/api.php?action=get_quiz_details&id=${props.editData.id}`)
    const result = await response.json()
    if (result.success) {
      // Namapujeme dáta späť na formát pre formulár
      questions.value = result.questions.map(q => ({
        id: q.id,
        text: q.question_text,
        options: q.options,
        correctAnswer: parseInt(q.correct_option_index)
      }))
    }
  } catch (e) { console.error(e) }
}

onMounted(fetchQuestionsForEdit)

const handleAddQuestion = () => {
  questions.value.push({ id: Date.now(), text: '', options: ['', '', '', ''], correctAnswer: 0 })
  if (errorMessage.value) errorMessage.value = ''
}

const removeQuestion = (index) => { questions.value.splice(index, 1) }

const validateForm = () => {
  if (!quizTitle.value.trim() || !quizDescription.value.trim() || questions.value.length === 0) {
    errorMessage.value = 'Please fill in title, description and at least one question.';
    return false;
  }
  return true;
}

const handleSaveClick = () => { if (validateForm()) showConfirmModal.value = true; }

const confirmSave = async () => {
  const user = JSON.parse(localStorage.getItem('user'))

  const quizData = {
    user_id: user.id,
    title: quizTitle.value,
    description: quizDescription.value,
    is_public: isPublic.value,
    questions: questions.value.map(q => ({
      text: q.text,
      options: q.options,
      correctAnswer: q.correctAnswer
    }))
  }
  if (isEditMode) quizData.quiz_id = props.editData.id

  try {
    const response = await fetch(`http://localhost:8000/backend/api.php?action=save_quiz`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(quizData)
    })
    const result = await response.json()
    if (result.success) {
      emit('saved')
      emit('close')
    } else { alert(result.message) }
  } catch (e) { console.error(e) }
}
</script>

<template>
  <div class="modal-overlay" @click.self="emit('close')">
    <div class="creator-card">
      <div class="card-header">
        <h2>{{ isEditMode ? 'Edit Quiz' : 'Create New Quiz' }}</h2>
        <button class="close-btn" @click="emit('close')">×</button>
      </div>

      <div class="form-body">
        <div class="input-group">
          <label>Quiz Title</label>
          <input type="text" v-model="quizTitle" placeholder="Enter title" />
        </div>

        <div class="input-group">
          <label>Description</label>
          <textarea v-model="quizDescription" placeholder="Description" rows="3"></textarea>
        </div>

        <!-- PEKNÝ FIALOVÝ SWITCH -->
        <div class="input-group status-row">
          <label>Public Visibility</label>
          <label class="switch">
            <input type="checkbox" v-model="isPublic">
            <span class="slider round"></span>
          </label>
        </div>

        <!-- PÔVODNÁ SEKCIA OTÁZOK -->
        <div class="questions-section">
          <div class="questions-header">
            <label>Questions ({{ questions.length }})</label>
            <button class="add-question-btn" @click="handleAddQuestion">+ Add Question</button>
          </div>

          <div v-if="questions.length === 0" class="questions-empty-state">No questions yet.</div>

          <div v-else class="questions-list">
            <div v-for="(question, qIndex) in questions" :key="qIndex" class="question-card">
              <div class="question-header-row">
                <span class="question-number">Question {{ qIndex + 1 }}</span>
                <button class="remove-q-btn" @click="removeQuestion(qIndex)" title="Remove Question">×</button>
              </div>
              <input v-model="question.text" placeholder="What is the question?" class="question-input" />

              <div class="options-grid">
                <div v-for="(option, oIndex) in question.options" :key="oIndex" class="option-item" :class="{ 'is-correct': question.correctAnswer === oIndex }">
                  <input type="radio" :name="'correct-' + qIndex" :value="oIndex" v-model="question.correctAnswer" />
                  <input type="text" v-model="question.options[oIndex]" :placeholder="'Option ' + (oIndex + 1)" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="error-banner">⚠️ {{ errorMessage }}</div>

      <div class="card-footer">
        <button class="save-btn" @click="handleSaveClick">💾 Save Quiz</button>
        <button class="cancel-btn" @click="emit('close')">Cancel</button>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div v-if="showConfirmModal" class="confirm-overlay">
      <div class="confirm-card">
        <h3>Ready to Save?</h3>
        <div class="confirm-actions">
          <button class="confirm-btn-secondary" @click="showConfirmModal = false">No</button>
          <button class="confirm-btn-primary" @click="confirmSave">Yes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* MODAL OVERLAY & CARD */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.5); display: flex; align-items: center;
  justify-content: center; z-index: 1000; backdrop-filter: blur(3px);
}
.creator-card {
  background: var(--card-bg); width: 90%; max-width: 800px;
  border-radius: 12px; display: flex; flex-direction: column;
  max-height: 90vh; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}
.card-header {
  padding: 1.5rem 2rem; display: flex; justify-content: space-between;
  align-items: center; border-bottom: 1px solid var(--color-border);
}
.close-btn { background: none; border: none; font-size: 2rem; color: var(--color-text); cursor: pointer; opacity: 0.5; }
.close-btn:hover { opacity: 1; }

.form-body { padding: 2rem; overflow-y: auto; }
.input-group { margin-bottom: 1.5rem; display: flex; flex-direction: column; }
.input-group label { font-weight: 600; margin-bottom: 0.5rem; color: var(--color-text); }
input, textarea {
  padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 6px;
  background: var(--color-background-soft); color: var(--color-text);
  font-family: inherit;
}

/* PUBLIC/PRIVATE SWITCH */
.status-row {
  flex-direction: row; justify-content: space-between; align-items: center;
  background: var(--color-background-soft); padding: 1rem; border-radius: 8px;
}
.switch { position: relative; display: inline-block; width: 50px; height: 26px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc; transition: .4s; border-radius: 34px;
}
.slider:before {
  position: absolute; content: ""; height: 18px; width: 18px;
  left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;
}
input:checked + .slider { background-color: #8b5cf6; }
input:checked + .slider:before { transform: translateX(24px); }

/* QUESTIONS SECTION */
.questions-section { margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.5rem; }
.questions-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.add-question-btn {
  background: #8b5cf6; color: white; border: none; padding: 0.6rem 1.2rem;
  border-radius: 6px; cursor: pointer; font-weight: 600; transition: opacity 0.2s;
}
.add-question-btn:hover { opacity: 0.9; }

.question-card {
  background: var(--color-background-soft); border: 1px solid var(--color-border);
  border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; position: relative;
}
.question-header-row {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;
}
.question-number { font-weight: 700; color: #8b5cf6; font-size: 1rem; }

/* REMOVE BUTTON STYLE */
.remove-q-btn {
  background: rgba(239, 68, 68, 0.1); color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2); width: 30px; height: 30px;
  border-radius: 6px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 1.2rem; transition: all 0.2s;
}
.remove-q-btn:hover { background: #ef4444; color: white; }

.question-input { width: 100%; margin-bottom: 1rem; font-weight: 500; }

/* GRID LOGIC */
.options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }

@media (max-width: 600px) {
  .options-grid { grid-template-columns: 1fr; }
  .creator-card { width: 95%; max-height: 95vh; }
  .form-body { padding: 1rem; }
  .card-header, .card-footer { padding: 1rem; }
}

.option-item {
  display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem;
  border-radius: 6px; border: 1px solid transparent; transition: background 0.2s;
}
.option-item input[type="text"] { flex: 1; padding: 0.5rem; }
.option-item.is-correct { border-color: #10b981; background: rgba(16, 185, 129, 0.1); }

/* FOOTER */
.card-footer {
  padding: 1.5rem 2rem; background: var(--color-background-soft);
  border-top: 1px solid var(--color-border); display: flex; gap: 1rem;
}
.save-btn {
  flex: 2; background: linear-gradient(90deg, #8b5cf6, #3b82f6);
  color: white; border: none; padding: 0.75rem; border-radius: 6px;
  font-weight: 600; cursor: pointer;
}
.cancel-btn {
  flex: 1; background: var(--color-border); color: var(--color-text);
  border: none; padding: 0.75rem; border-radius: 6px; cursor: pointer;
}

.error-banner { background: #fee2e2; color: #b91c1c; padding: 1rem; text-align: center; font-weight: 500; }

/* CONFIRM MODAL */
.confirm-overlay {
  position: absolute; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.4); display: flex; align-items: center;
  justify-content: center; z-index: 1100; backdrop-filter: blur(2px);
}
.confirm-card {
  background: var(--card-bg); padding: 2rem; border-radius: 12px;
  text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2); color: var(--color-text);
}
.confirm-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
.confirm-btn-primary { background: #8b5cf6; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; flex: 1; }
.confirm-btn-secondary { background: none; border: 1px solid var(--color-border); color: var(--color-text); padding: 10px 20px; border-radius: 8px; cursor: pointer; flex: 1; }
</style>
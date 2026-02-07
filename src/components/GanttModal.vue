<template>
  <Teleport to="body">
    <div v-if="show" class="gantt-modal__backdrop" @click.self="onClose">
      <div class="gantt-modal" role="dialog" aria-modal="true" aria-label="Configurar tarefa">
        <button class="gantt-modal__close" type="button" aria-label="Fechar" @click="onClose">×</button>
        <h2 class="gantt-modal__title">Configurar tarefa</h2>
        <p v-if="task" class="gantt-modal__subtitle">
          {{ task.name }} (ID {{ task.id }})
        </p>

        <div class="gantt-modal__field">
          <label for="start-date">Data de Início</label>
          <input id="start-date" v-model="local.start" type="date">
        </div>

        <div class="gantt-modal__field">
          <label for="due-date">Data de Entrega</label>
          <input id="due-date" v-model="local.dueDate" type="date">
        </div>

        <div class="gantt-modal__field">
          <label for="progress">Progresso (%)</label>
          <input id="progress" v-model.number="local.progress" type="number" min="0" max="100" step="1">
        </div>

        <div class="gantt-modal__field">
          <label for="predecessors">Predecessores (IDs separados por vírgula)</label>
          <input id="predecessors" v-model="local.predecessors" type="text" placeholder="12, 18, 25">
        </div>

        <div class="gantt-modal__actions">
          <button class="gantt-modal__button gantt-modal__button--danger" type="button" @click="onDelete">
            Excluir
          </button>
          <div class="gantt-modal__spacer"></div>
          <button class="gantt-modal__button" type="button" @click="onClose">Cancelar</button>
          <button class="gantt-modal__button gantt-modal__button--primary" type="button" @click="onSave">
            Salvar
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
const handleEsc = (event, closeFn) => {
  if (event.key === 'Escape') {
    closeFn()
  }
}

export default {
  name: 'GanttModal',
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    task: {
      type: Object,
      default: null,
    },
    metadata: {
      type: Object,
      default: () => ({ start: '', progress: 0, predecessors: [] }),
    },
    dueDate: {
      type: String,
      default: '',
    },
  },
  emits: ['close', 'save', 'delete'],
  data() {
    return {
      local: {
        start: '',
        dueDate: '',
        progress: 0,
        predecessors: '',
      },
      escHandler: null,
    }
  },
  watch: {
    show(value) {
      if (value) {
        this.syncFromProps()
        this.bindEsc()
      } else {
        this.unbindEsc()
      }
    },
  },
  unmounted() {
    this.unbindEsc()
  },
  methods: {
    bindEsc() {
      if (this.escHandler) return
      this.escHandler = (event) => handleEsc(event, this.onClose)
      window.addEventListener('keydown', this.escHandler)
    },
    unbindEsc() {
      if (!this.escHandler) return
      window.removeEventListener('keydown', this.escHandler)
      this.escHandler = null
    },
    syncFromProps() {
      this.local.start = this.metadata?.start || ''
      this.local.dueDate = this.dueDate || ''
      this.local.progress = Number.isFinite(this.metadata?.progress)
        ? this.metadata.progress
        : 0
      this.local.predecessors = Array.isArray(this.metadata?.predecessors)
        ? this.metadata.predecessors.join(', ')
        : ''
    },
    onClose() {
      this.$emit('close')
    },
    onSave() {
      const predecessors = this.local.predecessors
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean)

      this.$emit('save', {
        start: this.local.start,
        dueDate: this.local.dueDate,
        progress: Math.min(100, Math.max(0, Number(this.local.progress || 0))),
        predecessors,
      })
    },
    onDelete() {
      this.$emit('delete')
    },
  },
}
</script>

<style scoped>
.gantt-modal__backdrop {
  position: fixed;
  inset: 0;
  background: radial-gradient(circle at top, rgba(15, 23, 42, 0.45), rgba(15, 23, 42, 0.7));
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
  padding: 24px;
}

.gantt-modal {
  width: min(560px, 92vw);
  padding: 24px;
  background: #ffffff;
  color: #1d2d44;
  border-radius: 16px;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.25);
  position: relative;
  display: grid;
  gap: 12px;
}

.gantt-modal__close {
  position: absolute;
  top: 14px;
  right: 14px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: rgba(42, 110, 247, 0.1);
  color: #2a6ef7;
  font-size: 20px;
  cursor: pointer;
}

.gantt-modal__title {
  margin: 0;
  font-size: 20px;
}

.gantt-modal__subtitle {
  margin: -6px 0 4px;
  font-size: 13px;
  opacity: 0.7;
}

.gantt-modal__field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.gantt-modal__field label {
  font-size: 12px;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: rgba(29, 45, 68, 0.7);
}

.gantt-modal__field input {
  padding: 10px 12px;
  border: 1px solid rgba(42, 110, 247, 0.2);
  border-radius: 10px;
  background: #f9fbff;
  font-size: 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.gantt-modal__field input:focus {
  outline: none;
  border-color: #2a6ef7;
  box-shadow: 0 0 0 3px rgba(42, 110, 247, 0.15);
}

.gantt-modal__actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 6px;
}

.gantt-modal__spacer {
  flex: 1;
}

.gantt-modal__button {
  padding: 10px 18px;
  border-radius: 999px;
  border: 1px solid rgba(42, 110, 247, 0.2);
  background: #f2f5ff;
  color: #1d2d44;
  cursor: pointer;
  font-weight: 600;
}

.gantt-modal__button--primary {
  background: #2a6ef7;
  color: #fff;
  border-color: #2a6ef7;
}

.gantt-modal__button--danger {
  background: #ffecec;
  border-color: #ffb3b3;
  color: #b91c1c;
}
</style>

<template>
  <div class="app">
    <header ref="headerRef" class="app__header">
      <div>
        <h1>Gantt + Deck (Teste)</h1>
        <p>Arraste, redimensione e sincronize com o mock do Deck.</p>
      </div>
      <div class="app__config">
        <label>
          Board ID
          <input v-model="boardId" type="number" min="1" placeholder="Ex: 1">
        </label>
        <label>
          Stack ID
          <input v-model="stackId" type="number" min="1" placeholder="Ex: 1">
        </label>
        <button type="button" @click="loadCards">Carregar</button>
      </div>
    </header>

    <section v-if="settingsOpen" ref="settingsRef" class="settings">
      <h3>Configurações</h3>
      <div class="settings__grid">
        <label>
          Modo de visualização
          <select v-model="settings.viewMode">
            <option value="Day">Dia</option>
            <option value="Week">Semana</option>
            <option value="Month">Mês</option>
            <option value="Year">Ano</option>
          </select>
        </label>
        <label>
          Tamanho da coluna
          <select v-model.number="settings.columnWidth">
            <option :value="24">Curto</option>
            <option :value="32">Médio</option>
            <option :value="42">Largo</option>
          </select>
        </label>
        <label>
          Altura do header
          <input v-model.number="settings.headerHeight" type="number" min="40" max="80" step="2">
        </label>
        <label>
          Largura da lista
          <input v-model.number="settings.sidebarWidth" type="number" min="180" max="360" step="10">
        </label>
        <label>
          Formato de data
          <select v-model="settings.dateFormat">
            <option value="DD/MM">DD/MM</option>
            <option value="MM/DD">MM/DD</option>
            <option value="YYYY-MM-DD">YYYY-MM-DD</option>
          </select>
        </label>
        <label>
          Tooltip
          <select v-model="settings.popupTrigger">
            <option value="click">Clique</option>
            <option value="mouseenter">Hover</option>
          </select>
        </label>
        <label>
          Tema
          <select v-model="settings.theme">
            <option value="default">Padrão</option>
            <option value="soft">Suave</option>
            <option value="contrast">Alto contraste</option>
          </select>
        </label>
        <label class="settings__toggle">
          <input v-model="settings.showDependencies" type="checkbox">
          Mostrar dependências
        </label>
        <label class="settings__toggle">
          <input v-model="settings.showProgress" type="checkbox">
          Mostrar progresso
        </label>
        <label class="settings__toggle">
          <input v-model="settings.showLabels" type="checkbox">
          Mostrar rótulo na barra
        </label>
        <label class="settings__toggle">
          <input v-model="settings.showStripes" type="checkbox">
          Listras no fundo
        </label>
        <label class="settings__toggle">
          <input v-model="settings.reduceMotion" type="checkbox">
          Reduzir animações
        </label>
        <label class="settings__toggle">
          <input v-model="settings.showIds" type="checkbox">
          Mostrar ID na lista
        </label>
      </div>
    </section>

    <main class="app__main" :style="mainStyle">
      <aside class="task-list">
        <div class="task-list__header">
          <h3>Ordenar tarefas</h3>
          <button class="task-list__add" type="button" @click="createTask" aria-label="Nova tarefa">
            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
              <path
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                d="M12 5v14M5 12h14"
              />
            </svg>
          </button>
        </div>
        <div class="task-list__spacer" aria-hidden="true"></div>
        <div
          v-for="(task, index) in taskList"
          :key="task.id"
          class="task-list__item"
          draggable="true"
          @dragstart="onDragStart(index)"
          @dragover.prevent
          @drop="onDrop(index)"
        >
          <div class="task-list__card" @click="openTaskFromList(task)">
            <span class="task-list__handle">::</span>
            <span>{{ task.name }}</span>
            <span v-if="settings.showIds" class="task-list__id">#{{ task.id }}</span>
          </div>
        </div>
        <button class="task-list__settings" type="button" @click="toggleSettings" aria-label="Gantt settings">
          <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
            <path
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm8 3.5-1.6.9.1 1.9-2 1.1-1.2-1.5-1.8.4-.6 1.8h-2.3l-.6-1.8-1.8-.4-1.2 1.5-2-1.1.1-1.9L4 12l.9-1.6-1.1-2 1.5-1.2-.4-1.8 1.8-.6h2.3l.6 1.8 1.8.4 1.2-1.5 2 1.1-.1 1.9L20 12Z"
            />
          </svg>
          <span>Gantt settings</span>
        </button>
      </aside>
      <div
        ref="ganttContainer"
        class="gantt"
        :class="{
          'gantt--no-deps': !settings.showDependencies,
          'gantt--no-progress': !settings.showProgress,
          'gantt--no-labels': !settings.showLabels,
          'gantt--no-stripes': !settings.showStripes,
          'gantt--reduce-motion': settings.reduceMotion,
        }"
      ></div>
    </main>

    <GanttModal
      :show="modalOpen"
      :task="selectedTask"
      :metadata="selectedMetadata"
      :due-date="selectedDueDate"
      @close="closeModal"
      @save="saveModal"
      @delete="deleteTask"
    />
  </div>
</template>

<script>
import { ref, nextTick, watch, computed, onMounted, onBeforeUnmount } from 'vue'
import Gantt from 'frappe-gantt'

import GanttModal from './components/GanttModal.vue'
import { parseCardDescription, serializeCardDescription } from './utils/ganttMetadata'
import { deckClient } from './api/deckClient'

function dateOnly(date) {
  if (!date) return ''
  return date.toISOString().slice(0, 10)
}

function isoFromDateOnly(value) {
  if (!value) return null
  const date = new Date(`${value}T00:00:00`)
  return date.toISOString()
}

export default {
  name: 'App',
  components: { GanttModal },
  setup() {
    const boardId = ref('1')
    const stackId = ref('1')
    const headerRef = ref(null)
    const settingsRef = ref(null)
    const ganttContainer = ref(null)
    const gantt = ref(null)
    const taskList = ref([])
    const dragIndex = ref(null)
    const dragState = ref({
      active: false,
      taskId: null,
      fromIndex: null,
      hoverIndex: null,
    })
    const cardsById = ref({})
    const metadataById = ref({})
    const selectedTask = ref(null)
    const selectedMetadata = ref({ start: '', progress: 0, predecessors: [] })
    const selectedDueDate = ref('')
    const modalOpen = ref(false)
    const settingsOpen = ref(false)
    const settings = ref({
      viewMode: 'Day',
      columnWidth: 30,
      headerHeight: 50,
      sidebarWidth: 240,
      dateFormat: 'DD/MM',
      popupTrigger: 'click',
      theme: 'default',
      showDependencies: true,
      showProgress: true,
      showLabels: true,
      showStripes: true,
      reduceMotion: false,
      showIds: false,
    })

    const density = { barHeight: 20, padding: 18 }

    const resizeTick = ref(0)

    const mainStyle = computed(() => {
      resizeTick.value
      const headerHeight = headerRef.value?.offsetHeight || 0
      const settingsHeight = settingsOpen.value ? (settingsRef.value?.offsetHeight || 0) : 0
      const padding = 24 * 2
      const gap = 20
      const available = window.innerHeight - headerHeight - settingsHeight - padding - gap

      const rowHeight = density.barHeight + density.padding
      const rows = taskList.value.filter((task) => !task.custom_class).length + 1
      const content = settings.value.headerHeight + density.padding + rows * rowHeight
      const innerPadding = 16 * 2
      const extraForScroll = 32

      const height = Math.max(260, Math.min(available, content + innerPadding + extraForScroll))
      return {
        height: `${height}px`,
      }
    })

    const loadSettings = () => {
      try {
        const saved = JSON.parse(localStorage.getItem('ganttSettings') || '{}')
        settings.value = { ...settings.value, ...saved }
      } catch (err) {
        // ignore
      }
    }

    const persistSettings = () => {
      localStorage.setItem('ganttSettings', JSON.stringify(settings.value))
    }

    const buildTasks = (cards) => {
      metadataById.value = {}
      const tasks = cards.map((card) => {
        const { metadata } = parseCardDescription(card.description || '')
        metadataById.value[card.id] = metadata

        const start = metadata.start
        const end = card.duedate ? card.duedate.slice(0, 10) : start

        return {
          id: String(card.id),
          name: card.title,
          start,
          end,
          progress: metadata.progress,
          dependencies: metadata.predecessors.join(','),
        }
      })

      return tasks
    }

    const addRangePadding = (tasks) => tasks

    const extraPaddingDays = ref(365)
    const isExtending = ref(false)
    const skipCenterRef = ref(false)

    const initGantt = async (tasks) => {
      await nextTick()
      if (!ganttContainer.value) return

      const density = { barHeight: 20, padding: 18 }
      document.documentElement.style.setProperty('--gantt-bar-height', `${density.barHeight}px`)
      document.documentElement.style.setProperty('--gantt-padding', `${density.padding}px`)
      document.documentElement.style.setProperty('--gantt-header-height', `${settings.value.headerHeight}px`)
      document.documentElement.style.setProperty('--sidebar-width', `${settings.value.sidebarWidth}px`)

      const themeVars = {
        default: { primary: '#2a6ef7', background: '#f5f6fb' },
        soft: { primary: '#4c8bf5', background: '#f1f4fb' },
        contrast: { primary: '#0b5cff', background: '#eef1ff' },
      }
      const theme = themeVars[settings.value.theme] || themeVars.default
      document.documentElement.style.setProperty('--color-primary', theme.primary)
      document.documentElement.style.setProperty('--color-main-background', theme.background)

      const tasksForGantt = (() => {
        if (!tasks.length) return tasks
        const last = tasks.reduce((max, task) => {
          const date = new Date(task.end || task.start)
          return date > max ? date : max
        }, new Date(tasks[0].end || tasks[0].start))
        const spacerDate = last.toISOString().slice(0, 10)
        return [
          ...tasks,
          {
            id: '__spacer-row__',
            name: '',
            start: spacerDate,
            end: spacerDate,
            progress: 0,
            dependencies: '',
            custom_class: 'gantt-spacer',
          },
        ]
      })()

      gantt.value = new Gantt(ganttContainer.value, tasksForGantt, {
        bar_height: density.barHeight,
        padding: density.padding,
        header_height: settings.value.headerHeight,
        column_width: settings.value.columnWidth,
        view_mode: settings.value.viewMode,
        date_format: settings.value.dateFormat,
        popup_trigger: settings.value.popupTrigger,
        on_click(task) {
          openModal(task)
        },
        on_date_change(task, start, end) {
          const metadata = metadataById.value[task.id] || { start: dateOnly(start), progress: task.progress, predecessors: [] }
          metadata.start = dateOnly(start)
          metadata.progress = task.progress

          const dueDate = dateOnly(end)

          persistCard(task.id, metadata, dueDate)
        },
        on_progress_change(task, progress) {
          const metadata = metadataById.value[task.id] || { start: task.start, progress: 0, predecessors: [] }
          metadata.progress = progress
          persistCard(task.id, metadata, null)
        },
      })
      // Do not auto-move dependent tasks when dragging a parent.
      gantt.value.get_all_dependent_tasks = () => []

      const setExactRange = () => {
        const realTasks = tasksForGantt.filter((task) => !task.custom_class)
        if (!realTasks.length) return
        const start = realTasks.reduce((min, task) => {
          const date = new Date(task.start)
          return date < min ? date : min
        }, new Date(realTasks[0].start))
        const end = realTasks.reduce((max, task) => {
          const date = new Date(task.end || task.start)
          return date > max ? date : max
        }, new Date(realTasks[0].end || realTasks[0].start))
        start.setHours(0, 0, 0, 0)
        end.setHours(0, 0, 0, 0)
        end.setDate(end.getDate() + extraPaddingDays.value)
        gantt.value.gantt_start = start
        gantt.value.gantt_end = end
      }

      setExactRange()
      gantt.value.setup_dates()
      gantt.value.render()
      const svgEl = gantt.value.$svg
      if (svgEl && gantt.value.dates?.length) {
        const width = gantt.value.dates.length * gantt.value.options.column_width
        svgEl.style.width = `${width}px`
        svgEl.style.minWidth = `${width}px`
      }

      if (!skipCenterRef.value) {
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            centerOnToday()
          })
        })
      } else {
        skipCenterRef.value = false
      }

      const svg = gantt.value.$svg
      if (svg && !svg.__ganttMouseoutBound) {
        svg.__ganttMouseoutBound = true
        svg.addEventListener('mouseout', (event) => {
          const fromBar = event.target?.closest?.('.bar-wrapper')
          const toBar = event.relatedTarget?.closest?.('.bar-wrapper')
          if (fromBar && !toBar) {
            gantt.value.hide_popup()
          }
        })
      }

      if (svg && !svg.__ganttVerticalDragBound) {
        svg.__ganttVerticalDragBound = true

        const getRowIndex = (event) => {
          if (!gantt.value) return null
          const rect = svg.getBoundingClientRect()
          const y = event.clientY - rect.top
          const rowHeight = gantt.value.options.bar_height + gantt.value.options.padding
          const startY = gantt.value.options.header_height + gantt.value.options.padding / 2
          const index = Math.floor((y - startY) / rowHeight)
          if (index < 0 || index >= taskList.value.length) return null
          return index
        }

        const clearDropHighlight = () => {
          svg.querySelectorAll('.grid-row.drop-target').forEach((row) => {
            row.classList.remove('drop-target')
          })
        }

        svg.addEventListener('mousedown', (event) => {
          const isHandle = event.target?.classList?.contains?.('handle')
          const barWrapper = event.target?.closest?.('.bar-wrapper')
          if (!barWrapper || isHandle) return

          const taskId = barWrapper.getAttribute('data-id')
          const fromIndex = taskList.value.findIndex((task) => String(task.id) === String(taskId))
          if (fromIndex === -1) return

          dragState.value = {
            active: true,
            taskId,
            fromIndex,
            hoverIndex: fromIndex,
          }
        })

        svg.addEventListener('mousemove', (event) => {
          if (!dragState.value.active) return
          const index = getRowIndex(event)
          if (index === null) return
          dragState.value.hoverIndex = index
          clearDropHighlight()
          const rows = svg.querySelectorAll('.grid-row')
          if (rows[index]) {
            rows[index].classList.add('drop-target')
          }
        })

        document.addEventListener('mouseup', async () => {
          if (!dragState.value.active) return
          const { fromIndex, hoverIndex } = dragState.value
          clearDropHighlight()
          dragState.value = { active: false, taskId: null, fromIndex: null, hoverIndex: null }
          if (hoverIndex === null || hoverIndex === fromIndex) return

          const updated = [...taskList.value]
          const [moved] = updated.splice(fromIndex, 1)
          updated.splice(hoverIndex, 0, moved)
          taskList.value = updated
          await rebuildGantt()
        })
      }

      // Infinite scroll disabled to avoid re-render jumps; use large padding instead.
    }

    const rebuildGantt = async () => {
      if (!ganttContainer.value) return
      ganttContainer.value.innerHTML = ''
      await initGantt(addRangePadding(taskList.value))
    }

    const refreshTask = (taskId, metadata, dueDate) => {
      if (!gantt.value) return
      gantt.value.update_task(String(taskId), {
        start: metadata.start,
        end: dueDate || metadata.start,
        progress: metadata.progress,
        dependencies: metadata.predecessors.join(','),
      })
    }

    const loadCards = async () => {
      if (!boardId.value || !stackId.value) return

      const data = await deckClient.getStack(boardId.value, stackId.value)
      const cards = Array.isArray(data?.cards) ? data.cards : []

      cardsById.value = cards.reduce((acc, card) => {
        acc[card.id] = card
        return acc
      }, {})

      const tasks = buildTasks(cards)
      taskList.value = tasks
      await initGantt(addRangePadding(taskList.value))
    }

    const persistCard = async (taskId, metadata, dueDate) => {
      const card = cardsById.value[taskId]
      if (!card) return

      metadataById.value[taskId] = metadata

      const updatedDescription = serializeCardDescription(card.description || '', metadata)
      const payload = {
        title: card.title,
        description: updatedDescription,
        type: card.type,
        order: card.order,
        duedate: dueDate ? isoFromDateOnly(dueDate) : card.duedate,
      }

      const updatedCard = await deckClient.updateCard(boardId.value, stackId.value, taskId, payload)

      cardsById.value[taskId] = {
        ...card,
        ...updatedCard,
      }

      const normalizedDue = payload.duedate ? payload.duedate.slice(0, 10) : metadata.start
      refreshTask(taskId, metadata, normalizedDue)
    }

    const deleteTask = async () => {
      if (!selectedTask.value) return
      const ok = window.confirm('Excluir esta tarefa? Essa ação não pode ser desfeita.')
      if (!ok) return

      const taskId = selectedTask.value.id
      modalOpen.value = false

      await deckClient.deleteCard(boardId.value, stackId.value, taskId)

      delete cardsById.value[taskId]
      delete metadataById.value[taskId]

      taskList.value = taskList.value.filter((task) => String(task.id) !== String(taskId))
      selectedTask.value = null

      await rebuildGantt()
    }

    const createTask = async () => {
      const title = window.prompt('Nome da nova tarefa:')
      if (!title) return

      const now = new Date()
      const start = now.toISOString().slice(0, 10)
      const due = new Date(now)
      due.setDate(due.getDate() + 1)
      const duedate = due.toISOString()
      const metadata = { start, progress: 0, predecessors: [] }
      const description = serializeCardDescription('', metadata)

      const tempId = `tmp-${Date.now()}`
      const newCard = {
        id: tempId,
        title,
        description,
        type: 'plain',
        order: taskList.value.length + 1,
        duedate,
      }

      cardsById.value[tempId] = newCard
      taskList.value = buildTasks(Object.values(cardsById.value))
      await rebuildGantt()
    }

    const openModal = (task) => {
      if (!task || task.custom_class === 'gantt-padding' || task.custom_class === 'gantt-spacer') return
      selectedTask.value = task
      selectedMetadata.value = metadataById.value[task.id] || { start: task.start, progress: task.progress, predecessors: [] }
      selectedDueDate.value = task.end
      modalOpen.value = true
    }

    const openTaskFromList = (task) => {
      if (!task || task.custom_class === 'gantt-padding' || task.custom_class === 'gantt-spacer') return
      openModal(task)
    }

    const closeModal = () => {
      modalOpen.value = false
    }

    const saveModal = (payload) => {
      modalOpen.value = false
      if (!selectedTask.value) return

      const metadata = {
        start: payload.start || selectedMetadata.value.start,
        progress: payload.progress,
        predecessors: payload.predecessors,
      }

      persistCard(selectedTask.value.id, metadata, payload.dueDate)
    }

    const onDragStart = (index) => {
      dragIndex.value = index
    }

    const onDrop = async (index) => {
      if (dragIndex.value === null || dragIndex.value === index) return
      const updated = [...taskList.value]
      const [moved] = updated.splice(dragIndex.value, 1)
      updated.splice(index, 0, moved)
      taskList.value = updated
      dragIndex.value = null
      await rebuildGantt()
    }

    const toggleSettings = () => {
      settingsOpen.value = !settingsOpen.value
    }

    const onResize = () => {
      resizeTick.value += 1
    }

    const centerOnToday = () => {
      if (!gantt.value || !gantt.value.$svg) return
      const container = gantt.value.$svg.parentElement
      if (!container) return

      const now = new Date()
      const ganttStart = gantt.value.gantt_start
      const step = gantt.value.options.step || 24
      const columnWidth = gantt.value.options.column_width || 30

      if (!ganttStart) return

      const hours = (now.getTime() - ganttStart.getTime()) / 36e5
      const x = (hours / step) * columnWidth
      const target = x - container.clientWidth / 2
      container.scrollLeft = Math.max(0, target)
    }

    const extendForward = async (days, container) => {
      if (!gantt.value) return
      isExtending.value = true
      extraPaddingDays.value += days
      const prevScroll = container.scrollLeft
      skipCenterRef.value = true
      await rebuildGantt()
      container.scrollLeft = prevScroll
      isExtending.value = false
    }

    watch(
      settings,
      async () => {
        persistSettings()
        await rebuildGantt()
      },
      { deep: true }
    )

    loadSettings()
    loadCards()

    onMounted(() => {
      window.addEventListener('resize', onResize)
    })

    onBeforeUnmount(() => {
      window.removeEventListener('resize', onResize)
    })

    return {
      boardId,
      stackId,
      headerRef,
      settingsRef,
      ganttContainer,
      mainStyle,
      modalOpen,
      selectedTask,
      selectedMetadata,
      selectedDueDate,
      taskList,
      settingsOpen,
      settings,
      loadCards,
      closeModal,
      saveModal,
      deleteTask,
      createTask,
      openTaskFromList,
      onDragStart,
      onDrop,
      toggleSettings,
    }
  },
}
</script>

<style>
:root {
  --color-primary: #2a6ef7;
  --color-main-background: #f5f6fb;
  --color-main-text: #1d2d44;
  --gantt-bar-height: 20px;
  --gantt-padding: 18px;
  --gantt-header-height: 50px;
  --gantt-align-offset: 16px;
  --sidebar-width: 240px;
}

.app {
  min-height: 100vh;
  background: var(--color-main-background);
  color: var(--color-main-text);
  padding: 24px;
  box-sizing: border-box;
  font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
  overflow-x: hidden;
}

.app__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
}

.app__header h1 {
  margin: 0;
  font-size: 24px;
}

.app__header p {
  margin: 6px 0 0;
  opacity: 0.7;
}

.app__config {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.app__config label {
  display: flex;
  flex-direction: column;
  font-size: 12px;
  gap: 6px;
}

.app__config input {
  padding: 6px 8px;
  border-radius: 6px;
  border: 1px solid #d1d1d1;
}

.app__config button {
  padding: 8px 16px;
  border-radius: 6px;
  border: none;
  background: var(--color-primary);
  color: #fff;
  cursor: pointer;
}

.settings {
  background: #fff;
  border-radius: 16px;
  padding: 16px;
  margin-bottom: 20px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.settings h3 {
  margin: 0 0 12px;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(29, 45, 68, 0.6);
}

.settings__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.settings label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 12px;
}

.settings select,
.settings input[type="number"] {
  padding: 8px 10px;
  border-radius: 8px;
  border: 1px solid rgba(42, 110, 247, 0.2);
  background: #f9fbff;
}

.settings__toggle {
  flex-direction: row;
  align-items: center;
  gap: 8px;
}

.app__main {
  display: grid;
  grid-template-columns: var(--sidebar-width) 1fr;
  gap: 20px;
  align-items: stretch;
}

.task-list {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  height: 100%;
  display: flex;
  flex-direction: column;
}

.task-list__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: var(--gantt-header-height);
}

.task-list h3 {
  margin: 0;
  display: flex;
  align-items: center;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(29, 45, 68, 0.6);
}

.task-list__add {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid rgba(42, 110, 247, 0.3);
  background: #fff;
  color: var(--color-primary);
  display: grid;
  place-items: center;
  cursor: pointer;
}

.task-list__spacer {
  height: calc(var(--gantt-padding) + var(--gantt-align-offset));
}

.task-list__item {
  display: flex;
  align-items: center;
  height: calc(var(--gantt-bar-height) + var(--gantt-padding));
  cursor: grab;
}

.task-list__item:active {
  cursor: grabbing;
}

.task-list__card {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 10px;
  height: calc(var(--gantt-bar-height) + var(--gantt-padding));
  width: 100%;
  border-radius: 0;
  border: 1px solid rgba(42, 110, 247, 0.12);
  background: #f8faff;
  line-height: calc(var(--gantt-bar-height) + var(--gantt-padding));
}

.task-list__handle {
  font-size: 14px;
  opacity: 0.6;
}

.task-list__id {
  margin-left: auto;
  font-size: 11px;
  opacity: 0.6;
}

.task-list__settings {
  margin-top: auto;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  align-self: flex-start;
  padding: 6px 10px;
  border-radius: 8px;
  border: 1px solid rgba(42, 110, 247, 0.2);
  background: #f2f6ff;
  color: #204bb3;
  font-size: 12px;
  cursor: pointer;
}

.gantt {
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
  overflow: hidden;
  max-width: 100%;
  height: 100%;
}

.gantt .gantt-container {
  overflow-x: scroll;
  overflow-y: hidden;
  max-width: 100%;
  height: 100%;
  padding-bottom: 28px;
}

.gantt .gantt-container svg {
  width: max-content;
  min-width: 100%;
  display: block;
}

.gantt .bar {
  fill: var(--color-primary);
}

.gantt .bar-progress {
  fill: rgba(255, 255, 255, 0.6);
}

.gantt .grid-row {
  stroke: rgba(0, 0, 0, 0.05);
}

.gantt .grid-row.drop-target {
  fill: rgba(42, 110, 247, 0.12);
}

.gantt.gantt--no-deps .arrow,
.gantt.gantt--no-deps .arrow-head {
  display: none;
}

.gantt.gantt--no-progress .bar-progress {
  display: none;
}

.gantt.gantt--no-labels .bar-label {
  display: none;
}

.gantt.gantt--no-stripes .grid-row:nth-child(even) {
  fill: transparent;
}

.gantt.gantt--reduce-motion * {
  transition: none !important;
  animation: none !important;
}

.gantt .bar-wrapper.gantt-padding,
.gantt .bar-wrapper.gantt-padding .bar,
.gantt .bar-wrapper.gantt-padding .bar-progress,
.gantt .bar-wrapper.gantt-padding .bar-label,
.gantt .bar-wrapper.gantt-padding .handle-group {
  display: none !important;
}

.gantt .bar-wrapper.gantt-spacer,
.gantt .bar-wrapper.gantt-spacer .bar,
.gantt .bar-wrapper.gantt-spacer .bar-progress,
.gantt .bar-wrapper.gantt-spacer .bar-label,
.gantt .bar-wrapper.gantt-spacer .handle-group {
  display: none !important;
}
</style>

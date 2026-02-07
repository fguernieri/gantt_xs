const store = {
  boards: {
    1: {
      stacks: {
        1: {
          cards: [
            {
              id: 101,
              title: 'Planejar release',
              description: 'Checklist inicial.\n\n<!-- [gantt]: {"start":"2026-02-03","progress":30,"predecessors":[]} -->',
              type: 'plain',
              order: 1,
              duedate: '2026-02-10T00:00:00.000Z',
            },
            {
              id: 102,
              title: 'Implementar backend',
              description: 'Depende do planejamento.\n\n<!-- [gantt]: {"start":"2026-02-05","progress":10,"predecessors":["101"]} -->',
              type: 'plain',
              order: 2,
              duedate: '2026-02-14T00:00:00.000Z',
            },
            {
              id: 103,
              title: 'Revisar QA',
              description: 'Sem metadados ainda.',
              type: 'plain',
              order: 3,
              duedate: '2026-02-18T00:00:00.000Z',
            },
          ],
        },
      },
    },
  },
}

function clone(value) {
  return JSON.parse(JSON.stringify(value))
}

function ensureStack(boardId, stackId) {
  const board = store.boards[boardId]
  if (!board) return null
  const stack = board.stacks[stackId]
  if (!stack) return null
  return stack
}

export async function getStack(boardId, stackId) {
  const stack = ensureStack(Number(boardId), Number(stackId))
  if (!stack) {
    return { cards: [] }
  }
  return clone({ cards: stack.cards })
}

export async function updateCard(boardId, stackId, cardId, payload) {
  const stack = ensureStack(Number(boardId), Number(stackId))
  if (!stack) {
    throw new Error('Stack não encontrado')
  }

  const index = stack.cards.findIndex((card) => card.id === Number(cardId))
  if (index === -1) {
    throw new Error('Card não encontrado')
  }

  stack.cards[index] = {
    ...stack.cards[index],
    ...payload,
  }

  return clone(stack.cards[index])
}

export async function deleteCard(boardId, stackId, cardId) {
  const stack = ensureStack(Number(boardId), Number(stackId))
  if (!stack) {
    throw new Error('Stack não encontrado')
  }

  const index = stack.cards.findIndex((card) => card.id === Number(cardId))
  if (index === -1) {
    throw new Error('Card não encontrado')
  }

  const [removed] = stack.cards.splice(index, 1)
  return clone(removed)
}

export function resetMockData() {
  return clone(store)
}

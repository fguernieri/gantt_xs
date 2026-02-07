import { getStack, updateCard, deleteCard } from './deckMock'

export const deckClient = {
  async getStack(boardId, stackId) {
    return getStack(boardId, stackId)
  },
  async updateCard(boardId, stackId, cardId, payload) {
    return updateCard(boardId, stackId, cardId, payload)
  },
  async deleteCard(boardId, stackId, cardId) {
    return deleteCard(boardId, stackId, cardId)
  },
}

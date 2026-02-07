import axios from 'axios'

const API_BASE = '/apps/gantt_xs/api'

// Get CSRF token from DOM or window
const getRequestToken = () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content ||
                window.requesttoken ||
                ''
  return token
}

// Create axios instance with Nextcloud auth headers
const axiosInstance = axios.create({
  baseURL: API_BASE,
  withCredentials: true,
  headers: {
    'OCS-APIRequest': 'true',
    'Content-Type': 'application/json',
  }
})

// Add request interceptor to include CSRF token
axiosInstance.interceptors.request.use((config) => {
  const token = getRequestToken()
  if (token) {
    config.headers['requesttoken'] = token
  }
  return config
})

export const deckClient = {
  async getStacks() {
    try {
      const response = await axiosInstance.get('/stacks')
      return response.data
    } catch (error) {
      console.error('Error fetching stacks:', error)
      throw error
    }
  },

  async getStacksForBoard(boardId) {
    try {
      const response = await axiosInstance.get(`/boards/${boardId}/stacks`)
      return response.data
    } catch (error) {
      console.error('Error fetching stacks for board:', error)
      throw error
    }
  },

  async getStack(boardId, stackId) {
    try {
      const response = await axiosInstance.get(`/stacks/${boardId}/${stackId}`)
      return response.data
    } catch (error) {
      console.error('Error fetching stack:', error)
      throw error
    }
  },

  async getCard(boardId, stackId, cardId) {
    try {
      const response = await axiosInstance.get(`/cards/${boardId}/${stackId}/${cardId}`)
      return response.data
    } catch (error) {
      console.error('Error fetching card:', error)
      throw error
    }
  },

  async createCard(boardId, stackId, payload) {
    try {
      const response = await axiosInstance.post(`/stacks/${boardId}/${stackId}/cards`, payload)
      return response.data
    } catch (error) {
      console.error('Error creating card:', error)
      throw error
    }
  },

  async updateCard(boardId, stackId, cardId, payload) {
    try {
      const response = await axiosInstance.put(`/cards/${boardId}/${stackId}/${cardId}`, payload)
      return response.data
    } catch (error) {
      console.error('Error updating card:', error)
      throw error
    }
  },

  async deleteCard(boardId, stackId, cardId) {
    try {
      const response = await axiosInstance.delete(`/cards/${boardId}/${stackId}/${cardId}`)
      return response.data
    } catch (error) {
      console.error('Error deleting card:', error)
      throw error
    }
  },
}


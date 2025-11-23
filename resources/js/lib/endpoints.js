/**
 * API Endpoints Map
 * Centralized endpoint definitions for all API routes
 */
export default {
  global: {
    data: () => `/global-data`,
  },
  users: {
    show: (id) => `/users/${id}`,
    update: (id) => `/users/${id}`,
    gamification: (id) => `/users/${id}/gamification`,
  },
  xp: {
    logs: (userId) => `/xp/${userId}`,
    summary: (userId) => `/xp/${userId}/summary`,
  },
  levels: {
    list: () => `/levels`,
    show: (xp) => `/levels?xp=${xp}`,
    progress: (userId) => `/levels/${userId}/progress`,
  },
  badges: {
    list: () => `/badges`,
    user: (userId) => `/badges/user/${userId}`,
    award: () => `/badges/award`,
  },
  tasks: {
    daily: (userId) => `/tasks/daily/${userId}`,
    weekly: (userId) => `/tasks/weekly/${userId}`,
    complete: () => `/tasks/complete`,
    assign: () => `/tasks/assign`,
    autoComplete: () => `/tasks/auto-complete`,
  },
  questions: {
    list: () => `/questions`,
    show: (id) => `/questions/${id}`,
    search: () => `/questions/search`,
    create: () => `/questions`,
  },
  answers: {
    create: () => `/answers`,
    verify: () => `/answers/verify`,
    listForQuestion: (questionId) => `/answers/question/${questionId}`,
  },
}


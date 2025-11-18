/**
 * usePagination Composable
 * Reusable pagination logic for lists
 */
import { ref, computed } from 'vue'

export function usePagination(initialPerPage = 20) {
  const page = ref(1)
  const perPage = ref(initialPerPage)
  const total = ref(0)

  /**
   * Total number of pages
   */
  const totalPages = computed(() => {
    return Math.ceil(total.value / perPage.value) || 1
  })

  /**
   * Check if there's a next page
   */
  const hasNext = computed(() => {
    return page.value < totalPages.value
  })

  /**
   * Check if there's a previous page
   */
  const hasPrev = computed(() => {
    return page.value > 1
  })

  /**
   * Go to next page
   */
  const next = () => {
    if (hasNext.value) {
      page.value++
    }
  }

  /**
   * Go to previous page
   */
  const prev = () => {
    if (hasPrev.value) {
      page.value--
    }
  }

  /**
   * Set current page
   * @param {number} n - Page number
   */
  const setPage = (n) => {
    if (n >= 1 && n <= totalPages.value) {
      page.value = n
    }
  }

  /**
   * Reset pagination
   */
  const reset = () => {
    page.value = 1
    total.value = 0
  }

  /**
   * Update total items
   * @param {number} count - Total count
   */
  const setTotal = (count) => {
    total.value = count
  }

  /**
   * Get pagination metadata
   */
  const getMeta = () => {
    return {
      page: page.value,
      perPage: perPage.value,
      total: total.value,
      totalPages: totalPages.value,
      from: (page.value - 1) * perPage.value + 1,
      to: Math.min(page.value * perPage.value, total.value)
    }
  }

  return {
    page,
    perPage,
    total,
    totalPages,
    hasNext,
    hasPrev,
    next,
    prev,
    setPage,
    reset,
    setTotal,
    getMeta
  }
}


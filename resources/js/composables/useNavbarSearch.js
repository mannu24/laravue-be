import { ref } from 'vue'

// Global state for navbar search
const showSearch = ref(false)
const searchQuery = ref('')

export function useNavbarSearch() {
  const openSearch = (query = '') => {
    showSearch.value = true
    if (query) {
      searchQuery.value = query
    }
  }

  const closeSearch = () => {
    showSearch.value = false
    searchQuery.value = ''
  }

  return {
    showSearch,
    searchQuery,
    openSearch,
    closeSearch,
  }
}


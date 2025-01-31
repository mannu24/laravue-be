import { ref, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

export function useMentions() {
    const mentionSuggestions = ref([]);
    const mentionQuery = ref('');
    const isMentioning = ref(false);
    const authStore = useAuthStore();
    
    const searchMentions = async (query) => {
        if(query.length) {
            try {
                const response = await axios.get('/api/v1/posts/mention-suggestions?q='+query, authStore.config);
                mentionSuggestions.value = response.data;
            } catch (error) {
                console.error('Mention search failed:', error);
            }
        }
    };

    watch(mentionQuery, (newVal) => {
        if (newVal.length > 0) {
            searchMentions(newVal);
        } else {
            mentionSuggestions.value = [];
        }
    });

    return {
        mentionSuggestions,
        mentionQuery,
        isMentioning,
    };
}
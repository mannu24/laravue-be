import { ref } from 'vue'
import axios from 'axios'

export function useAiAnswer() {
    const aiAnswer = ref('')
    const isStreaming = ref(false)
    const error = ref(null)

    const streamAiAnswer = async (questionId) => {
        isStreaming.value = true
        aiAnswer.value = ''
        error.value = null

        try {
            const response = await fetch(`/api/v1/questions/${questionId}/ai-answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/event-stream',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })

            if (!response.ok) throw new Error('Failed to fetch AI answer')

            const reader = response.body.getReader()
            const decoder = new TextDecoder()

            while (true) {
                const { value, done } = await reader.read()
                if (done) break
                
                const chunk = decoder.decode(value, { stream: true })
                // Basic SSE parsing: data: {...}\n\n
                const lines = chunk.split('\n')
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        try {
                            const data = JSON.parse(line.slice(6))
                            if (data.text) {
                                aiAnswer.value += data.text
                            }
                        } catch (e) {
                            // Might be a partial JSON or non-JSON event
                        }
                    }
                }
            }
        } catch (err) {
            error.value = err.message
            console.error('AI Streaming Error:', err)
        } finally {
            isStreaming.value = false
        }
    }

    const validateAiAnswer = async (answerId, isHelpful) => {
        try {
            await axios.post(`/api/v1/ai-answers/${answerId}/validate`, {
                is_helpful: isHelpful
            })
        } catch (err) {
            console.error('Validation Error:', err)
        }
    }

    return {
        aiAnswer,
        isStreaming,
        error,
        streamAiAnswer,
        validateAiAnswer
    }
}

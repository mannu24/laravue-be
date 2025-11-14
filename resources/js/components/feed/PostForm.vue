<script setup>
import axios from 'axios';
import { ref, onMounted, nextTick } from "vue";
import CustomInput from "../elements/CustomInput.vue";
import { useMentions } from '@/composables/useMentions';
import { useAuthStore } from '../../stores/auth';
import { Button } from '@/components/ui/button';

const { mentionSuggestions, mentionQuery } = useMentions();
const mentionState = ref({
    active: false,
    position: { node: null, offset: 0 },
    query: '',
});
const newPost = ref({
    title: '',
    content: '',
    mentions: [],
    media: []
});

const emit = defineEmits(['fetch', 'close'])
const authStore = useAuthStore();
const deleted = ref([]);
const previews = ref([]);
const editor = ref(null);
const props = defineProps(['post']);
const expanded = ref(false);
const isSubmitting = ref(false);
const errors = ref({});

const expandCard = (val) => {
    expanded.value = val;
    if (!val) {
        resetForm();
    }
};

const handleInput = () => {
    newPost.value.content = editor.value.innerHTML;
    detectMentionTrigger();
};

const handleKeyDown = (e) => {
    if (e.key === 'Backspace') {
        const sel = window.getSelection();
        if (!sel.rangeCount) return;

        const range = sel.getRangeAt(0);
        const mentionNode = getMentionBeforeCursor(range);

        if (mentionNode) {
            e.preventDefault();
            removeMention(mentionNode);
            return;
        }
    }
    if (mentionState.value.active) {
        if (['Escape', 'ArrowUp', 'ArrowDown', 'Enter'].includes(e.key)) {
            e.preventDefault();
        }
    }
};

const getMentionBeforeCursor = (range) => {
    if (!range.collapsed) return null;

    let node = range.startContainer;
    let offset = range.startOffset;

    if (node.nodeType === Node.TEXT_NODE) {
        if (offset === 0) {
            node = node.previousSibling;
        } else {
            return null;
        }
    }
    else if (node.nodeType === Node.ELEMENT_NODE) {
        if (offset > 0) {
            node = node.childNodes[offset - 1];
        } else {
            node = node.previousSibling;
        }
    }

    while (node) {
        if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('mention')) {
            return node;
        }
        if (node.nodeType === Node.TEXT_NODE && node.textContent === '\u00A0') {
            node = node.previousSibling;
        } else {
            break;
        }
    }

    return null;
};

const removeMention = (mentionNode) => {
    const userId = parseInt(mentionNode.dataset.userId);

    const parent = mentionNode.parentNode;
    parent.removeChild(mentionNode);
    console.log(parent.innerHTML);

    newPost.value.mentions = newPost.value.mentions.filter(m => m.id !== userId);
    newPost.value.content = parent.innerHTML;

    const event = new Event('input', { bubbles: true });
    editor.value.dispatchEvent(event);
};

const detectMentionTrigger = () => {
    const selection = window.getSelection();
    if (!selection.rangeCount) return;

    const range = selection.getRangeAt(0);
    const textBeforeCursor = getTextBeforeCursor(range);
    const atIndex = textBeforeCursor.lastIndexOf('@');

    if (atIndex > -1) {
        const precedingChar = textBeforeCursor[atIndex - 1];
        const validTrigger = !precedingChar || /\s/.test(precedingChar);

        if (validTrigger) {
            mentionState.value = {
                active: true,
                position: {
                    node: range.startContainer,
                    offset: atIndex
                },
                query: textBeforeCursor.slice(atIndex + 1)
            };
            mentionQuery.value = mentionState.value.query;
            return;
        }
    }
    mentionState.value.active = false;
};

const getTextBeforeCursor = (range) => {
    let text = '';
    let node = range.startContainer;
    const offset = range.startOffset;

    if (node.nodeType === Node.TEXT_NODE) {
        text = node.textContent.slice(0, offset);
    }
    return text;
};

const insertMention = (user) => {
    if (!mentionState.value.active) return;

    const range = new Range();
    const startNode = mentionState.value.position.node;
    const startOffset = mentionState.value.position.offset;

    range.setStart(startNode, startOffset);
    range.setEnd(window.getSelection().getRangeAt(0).endContainer, window.getSelection().getRangeAt(0).endOffset);

    range.deleteContents();

    const mentionEl = document.createElement('span');
    mentionEl.className = 'mention-link';
    mentionEl.contentEditable = 'false';
    mentionEl.dataset.userId = user.id;
    mentionEl.textContent = `@${user.username}`;

    const space = document.createTextNode('\u00A0');
    const wrapper = document.createDocumentFragment();
    wrapper.appendChild(mentionEl);
    wrapper.appendChild(space);

    range.insertNode(wrapper);

    const newRange = new Range();
    newRange.setStartAfter(space);
    newRange.collapse(true);

    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(newRange);

    if (!newPost.value.mentions.some(m => m.id === user.id)) {
        newPost.value.mentions.push({
            id: user.id,
            username: user.username
        });
    }

    mentionState.value = { active: false, position: { node: null, offset: 0 }, query: '' };
    mentionSuggestions.value = [];

    const event = new Event('input', { bubbles: true });
    editor.value.dispatchEvent(event);
    editor.value.focus();
};

const resetForm = () => {
    newPost.value = {
        title: '',
        content: '',
        mentions: [],
        media: []
    };
    expanded.value = false
    editor.value.innerHTML = '';
    previews.value = [];
};

const handle_media = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        const exists = newPost.value.media.some(
            (existingFile) => existingFile.name === file.name && existingFile.size === file.size
        );

        if (!exists) {
            newPost.value.media.push(file);
            const url = URL.createObjectURL(file);
            previews.value.push({
                type: 'og',
                url,
            });
        }
    });
    event.target.value = "";
};

const submitPost = async () => {
    try {
        isSubmitting.value = true
        const formData = new FormData();
        formData.append("title", newPost.value.title);
        formData.append("content", newPost.value.content);

        newPost.value.mentions.forEach((obj, index) => {
            formData.append(`mentions[${index}][id]`, obj.id);
            formData.append(`mentions[${index}][username]`, obj.username);
        });

        newPost.value.media.forEach((file, index) => {
            formData.append(`media[${index}]`, file);
        });

        deleted.value.forEach(e => {
            formData.append('deleted[]',e)
        })

        if(props.post) {
            formData.append("_method", "PUT");
            await axios.post("/api/v1/posts/"+props.post.post_code, formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: `Bearer ${authStore.token}`
                },
            }).then((response) => {
                isSubmitting.value = false
                if (response.data.status == 'success') {
                    resetForm();
                    emit('fetch')
                }
                else {
                    console.error("Error creating post:", response.data.message);
                }
            }).catch((err) => {
                isSubmitting.value = false
            });
        }
        else {
            await axios.post("/api/v1/posts", formData, {
                headers: {
                    "Content-Type": "multipart/form-data",
                    Authorization: `Bearer ${authStore.token}`
                },
            }).then((response) => {
                isSubmitting.value = false
                if (response.data.status == 'success') {
                    resetForm();
                    emit('fetch')
                }
                else {
                    console.error("Error creating post:", response.data.message);
                }
            }).catch((err) => {
                isSubmitting.value = false
            });
        }
    } catch (error) {
        console.error("Error creating post:", error.response?.data || error.message);
    }
};

const removeimage = (id,type) => {
    if(type!='url'){
        // var temp_id = newPost.value.media.findIndex(i => i.id == id)
        // var prev_id = previews.value.findIndex(i => i.id == id)
        // newPost.value.media.splice(temp_id, 1)
        previews.value.splice(id, 1)
    }
    else {
        deleted.value.push(id)
        previews.value.splice(id, 1)
    }
};

const closeModal = () => {
    expanded.value = false
    emit('close');
};

onMounted(() => {
    if(props.post) {
        expandCard(true)
        newPost.value.post_code = props.post.post_code;
        newPost.value.content = props.post.content;
        newPost.value.title = props.post.title;
        previews.value = props.post.media_urls.map(url => {return {type:'url', url: url}});
        nextTick(() => {
            editor.value.innerHTML = props.post.content;
        });
    }
});
</script>
<template>
    <div class="max-w-2xl mx-auto sm:px-6" :class="props.post ? 'fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm' : ''">
        <div class="rounded-2xl shadow-xl dark:shadow-gray-900/50 transition-all duration-300 ease-in-out"
            :class="[
                props.post ? 'bg-white dark:bg-gray-800 p-6 max-w-2xl border border-gray-200 dark:border-gray-700' : '',
                !props.post && expanded ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 shadow-lg' : '',
                !props.post && !expanded ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 shadow-md hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 transition-all' : ''
            ]">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0">
                    <img v-if="authStore.user?.profile_photo" :src="authStore.user?.profile_photo" alt="User Avatar"
                        class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                    <img v-else src="/assets/front/images/user.png" alt="User Avatar" 
                        class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                </div>
                <Transition name="fade" mode="out-in">
                    <div v-if="!expanded" 
                        class="flex-1 rounded-xl h-12 px-4 bg-gray-100 dark:bg-gray-700 flex items-center cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors border border-transparent hover:border-gray-300 dark:hover:border-gray-500"
                        @click="expandCard(true)">
                        <p class="text-gray-500 dark:text-gray-300 text-sm">What's on your mind, {{ authStore.user?.name || authStore.user?.username }}?</p>
                    </div>
                    <div v-else class="flex-1 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white text-base">{{ authStore.user?.name || authStore.user?.username }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $filters.date(new Date()) }}</p>
                        </div>
                        <button v-if="!props.post" 
                            @click.stop="closeModal"
                            class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Form Content -->
            <form v-if="expanded" class="space-y-4" @submit.prevent="submitPost">
                <CustomInput 
                    type="text" 
                    placeholder="Title of this post" 
                    v-model="newPost.title" 
                    :error="errors.title"
                    class="dark:bg-gray-900/50 dark:border-gray-600 dark:text-white"
                />
                
                <div class="relative">
                    <div 
                        ref="editor" 
                        contenteditable="true" 
                        @input="handleInput" 
                        @keydown="handleKeyDown" 
                        data-placeholder="Share your thoughts..."
                        class="w-full min-h-[120px] text-sm border rounded-xl px-4 py-3 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 focus:border-green-500 dark:focus:border-green-500"
                    ></div>
                    
                    <!-- Mention Suggestions -->
                    <Transition name="fade">
                        <div v-if="mentionSuggestions.length" 
                            class="absolute bottom-full left-0 mb-2 w-full max-h-60 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl dark:shadow-gray-900/50 overflow-y-auto z-50">
                            <div v-for="user,i in mentionSuggestions" :key="i" 
                                @mousedown.prevent="insertMention(user)"
                                class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                                <img v-if="user.avatar" :src="user.avatar" class="w-10 h-10 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                <i v-else class="fas fa-user-circle text-2xl text-gray-400 dark:text-gray-500"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ user.username }}</p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Media Previews -->
                <div v-if="previews.length" class="flex overflow-x-auto gap-3 pb-2 scrollbar-hide">
                    <TransitionGroup name="bounce" appear>
                        <div v-for="(file, index) in previews" :key="index" 
                            class="relative h-32 w-32 flex-shrink-0 group">
                            <button 
                                @click="removeimage(index, file.type)" 
                                class="absolute -top-2 -right-2 w-6 h-6 flex items-center justify-center bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-full transition-all duration-200 hover:scale-110 z-10 shadow-lg">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                            <img :src="file.url" 
                                class="h-full w-full rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 ring-1 ring-gray-100 dark:ring-gray-800" 
                                alt="Image Preview" />
                        </div>
                    </TransitionGroup>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                    <input type="file" @change="handle_media" accept="image/*" class="hidden" id="media_upload" multiple>
                    <label for="media_upload" 
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 cursor-pointer transition-colors font-medium text-sm">
                        <i class="fas fa-paperclip"></i>
                        <span>Attach Media</span>
                    </label>
                    <Button 
                        type="submit" 
                        class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 dark:from-green-500 dark:to-green-600 dark:hover:from-green-600 dark:hover:to-green-700 text-white transition-all duration-300 shadow-md hover:shadow-lg px-6" 
                        :disabled="isSubmitting">
                        <span v-if="!isSubmitting">Submit Post</span>
                        <span v-else class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Posting...
                        </span>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
<style scoped>
.mention-link {
    --tw-text-opacity: 1;
    color: rgb(42 97 70 / var(--tw-text-opacity, 1));
    padding-left: 0.25rem;
    padding-right: 0.25rem;
    --tw-bg-opacity: 1;
    background-color: rgb(209 235 223 / var(--tw-bg-opacity, 1));
    border-radius: 0.25rem;
}

.dark .mention-link {
    color: rgb(134 239 172 / var(--tw-text-opacity, 1));
    background-color: rgb(20 83 45 / var(--tw-bg-opacity, 1));
}

[contenteditable][data-placeholder]:empty:before {
    content: attr(data-placeholder);
    color: rgb(156 163 175);
    pointer-events: none;
}

.dark [contenteditable][data-placeholder]:empty:before {
    color: rgb(107 114 128);
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
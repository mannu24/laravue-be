<script setup>
import axios from 'axios';
import { ref, onMounted, onUnmounted, nextTick, watch, computed } from "vue";
import { Teleport } from 'vue';
import CustomInput from "../elements/CustomInput.vue";
import { useMentions } from '@/composables/useMentions';
import { useAuthStore } from '../../stores/auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useThemeStore } from '../../stores/theme';

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
const themeStore = useThemeStore();
const deleted = ref([]);
const previews = ref([]);
const editor = ref(null);
const props = defineProps(['post']);
const expanded = ref(false);
const isSubmitting = ref(false);
const errors = ref({});

// Character count for content
const contentLength = computed(() => {
    if (!editor.value) return 0;
    const text = editor.value.innerText || editor.value.textContent || '';
    return text.length;
});

const maxContentLength = 5000;

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
    expanded.value = false;
    errors.value = {};
    deleted.value = [];

    // Revoke all object URLs to free memory
    previews.value.forEach(preview => {
        if (preview.url && preview.url.startsWith('blob:')) {
            URL.revokeObjectURL(preview.url);
        }
    });

    previews.value = [];

    if (editor.value) {
        editor.value.innerHTML = '';
    }
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
                id: Date.now() + Math.random()
            });
        }
    });
    event.target.value = "";
};

const errorFallbackImage = (event) => {
    event.target.src = '/placeholder.svg';
};

const validateForm = () => {
    errors.value = {};
    let isValid = true;

    if (!newPost.value.title || newPost.value.title.trim().length === 0) {
        errors.value.title = 'Title is required';
        isValid = false;
    } else if (newPost.value.title.trim().length > 200) {
        errors.value.title = 'Title must be less than 200 characters';
        isValid = false;
    }

    if (!editor.value || !editor.value.innerText || editor.value.innerText.trim().length === 0) {
        errors.value.content = 'Content is required';
        isValid = false;
    } else if (contentLength.value > maxContentLength) {
        errors.value.content = `Content must be less than ${maxContentLength} characters`;
        isValid = false;
    }

    return isValid;
};

const submitPost = async () => {
    if (!validateForm()) {
        return;
    }

    try {
        isSubmitting.value = true;
        errors.value = {};

        const formData = new FormData();
        formData.append("title", newPost.value.title.trim());
        formData.append("content", newPost.value.content);

        newPost.value.mentions.forEach((obj, index) => {
            formData.append(`mentions[${index}][id]`, obj.id);
            formData.append(`mentions[${index}][username]`, obj.username);
        });

        newPost.value.media.forEach((file, index) => {
            formData.append(`media[${index}]`, file);
        });

        deleted.value.forEach(url => {
            formData.append('deleted[]', url);
        });

        const endpoint = props.post
            ? `/api/v1/posts/${props.post.post_code}`
            : '/api/v1/posts';

        if (props.post) {
            formData.append("_method", "PUT");
        }

        const response = await axios.post(endpoint, formData, {
            headers: {
                "Content-Type": "multipart/form-data",
                Authorization: `Bearer ${authStore.token}`
            },
        });

        if (response.data.status === 'success') {
            resetForm();
            closeModal();
            emit('fetch');
        } else {
            errors.value.submit = response.data.message || 'An error occurred. Please try again.';
        }
    } catch (error) {
        console.error("Error submitting post:", error.response?.data || error.message);
        if (error.response?.data?.errors) {
            errors.value = { ...errors.value, ...error.response.data.errors };
        } else {
            errors.value.submit = error.response?.data?.message || 'An error occurred. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const removeimage = (index, type) => {
    if (type !== 'url') {
        // Remove new file from media array
        const previewToRemove = previews.value[index];
        // Find the file by matching the preview URL (we need to track which file created which preview)
        // Since we create the URL when adding the file, we can match by finding the file that would create the same preview
        // For simplicity, we'll remove by index correlation (assuming order is maintained)
        if (index < newPost.value.media.length) {
            newPost.value.media.splice(index, 1);
        }
        // Revoke object URL to free memory
        if (previewToRemove.url && previewToRemove.url.startsWith('blob:')) {
            URL.revokeObjectURL(previewToRemove.url);
        }
        previews.value.splice(index, 1);
    } else {
        // Mark existing media URL for deletion
        const urlToDelete = previews.value[index].url;
        deleted.value.push(urlToDelete);
        previews.value.splice(index, 1);
    }
};

const closeModal = () => {
    if (isSubmitting.value) return; // Prevent closing while submitting
    expanded.value = false;
    resetForm();
    emit('close');
};

// Handle ESC key to close modal
const handleEscKey = (e) => {
    if (e.key === 'Escape' && props.post && expanded.value) {
        closeModal();
    }
};

// Handle click outside modal
const handleBackdropClick = (e) => {
    if (e.target === e.currentTarget && props.post) {
        closeModal();
    }
};

// Prevent body scroll when modal is open
watch(() => props.post && expanded.value, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
        document.addEventListener('keydown', handleEscKey);
    } else {
        document.body.style.overflow = '';
        document.removeEventListener('keydown', handleEscKey);
    }
}, { immediate: true });

onMounted(() => {
    if (props.post) {
        expandCard(true);
        newPost.value.post_code = props.post.post_code;
        newPost.value.content = props.post.content;
        newPost.value.title = props.post.title;
        previews.value = props.post.media_urls.map((url, index) => ({
            type: 'url',
            url: url,
            id: index
        }));
        nextTick(() => {
            if (editor.value) {
                editor.value.innerHTML = props.post.content;
            }
        });
    }
});

onUnmounted(() => {
    // Cleanup: revoke all object URLs
    previews.value.forEach(preview => {
        if (preview.url && preview.url.startsWith('blob:')) {
            URL.revokeObjectURL(preview.url);
        }
    });
    document.body.style.overflow = '';
    document.removeEventListener('keydown', handleEscKey);
});
</script>
<template>
    <Teleport to="body" v-if="props.post">
        <Transition name="modal" appear>
            <div v-if="props.post && expanded"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                @click="handleBackdropClick" role="dialog" aria-modal="true" aria-labelledby="edit-post-title">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl dark:shadow-gray-900/50 max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800"
                    @click.stop>
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-shrink-0">
                                <img v-if="authStore.user?.profile_photo" :src="authStore.user?.profile_photo"
                                    alt="User Avatar"
                                    class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                                <img v-else src="/assets/front/images/user.png" alt="User Avatar"
                                    class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                            </div>
                            <div class="flex-1 flex items-center justify-between">
                                <div>
                                    <h3 id="edit-post-title"
                                        class="font-semibold text-gray-900 dark:text-white text-base">{{
                                            authStore.user?.name || authStore.user?.username }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Edit Post</p>
                                </div>
                                <button @click.stop="closeModal" :disabled="isSubmitting"
                                    class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Form Content -->
                        <form class="space-y-4" @submit.prevent="submitPost">
                            <CustomInput type="text" placeholder="Title of this post" v-model="newPost.title"
                                :error="errors.title"
                                class="!bg-white dark:!bg-gray-700/50 !border-gray-300 dark:!border-gray-600 !text-gray-900 dark:!text-gray-100 placeholder:!text-gray-400 dark:placeholder:!text-gray-400 focus:!border-green-500 dark:focus:!border-green-500 hover:!border-gray-400 dark:hover:!border-gray-500" />

                            <div class="relative">
                                <div ref="editor" contenteditable="true" @input="handleInput" @keydown="handleKeyDown"
                                    data-placeholder="Share your thoughts..."
                                    class="w-full min-h-[120px] text-sm border rounded-xl px-4 py-3 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500/50 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 focus:border-green-500 dark:focus:border-green-500"
                                    :class="{ 'border-red-500 dark:border-red-500': errors.content }"></div>

                                <!-- Character Count -->
                                <div class="flex items-center justify-between mt-1">
                                    <span v-if="errors.content" class="text-xs text-red-600 dark:text-red-400 ml-1">{{
                                        errors.content }}</span>
                                    <span v-else class="text-xs text-gray-500 dark:text-gray-400 ml-1"></span>
                                    <span :class="[
                                        'text-xs ml-auto',
                                        contentLength > maxContentLength
                                            ? 'text-red-600 dark:text-red-400'
                                            : 'text-gray-500 dark:text-gray-400'
                                    ]">
                                        {{ contentLength }} / {{ maxContentLength }}
                                    </span>
                                </div>

                                <!-- Mention Suggestions -->
                                <Transition name="fade">
                                    <div v-if="mentionSuggestions.length"
                                        class="absolute bottom-full left-0 mb-2 w-full max-h-60 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl dark:shadow-gray-900/50 overflow-y-auto z-50">
                                        <div v-for="user, i in mentionSuggestions" :key="i"
                                            @mousedown.prevent="insertMention(user)"
                                            class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                                            <img v-if="user.avatar" :src="user.avatar"
                                                class="w-10 h-10 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                            <i v-else
                                                class="fas fa-user-circle text-2xl text-gray-400 dark:text-gray-500"></i>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{
                                                    user.name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{
                                                    user.username }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Error Message -->
                            <div v-if="errors.submit"
                                class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <p class="text-sm text-red-600 dark:text-red-400">{{ errors.submit }}</p>
                            </div>

                            <!-- Media Previews -->
                            <div v-if="previews.length" class="flex flex-wrap gap-3 pb-2">
                                <TransitionGroup name="bounce" appear>
                                    <div v-for="(file, index) in previews" :key="file.id || index"
                                        class="relative h-32 w-32 flex-shrink-0 group">
                                        <button @click="removeimage(index, file.type)" :disabled="isSubmitting"
                                            class="absolute -top-2 -right-2 w-6 h-6 flex items-center justify-center bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-full transition-all duration-200 hover:scale-110 z-10 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                        <img :src="file.url"
                                            class="h-full w-full rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 ring-1 ring-gray-100 dark:ring-gray-800"
                                            alt="Image Preview" @error="errorFallbackImage" />
                                        <div v-if="file.type === 'url'"
                                            class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs px-2 py-1 rounded-b-xl">
                                            Existing
                                        </div>
                                    </div>
                                </TransitionGroup>
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                <input type="file" @change="handle_media" accept="image/*" class="hidden"
                                    id="media_upload_edit" multiple :disabled="isSubmitting">
                                <label for="media_upload_edit" :class="[
                                    'flex items-center gap-2 px-4 py-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 cursor-pointer transition-colors font-medium text-sm',
                                    isSubmitting ? 'opacity-50 cursor-not-allowed' : ''
                                ]">
                                    <i class="fas fa-paperclip"></i>
                                    <span>Attach Media</span>
                                </label>
                                <Button type="submit"
                                    class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 dark:from-green-500 dark:to-green-600 dark:hover:from-green-600 dark:hover:to-green-700 text-white transition-all duration-300 shadow-md hover:shadow-lg px-6"
                                    :disabled="isSubmitting || contentLength > maxContentLength">
                                    <span v-if="!isSubmitting">Update Post</span>
                                    <span v-else class="flex items-center gap-2">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Updating...
                                    </span>
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <div v-else class="max-w-2xl mx-auto sm:px-6">
        <Card class="overflow-hidden transition-all duration-300" :class="[
            expanded
                ? 'shadow-lg border-gray-200 dark:border-gray-800'
                : 'shadow-sm hover:shadow-md border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700'
        ]">
            <div class="relative overflow-hidden" :class="[
                expanded
                    ? 'bg-gradient-to-r from-green-50 via-green-50/25 to-white dark:from-vue/15 dark:via-none dark:to-black'
                    : ''
            ]">
                <CardContent class="p-0">
                    <!-- Header -->
                    <div class="px-6 py-4" :class="[
                        expanded
                            ? 'border-b border-gray-200 dark:border-gray-700'
                            : ''
                    ]">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <img v-if="authStore.user?.profile_photo" :src="authStore.user?.profile_photo"
                                    alt="User Avatar"
                                    class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                                <img v-else src="/assets/front/images/user.png" alt="User Avatar"
                                    class="w-12 h-12 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700" />
                            </div>
                            <Transition name="fade" mode="out-in">
                                <div v-if="!expanded"
                                    class="flex-1 rounded-lg h-12 px-4 bg-gray-100 dark:bg-gray-800/50 flex items-center cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors border border-transparent hover:border-gray-300 dark:hover:border-gray-900"
                                    @click="expandCard(true)">
                                    <p class="text-gray-500 dark:text-gray-300 text-sm">What's on your mind, {{
                                        authStore.user?.name || authStore.user?.username }}?</p>
                                </div>
                                <div v-else class="flex-1 flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-base">{{
                                            authStore.user?.name || authStore.user?.username }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $filters.date(new
                                            Date()) }}</p>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Form Content -->
                    <form v-if="expanded" class="px-6 py-4 space-y-4" @submit.prevent="submitPost">
                        <CustomInput type="text" placeholder="Title of this post" v-model="newPost.title"
                            :error="errors.title"
                            class="!bg-white dark:!bg-gray-700/50 !border-gray-300 dark:!border-gray-600 !text-gray-900 dark:!text-gray-100 placeholder:!text-gray-400 dark:placeholder:!text-gray-400 focus:!border-green-500 dark:focus:!border-green-500 hover:!border-gray-400 dark:hover:!border-gray-500 rounded-md" />

                        <div class="relative">
                            <div ref="editor" contenteditable="true" @input="handleInput" @keydown="handleKeyDown"
                                data-placeholder="Share your thoughts..."
                                class="w-full min-h-[120px] text-sm border rounded-md px-4 py-3 transition-all duration-200 focus:outline-none  bg-white dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 focus:border-green-500 dark:focus:border-green-500"
                                :class="{ 'border-red-500 dark:border-red-500': errors.content }"></div>

                            <!-- Character Count -->
                            <div class="flex items-center justify-between mt-1">
                                <span v-if="errors.content" class="text-xs text-red-600 dark:text-red-400 ml-1">{{
                                    errors.content }}</span>
                                <span v-else class="text-xs text-gray-500 dark:text-gray-400 ml-1"></span>
                                <span :class="[
                                    'text-xs ml-auto',
                                    contentLength > maxContentLength
                                        ? 'text-red-600 dark:text-red-400'
                                        : 'text-gray-500 dark:text-gray-400'
                                ]">
                                    {{ contentLength }} / {{ maxContentLength }}
                                </span>
                            </div>

                            <!-- Mention Suggestions -->
                            <Transition name="fade">
                                <div v-if="mentionSuggestions.length"
                                    class="absolute bottom-full left-0 mb-2 w-full max-h-60 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl dark:shadow-gray-900/50 overflow-y-auto z-50">
                                    <div v-for="user, i in mentionSuggestions" :key="i"
                                        @mousedown.prevent="insertMention(user)"
                                        class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 cursor-pointer flex items-center gap-3 transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-0">
                                        <img v-if="user.avatar" :src="user.avatar"
                                            class="w-10 h-10 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                                        <i v-else
                                            class="fas fa-user-circle text-2xl text-gray-400 dark:text-gray-500"></i>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{
                                                user.name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{
                                                user.username }}</p>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errors.submit"
                            class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">{{ errors.submit }}</p>
                        </div>

                        <!-- Media Previews -->
                        <div v-if="previews.length" class="flex flex-wrap gap-3 pb-2">
                            <TransitionGroup name="bounce" appear>
                                <div v-for="(file, index) in previews" :key="file.id || index"
                                    class="relative h-32 w-32 flex-shrink-0 group">
                                    <button @click="removeimage(index, file.type)" :disabled="isSubmitting"
                                        class="absolute -top-2 -right-2 w-6 h-6 flex items-center justify-center bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-full transition-all duration-200 hover:scale-110 z-10 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    <img :src="file.url"
                                        class="h-full w-full rounded-xl object-cover border-2 border-gray-200 dark:border-gray-700 ring-1 ring-gray-100 dark:ring-gray-800"
                                        alt="Image Preview" @error="errorFallbackImage" />
                                    <div v-if="file.type === 'url'"
                                        class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs px-2 py-1 rounded-b-xl">
                                        Existing
                                    </div>
                                </div>
                            </TransitionGroup>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-between gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <input type="file" @change="handle_media" accept="image/*" class="hidden" id="media_upload"
                                multiple :disabled="isSubmitting">
                            <label for="media_upload" :class="[
                                'flex items-center gap-2 px-4 py-2 rounded-lg text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 cursor-pointer transition-colors font-medium text-sm',
                                isSubmitting ? 'opacity-50 cursor-not-allowed' : ''
                            ]">
                                <i class="fas fa-paperclip"></i>
                                <span>Attach Media</span>
                            </label>
                            <Button variant="outline" size="lg" :class="[isSubmitting ? 'opacity-50 cursor-not-allowed' : '', 'ms-auto']" @click.stop="closeModal">
                                Cancel
                            </Button>
                            <Button type="submit"
                                class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 dark:from-green-500 dark:to-green-600 dark:hover:from-green-600 dark:hover:to-green-700 text-white transition-all duration-300 shadow-md hover:shadow-lg px-6"
                                :disabled="isSubmitting || contentLength > maxContentLength">
                                <span v-if="!isSubmitting">Submit Post</span>
                                <span v-else class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    Posting...
                                </span>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </div>
        </Card>
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

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-active .bg-white,
.modal-leave-active .bg-white {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from .bg-white,
.modal-leave-to .bg-white {
    transform: scale(0.95);
    opacity: 0;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Bounce transition for media */
.bounce-enter-active {
    transition: all 0.3s ease;
}

.bounce-leave-active {
    transition: all 0.3s ease;
}

.bounce-enter-from {
    opacity: 0;
    transform: scale(0.8);
}

.bounce-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
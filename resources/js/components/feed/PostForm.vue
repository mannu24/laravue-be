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
    <div :class="props.post ? 'fixed inset-0 z-50 flex items-center justify-center bg-black/60' : 'none'">
        <!-- v-click-outside="{ closeCondition: () => expanded, closeAction: () => expandCard(false) }" -->
        <div class="mx-auto w-full rounded-lg shadow-lg p-4 transition-all duration-300 ease-in-out"
            :class="{ 'max-w-xl h-auto': expanded, 'max-w-lg h-20': !expanded, 'bg-gray-200 hover:bg-gray-300/40 dark:bg-gray-900/80 shadow-md dark:hover:bg-gray-900' : !props.post, 'bg-gray-200 dark:bg-gray-900 shadow-md' : props.post }">
            <div class="flex items-center">
                <img v-if="authStore.user?.profile_photo" :src="authStore.user?.profile_photo" alt="User Avatar"
                    class="w-10 h-10 rounded-full mr-3" />
                <img v-else src="/assets/front/images/user.png" alt="User Avatar" class="w-10 h-10 rounded-full mr-3" />
                <Transition name="fade" mode="out-in">
                    <div class="w-full rounded-full h-10 px-4 bg-gray-300 dark:bg-gray-800/80 flex items-center cursor-pointer"
                        @click="expandCard(true)" v-if="!expanded">
                        <p class="text-gray-600 dark:text-gray-200">What's on your mind?</p>
                    </div>
                    <div class="w-full flex justify-between" v-else>
                        <div class="dark:text-white text-gray-800">
                            <h2 class="font-semibold mb-0">{{ authStore.user.username }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $filters.date(new Date()) }}</p>
                        </div>
                        <p :title="'Close'" class="icon-hover w-8 h-8 rounded-full flex icon-hover text-gray-100 dark:text-gray-400 bg-black/10 hover:bg-black/20 dark:bg-white/10 dark:hover:bg-white/20 dark:hover:text-white hover:text-black-900" @click.stop="closeModal">
                            <i class="fas fa-times m-auto fa-lg"></i>
                        </p>
                    </div>
                </Transition>
            </div>
            <form v-if="expanded" class="mt-7" @submit.prevent="submitPost">
                <CustomInput type="text" placeholder="Title of this post" v-model="newPost.title" :error="errors.title" />
                <div class="relative">
                    <div ref="editor" contenteditable="true" @input="handleInput" @keydown="handleKeyDown" placeholder="What's on your mind?"
                        class="w-full dark:text-white text-gray-800 placeholder:text-slate-400 min-h-[100px] text-sm border rounded-md px-3 py-2 transition duration-300 ease focus:outline-none shadow-sm bg-transparent text-slate-700 border-gray-300 hover:border-gray-400/50 focus:border-gray-400 dark:border-slate-700 dark:hover:border-slate-600 dark:focus:border-slate-500"
                    ></div>
                    <Transition name="fade">
                        <div v-if="mentionSuggestions.length" class="absolute bottom-full left-0 w-full max-h-60 bg-white border rounded-lg shadow-lg overflow-y-auto z-50">
                            <div v-for="user,i in mentionSuggestions" :key="i" @mousedown.prevent="insertMention(user)"
                                class="p-2 hover:bg-gray-100 cursor-pointer flex items-center gap-2">
                                <img v-if="user.avatar" :src="user.avatar" class="w-8 h-8 rounded-full object-cover">
                                <i v-else class="fas fa-user-circle fa-2x text-black/50"></i>
                                <div>
                                    <p class="font-medium text-sm">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500">@{{ user.username }}</p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
                <div class="media-previews flex overflow-x-auto space-x-4" :class="{'pt-5':previews.length}">
                    <TransitionGroup name="bounce" appear>
                        <div v-for="(file, index) in previews" :key="index" class="h-32 w-32 flex-shrink-0 relative mb-1">
                            <div @click="removeimage(index, file.type)" class="w-6 h-6 flex absolute -top-3 -right-3 bg-vue/80 cursor-pointer hover:bg-laravel/70 transition-all duration-300 rounded-full text-white">
                                <i class="fas fa-times m-auto fa-sm"></i>
                            </div>
                            <img :src="file.url" class="h-full w-full rounded object-cover" alt="Image Preview" />
                            <!-- <video v-if="typeof file == 'string'" class="h-32 w-32 rounded object-cover" controls>
                                <source :src="file" />
                                Your browser does not support the video tag.
                            </video>
                            <video v-else-if="file.type.startsWith('video/')" class="h-32 w-32 rounded object-cover" controls>
                                <source :src="file.url" :type="file.type" />
                                Your browser does not support the video tag.
                            </video> -->
                        </div>
                    </TransitionGroup>
                </div>
                <div class="mt-2 flex justify-around">
                    <input type="file" @change="handle_media" accept="image/*" class="hidden" id="media_upload" multiple>
                    <label for="media_upload" class="flex items-center gap-2 text-green-600/60 hover:text-green-600/80 cursor-pointer">
                        <i class="fas fa-paperclip"></i>
                        <span>Attach Media</span>
                    </label>
                    <Button type="submit" class="bg-vue/80 hover:bg-vue text-white transition-all duration-300" :disabled="isSubmitting">
                        Submit Post
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
<style>
.mention-link {
    --tw-text-opacity: 1;
    color: rgb(42 97 70 / var(--tw-text-opacity, 1));
    padding-left: 0.25rem;
    padding-right: 0.25rem;
    --tw-bg-opacity: 1;
    background-color: rgb(209 235 223 / var(--tw-bg-opacity, 1));
    border-radius: 0.25rem;
}
</style>
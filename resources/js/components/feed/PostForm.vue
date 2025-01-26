<script setup>
import { Form, useForm } from "vee-validate";
import { ref, defineEmits } from "vue";
import CustomInput from "../elements/CustomInput.vue";
import { useAuthStore } from "../../stores/auth";

const form = ref({
	title: "",
	content: "",
	media: [],
	is_ai_generated: false,
});

const emit = defineEmits(['fetch'])
const authStore = useAuthStore();
const previews = ref([]);
const expanded = ref(false);
const errors = ref({});
const { isSubmitting, validate } = useForm();
const expandCard = (val) => {
	expanded.value = val;
	if(!val) {
		form.value.reset()
	}
}
const submitPost = async () => {
	const isValid = await validate();
	if (!isValid) return;

	try {
        const formData = new FormData();
        formData.append("title", form.value.title);
        formData.append("content", form.value.content);

        // Append files to FormData
        form.value.media.forEach((file, index) => {
          formData.append(`media[${index}]`, file);
        });

        const response = await axios.post("/api/posts", formData, {
			headers: {
				"Content-Type": "multipart/form-data",
				Authorization: `Bearer ${authStore.token}`
			},
        });

		if(response.data.status == 'success') {
			form.value.title = "";
			form.value.content = "";
			form.value.media = [];
			previews.value = [];
			emit('fetch')
		}
		else {
			console.error("Error creating post:", response.data.message);
		}
	} catch (error) {
        console.error("Error creating post:", error.response?.data || error.message);
	}
};
const handle_media = (event) => {
	const files = Array.from(event.target.files); 
	files.forEach((file) => {
		const exists = form.value.media.some(
          (existingFile) => existingFile.name === file.name && existingFile.size === file.size
        );

        if (!exists) {
			form.value.media.push(file);
			const url = URL.createObjectURL(file);
			previews.value.push({
				type: file.type,
				url,
			});
		}
	});
	event.target.value = "";
}
</script>
<template>
	<div v-click-outside="{
	closeCondition: () => expanded,
	closeAction: () => expandCard(false),
  }"
	class="mx-auto w-full bg-white rounded-lg shadow-lg p-4 transition-all duration-300 ease-in-out"
	:class="{ 'max-w-xl h-auto': expanded, 'max-w-md h-20': !expanded }"
	>
		<div class="flex items-center">
			<img src="/assets/front/images/user.png" alt="Avatar" class="w-10 h-10 rounded-full mr-3" />
			<Transition name="fade">
				<div class="w-full bg-gray-200 rounded-full h-10 px-4 flex items-center cursor-pointer" @click="expandCard(true)" v-if="!expanded">
					<p class="text-gray-500">What's on your mind?</p>
				</div>
			</Transition>
		</div>
		<form v-if="expanded" class="mt-4" @submit.prevent="submitPost">
			<CustomInput type="text" placeholder="Title of this post" v-model="form.title"
				:error="errors.title" />
			<CustomInput type="textarea" required :rows="5" placeholder="What's on your mind?"
				v-model="form.content" :error="errors.content" />
			<div class="mt-4 flex justify-around">
				<input type="file" @change="handle_media" accept="image/*,video/mp4,video/*" class="hidden" id="media_upload" multiple>
				<label for="media_upload" class="flex items-center gap-2 text-green-600 hover:text-green-700 cursor-pointer">
					<i class="fas fa-paperclip"></i>
					<span>Attach Media</span>	
				</label>
				<button type="submit" class="bg-primary-400 text-white px-4 py-2 rounded transition duration-150 ease-in-out hover:bg-primary-500"
					:disabled="isSubmitting">
					Submit Post
				</button>
			</div>
			<div class="media-previews w-full flex flex-wrap justify-start gap-2 pt-4">
				<div v-for="(file, index) in previews" :key="index" class="mb-1">
					<img
					v-if="file.type.startsWith('image/')"
					:src="file.url"
					class="h-32 w-32 rounded object-cover"
					alt="Image Preview"
					/>
					<video
					v-if="file.type.startsWith('video/')"
					class="h-32 w-32 rounded object-cover"
					controls
					>
						<source :src="file.url" :type="file.type" />
						Your browser does not support the video tag.
					</video>
				</div>
			</div>
		</form>
	</div>
</template>
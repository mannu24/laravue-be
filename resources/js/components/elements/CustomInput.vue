<script setup>
import { ref, watch, computed, defineProps, defineEmits } from "vue";

const props = defineProps({
  helper: String,
  icon: String,
  type: String,
  modelValue: [String, Number, Boolean, Array],
  id: String,
  name: String,
  label: String,
  placeholder: String,
  error: String,
  options: Array,
  multiple: Boolean,
  rows: [String, Number],
  required: Boolean,
  disabled: Boolean,
  readonly: Boolean,
})
const emit = defineEmits(['update:modelValue'])

const innerValue = ref(props.modelValue || "");
const inputComponent = computed(() => {
    const typeMapping = {
    text: "input",
    email: "input",
    password: "input",
    number: "input",
    checkbox: "input",
    radio: "input",
    select: "select",
    textarea: "textarea",
    };
    return typeMapping[props.type] || "input";
});

const inputProps = computed(() => {
    return {
    type: props.type === "select" || props.type === "textarea" ? undefined : props.type,
    rows: props.type !== "textarea" ? undefined : props.rows,
    id: props.id,
    name: props.name,
    placeholder: props.placeholder+(props.required ? ' *' : ''),
    required: props.required,
    disabled: props.disabled,
    readonly: props.readonly,
    multiple: props.type === "select" ? props.multiple : undefined,
    };
});

const computedClass = computed(() => {
    const baseClass =
    "w-full placeholder:text-slate-400 text-white text-sm border rounded-md px-3 py-2 transition duration-300 ease focus:outline-none shadow-sm";

    if (["checkbox", "radio"].includes(props.type)) {
    return "appearance-none cursor-pointer w-5 h-5 border rounded focus:ring-2 focus:ring-slate-400";
    }

    if (props.type === "select") {
    return `${baseClass} cursor-pointer ${props.disabled
        ? "pointer-events-none bg-slate-200 text-slate-400 border-slate-200"
        : "bg-transparent text-slate-700 border-slate-200 hover:border-slate-300 focus:border-slate-400"
        } ${props.error ? "border-red-600 text-red-600 focus:border-red-600" : ""}`;
    }

    return `${baseClass} ${props.disabled
    ? "pointer-events-none bg-slate-200 text-slate-400 border-slate-200"
    : "bg-transparent text-slate-700 border-slate-200 hover:border-slate-300 focus:border-slate-400"
    } ${props.error ? "border-red-600 text-red-600 focus:border-red-600 hover:border-red-600" : ""}`;
});

watch(innerValue, (val) => {
    emit("update:modelValue", val);
});

watch(() => props.modelValue, (val) => {
    innerValue.value = val;
});
</script>
<template>
    <div class="w-full mb-3 min-w-[200px]">
        <label v-if="label" :for="id" class="block text-sm text-slate-600 font-medium mb-2">
            {{ label }}
        </label>
        <div :class="{ 'relative': icon }">
            <component :is="inputComponent" v-bind="inputProps" :class="computedClass" :value="innerValue" @input="innerValue = $event.target.value" @change="innerValue = $event.target.value">
                <template v-if="type === 'select'">
                    <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
                </template>
            </component>
            <i v-if="icon" style="--fa-primary-color: #41B883;--fa-secondary-color: #35495E; --fa-secondary-opacity:1;"
                class="fad absolute w-5 h-5 top-2.5 right-2.5 text-slate-600" :class="icon"></i>
        </div>
        <p class="flex mt-1 ml-1 items-center gap-2 text-xs text-slate-400" v-if="helper" v-html="helper"></p>
        <span v-if="error" class="flex items-start ml-1 mt-1 text-xs text-red-600">{{ error }}</span>
    </div>
</template>
<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    required: false,
  },
  placeholder: String,
  autoResize: {
    type: Boolean,
    default: true,
  }
});

const emit = defineEmits(['update:modelValue'])
const textareaRef = ref(null);

function autoResize() {
  if (textareaRef.value) {
    textareaRef.value.style.height = 'auto';
    textareaRef.value.style.height = (textareaRef.value.scrollHeight + 2) + 'px';
  }
}

onMounted(() => {
  if (textareaRef.value && textareaRef.value.hasAttribute('autofocus')) {
    textareaRef.value.focus();
  }
});

watch(() => props.modelValue, () => {
  setTimeout(() => {
    autoResize()
  }, 10)
})

defineExpose({ focus: () => textareaRef.value.focus() });

function onInputChange($event) {
  emit('update:modelValue', $event.target.value);
}

onMounted(() => {
  if (props.autoResize) {
    autoResize();
  }
})
</script>

<template>
    <textarea
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-sky-500 dark:focus:border-sky-600 focus:ring-sky-500 dark:focus:ring-sky-600 rounded-md shadow-sm resize-none"
        :value="props.modelValue"
        @input="onInputChange"
        ref="textareaRef"
        :placeholder="placeholder"
    ></textarea>
</template>

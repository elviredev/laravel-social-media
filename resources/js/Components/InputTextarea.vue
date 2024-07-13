<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
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
    textareaRef.value.style.height = textareaRef.value.scrollHeight + 'px';
  }
}

onMounted(() => {
  if (textareaRef.value && textareaRef.value.hasAttribute('autofocus')) {
    textareaRef.value.focus();
  }
});

defineExpose({ focus: () => textareaRef.value.focus() });

function onInputChange($event) {
  emit('update:modelValue', $event.target.value);
  // autoresize du textarea selon le besoin du texte saisi
  if (props.autoResize) {
    autoResize();
  }
}
</script>

<template>
    <textarea
        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm resize-none"
        :value="props.modelValue"
        @input="onInputChange"
        ref="textareaRef"
        :placeholder="placeholder"
    ></textarea>
</template>

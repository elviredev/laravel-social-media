<script setup>
  defineProps({
      selected: Boolean,
      text: String
  })
</script>

<template>
  <button
    :class="[
        'px-3 py-2.5 outline-none text-sm', selected
        ? 'bg-white text-sky-500 border-b-2 border-sky-500'
        : 'text-gray-700',
    ]"
  >
    {{ text }}
  </button>
</template>

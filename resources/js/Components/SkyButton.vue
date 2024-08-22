<script setup>
  import { computed } from "vue";

  const props = defineProps({
    type: {
      type: String,
      default: 'button'
    },
    outline: {
      type: Boolean,
      default: false
    }
  })

  const buttonClasses = computed(() => {
    return {
      'btn-not-outline': !props.outline, // classe appliquée si outline est false
      'btn-outline': props.outline, // classe appliquée si outline est true
    }
  })
</script>

<template>
  <button
    :type="type"
    :class="buttonClasses"
  >
    <slot></slot>
  </button>
</template>

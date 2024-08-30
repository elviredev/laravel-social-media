<script setup>
import { ArrowDownTrayIcon, PaperClipIcon } from '@heroicons/vue/24/outline'
import { isImage } from "@/helpers.js";

  defineProps({
    attachments: Array
  })

  defineEmits(['attachmentClick'])
</script>

<template>
  <template v-for="(attachment, index) of attachments.slice(0, 4)">
    <div
      @click="$emit('attachmentClick', index)"
      class="group aspect-square bg-sky-100 text-gray-500 flex flex-col items-center justify-center relative cursor-pointer"
    >
      <!-- Affiche nb files en + -->
      <div
        v-if="index === 3 && attachments.length > 4"
        class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white flex items-center justify-center text-xl">
        +{{ attachments.length - 4 }} more
      </div>
      <!-- Bouton Icon Download -->
      <a
        @click.stop
        :href="route('post.download', attachment)"
        class="z-20 opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center bg-gray-700 text-gray-100 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800"
      >
        <ArrowDownTrayIcon class="w-4 h-4"/>
      </a>

      <img
        v-if="isImage(attachment)"
        :src="attachment.url"
        alt=""
        class="object-contain aspect-square"
      />

      <div v-else class="flex flex-col justify-center items-center">
        <PaperClipIcon class="w-12 h-12 mb-3"/>
        <small>{{attachment.name}}</small>
      </div>
    </div>
  </template>
</template>

<script setup>
import { addHours, format } from 'date-fns'

defineProps({
    post: {
      type: Object,
    },
    showTime: {
      type: Boolean,
      default: true,
    }
  })

  // ajouter 2h à l'heure affichée pour être en heure locale
  function adjustTime(date) {
    const adjustHours =  addHours(new Date(date), 2)
    return format(adjustHours, 'y-MM-dd HH:mm:ss')
  }
</script>

<template>
  <div class="flex items-center gap-2">
    <a href="javascript:void(0)">
      <img
        :src="post.user.avatar_url"
        alt="avatar"
        class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500"
      >
    </a>
    <div>
      <h4 class="font-bold">
        <a href="javascript:void(0)" class="hover:underline">{{post.user.name}}</a>
        <template v-if="post.group">
          >
          <a href="javascript:void(0)" class="hover:underline">{{post.group.name}}</a>
        </template>
      </h4>
      <small v-if="showTime" class="text-gray-400">{{adjustTime(post.updated_at)}}</small>
    </div>
  </div>
</template>

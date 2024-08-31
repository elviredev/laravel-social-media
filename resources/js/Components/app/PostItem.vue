<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ChatBubbleLeftRightIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline'
import { router } from "@inertiajs/vue3";
import axiosClient from "@/axiosClient.js";
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import ReadMoreReadLess from "@/Components/app/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import PostAttachments from "@/Components/app/PostAttachments.vue";
import CommentList from "@/Components/app/CommentList.vue";

const props = defineProps({
    post: Object
})

const emit = defineEmits(['editClick', 'attachmentClick'])

function openEditModal() {
  emit('editClick', props.post)
}

function deletePost() {
  if (window.confirm('Are you sure you want to delete this post?')) {
    router.delete(route('post.destroy', props.post.id), {
      preserveScroll: true,
    })
  }
}

function openAttachment(index) {
  emit("attachmentClick", props.post, index)
}

function sendReaction() {
  axiosClient.post(route('post.reaction', props.post), {
    reaction: 'like'
  })
    .then(({data}) => {
      props.post.current_user_has_reaction = data.current_user_has_reaction
      props.post.num_of_reactions = data.num_of_reactions
    })
}

</script>

<template>
  <div class="bg-white border rounded p-4 mb-3">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <PostUserHeader :post="post" />
      <!-- Menu Dropdown -->
      <EditDeleteDropdown :user="post.user" @edit="openEditModal" @delete="deletePost" />
    </div>

    <!-- Contenu article -->
    <div class="mb-8">
      <ReadMoreReadLess :content="post.body" />
    </div>

    <!-- Pièces Jointes -->
    <div class="grid gap-3 mb-6" :class="[
      post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
    ]">
      <PostAttachments
        :attachments="post.attachments"
        @attachmentClick="openAttachment"
      />
    </div>

    <!-- Like & Comments -->
    <Disclosure v-slot="{ open }">
      <div class="flex gap-2">
        <!-- Like Button -->
        <button
          @click="sendReaction"
          class="flex gap-1 items-center justify-center py-2 px-4 text-gray-800 rounded-lg flex-1"
          :class="[
          post.current_user_has_reaction ?
          'bg-sky-100 hover:bg-sky-200' :
          'bg-gray-100 hover:bg-gray-200',
        ]"
        >
          <HandThumbUpIcon class="w-6 h-6" />
          <span class="mr-2">{{ post.num_of_reactions }}</span>
          {{ post.current_user_has_reaction ? 'Unlike' : 'Like' }}
        </button>
        <!-- Comments Button -->
        <DisclosureButton
          class="flex gap-1 items-center justify-center py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg flex-1"
        >
          <ChatBubbleLeftRightIcon class="w-6 h-6" />
          <span class="mr-2">{{ post.num_of_comments }}</span>
          Comment
        </DisclosureButton>
      </div>
      <!-- Comments Section -->
      <DisclosurePanel class="comment-list mt-3 h-72 overflow-auto">
        <CommentList :post="post" :data="{comments: post.comments}" />
      </DisclosurePanel>
    </Disclosure>

    </div>
</template>

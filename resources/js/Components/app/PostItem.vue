<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ArrowDownTrayIcon, PaperClipIcon, HandThumbUpIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { PencilIcon, TrashIcon, EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { isImage } from "@/helpers.js";
import axiosClient from "@/axiosClient.js";
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import InputTextarea from "@/Components/InputTextarea.vue";
import SkyButton from "@/Components/SkyButton.vue";
import { adjustTime } from "@/helpers.js";
import ReadMoreReadLess from "@/Components/ReadMoreReadLess.vue";

const authUser = usePage().props.auth.user;

const props = defineProps({
    post: Object
})

const newCommentText = ref('')

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

function createComment() {
  axiosClient.post(route('post.comment.create', props.post), {
    comment: newCommentText.value
  })
    .then(({data}) => {
      newCommentText.value = ''
      props.post.comments.unshift(data)
      props.post.num_of_comments++
    })
}

</script>

<template>
  <div class="bg-white border rounded p-4 mb-3">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <PostUserHeader :post="post" />
      <!-- Menu Dropdown -->
      <Menu as="div" class="relative z-10 inline-block text-left">
        <div>
          <MenuButton class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center">
            <EllipsisVerticalIcon
              class="w-5 h-5"
              aria-hidden="true"
            />
          </MenuButton>
          </div>
          <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
          >
            <MenuItems
              class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
            >
              <div class="px-1 py-1">
                <MenuItem v-slot="{ active }">
                  <button
                    @click="openEditModal"
                    :class="[active
                      ? 'bg-sky-500 text-white'
                      : 'text-gray-900',
                      'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]"
                  >
                    <PencilIcon class="mr-2 h-5 w-5" aria-hidden="true"/>
                    Edit
                  </button>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <button
                    @click="deletePost"
                    :class="[
                      active ? 'bg-sky-500 text-white' : 'text-gray-900',
                      'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]"
                  >
                    <TrashIcon class="mr-2 h-5 w-5" aria-hidden="true"/>
                    Delete
                  </button>
                </MenuItem>
              </div>
            </MenuItems>
          </transition>
        </Menu>
    </div>

    <!-- Contenu article -->
    <div class="mb-8">
      <ReadMoreReadLess :content="post.body" />
    </div>

    <!-- Pièces Jointes -->
    <div class="grid gap-3 mb-6" :class="[
      post.attachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
    ]">
      <template v-for="(attachment, index) of post.attachments.slice(0, 4)">
        <div
          @click="openAttachment(index)"
          class="group aspect-square bg-sky-100 text-gray-500 flex flex-col items-center justify-center relative cursor-pointer"
        >
          <!-- Affiche nb files en + -->
          <div v-if="index === 3 && post.attachments.length > 4"
               class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/30 text-white flex items-center justify-center text-xl">
            +{{ post.attachments.length - 4 }} more
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
            <PaperClipIcon class="w-12 h-12 mb-3" />
            <small>{{attachment.name}}</small>
          </div>
        </div>
      </template>
    </div>

    <!-- Like & Comments -->
    <Disclosure v-slot="{ open }">
      <div class="flex gap-2">
        <!-- Like -->
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
      <DisclosurePanel class="mt-3">
        <!-- Créate comment -->
        <div class="flex gap-2 mb-3">
             <a href="javascript:void(0)">
               <img
                 :src="authUser.avatar_url"
                 alt="avatar"
                 class="w-[40px] rounded-full border border-2 transition-all hover:border-sky-500"
               >
             </a>
             <div class="flex flex-1">
              <InputTextarea
                v-model="newCommentText"
                rows="1"
                class="w-full resize-none rounded-r-none max-h-[160px]"
                placeholder="Enter your comment here"
              />
              <SkyButton
                @click="createComment"
                :type="button"
                :outline="false"
                class="w-[100px] rounded-l-none"
              >
                Submit
              </SkyButton>
             </div>
           </div>

        <!-- Display comments -->
        <div>
          <div v-for="comment of post.comments" :key="comment.id" class="mb-4">
            <div class="flex gap-2">
              <a href="javascript:void(0)">
                <img
                  :src="comment.user.avatar_url"
                  alt="avatar"
                  class="w-[40px] rounded-full border border-2 transition-all hover:border-sky-500"
                >
              </a>
              <div>
                <h4 class="font-bold">
                  <a href="javascript:void(0)" class="hover:underline">
                    {{comment.user.name}}
                  </a>
                </h4>
                <small class="text-gray-400 text-xs">
                  {{adjustTime(comment.updated_at)}}
                </small>
              </div>
            </div>
            <!-- Contenu commentaires -->
            <ReadMoreReadLess :content="comment.comment" content-class="text-sm flex flex-1 ml-12" />
          </div>
        </div>
      </DisclosurePanel>
    </Disclosure>

    </div>
</template>

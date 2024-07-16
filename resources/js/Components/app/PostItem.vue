<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ArrowDownTrayIcon, DocumentIcon, HandThumbUpIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { PencilIcon, TrashIcon, EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    post: Object
})

const emit = defineEmits(['editClick'])

function isImage(attachment) {
    const mime = attachment.mime.split('/')
    return mime[0].toLowerCase() === 'image'
}

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

</script>

<template>
  <div class="bg-white border rounded p-4 mb-3">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
      <PostUserHeader :post="post" />
      <!-- Menu Dropdown -->
      <Menu as="div" class="relative inline-block text-left">
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
    <div class="mb-3">
      <Disclosure v-slot="{ open }">
        <div class="ck-content-output" v-if="!open" v-html="post.body.substring(0, 200)" />
        <DisclosurePanel>
          <div class="ck-content-output" v-html="post.body" />
        </DisclosurePanel>
        <div class="flex justify-end">
          <DisclosureButton v-if="post.body.length >= 200" class="text-blue-500 hover:underline">
              {{ open ? 'Read Less' : 'Read More' }}
          </DisclosureButton>
        </div>
      </Disclosure>
    </div>

    <!-- Pièces Jointes -->
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 mb-3">
      <template v-for="attachment of post.attachments">

            <div class="group aspect-square bg-blue-100 text-gray-500 flex flex-col items-center justify-center relative">
                <!-- Bouton Icon Download -->
                <button class="opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center bg-gray-700 text-gray-100 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800">
                    <ArrowDownTrayIcon class="w-4 h-4"/>
                </button>

                <img
                    v-if="isImage(attachment)"
                    :src="attachment.url"
                    alt=""
                    class="object-cover aspect-square"
                />

                <template v-else>
                    <DocumentIcon class="w-12 h-12" />
                    <small>{{attachment.name}}</small>
                </template>
            </div>
        </template>
    </div>

    <!-- Like & Comments -->
    <div class="flex gap-2">
      <button class="flex gap-1 items-center justify-center py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg flex-1">
        <HandThumbUpIcon class="w-6 h-6" />
        Like
      </button>
      <button class="flex gap-1 items-center justify-center py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg flex-1">
        <ChatBubbleLeftRightIcon class="w-6 h-6" />
        Comment
    </button>
    </div>
    </div>
</template>

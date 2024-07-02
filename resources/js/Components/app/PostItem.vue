<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { ArrowDownTrayIcon, DocumentIcon, HandThumbUpIcon, ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'

defineProps({
    post: Object
})

function isImage(attachment) {
    const mime = attachment.mime.split('/')
    return mime[0].toLowerCase() === 'image'
}

</script>

<template>
    <div class="bg-white border rounded p-4 mb-3">
        <!-- Header -->
        <div class="flex items-center gap-2 mb-3">
            <a href="javascript:void(0)">
                <img :src="post.user.avatar" alt="" class="w-[40px] rounded-full border border-2 transition-all hover:border-blue-500">
            </a>
            <div>
                <h4 class="font-bold">
                    <a href="javascript:void(0)" class="hover:underline">{{post.user.name}}</a>
                    <template v-if="post.group">
                        >
                        <a href="javascript:void(0)" class="hover:underline">{{post.group.name}}</a>
                    </template>
                </h4>
                <small class="text-gray-400">{{post.created_at}}</small>
            </div>
        </div>

        <!-- Contenu article -->
        <div class="mb-3">
            <Disclosure v-slot="{ open }">
                <div v-if="!open" v-html="post.body.substring(0, 200)" />
                <DisclosurePanel>
                    <div v-html="post.body" />
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

<style scoped>

</style>

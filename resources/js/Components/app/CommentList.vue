<script setup>
import { ChatBubbleLeftEllipsisIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline'
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { adjustTime } from "@/helpers.js";
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import InputTextarea from "@/Components/InputTextarea.vue";
import SkyButton from "@/Components/SkyButton.vue";
import ReadMoreReadLess from "@/Components/app/ReadMoreReadLess.vue";
import EditDeleteDropdown from "@/Components/app/EditDeleteDropdown.vue";
import axiosClient from "@/axiosClient.js";

const props = defineProps({
  post: Object,
  data: Object,
  parentComment: {
    type: [Object, null],
    default: null
  }
})

const emit = defineEmits(['commentCreate', 'commentDelete'])

const authUser = usePage().props.auth.user;

const newCommentText = ref('')
const editingComment = ref(null)

function createComment() {
  axiosClient.post(route('post.comment.create', props.post), {
    comment: newCommentText.value,
    parent_id: props.parentComment?.id || null
  })
    .then(({data}) => {
      newCommentText.value = ''
      props.data.comments.unshift(data)

      if (props.parentComment) {
        props.parentComment.num_of_comments++
      }
      props.post.num_of_comments++
      emit('commentCreate', data)
    })
}

function deleteComment(comment) {
  if (!window.confirm('Are you sure you want to delete this comment?')) {
    return false
  }
  axiosClient.delete(route('comment.delete', comment.id))
    .then(() => {
      const commentIndex = props.data.comments.findIndex(c => c.id === comment.id)
      props.data.comments.splice(commentIndex, 1)
      if (props.parentComment) {
        props.parentComment.num_of_comments--
      }
      props.post.num_of_comments--
      emit('commentDelete', comment)
    })
}

function startCommentEdit(comment) {
  editingComment.value = {
    id: comment.id,
    comment: comment.comment.replace(/<br\s*\/?>/gi, '\n')
  }
}

function updateComment() {
  axiosClient.put(route('comment.update', editingComment.value.id), editingComment.value)
    .then(({data}) => {
      editingComment.value = null
      props.data.comments = props.data.comments.map((c) => {
        if (c.id === data.id) {
          return data
        }
        return c
      })
    })
}

function sendCommentReaction(comment) {
  axiosClient.post(route('comment.reaction', comment.id), {
    reaction: 'like'
  })
    .then(({data}) => {
      comment.current_user_has_reaction = data.current_user_has_reaction
      comment.num_of_reactions = data.num_of_reactions
    })
}

function onCommentCreate(comment) {
  if (props.parentComment) {
    props.parentComment.num_of_comments++
  }
  emit('commentCreate', comment)
}

function onCommentDelete(comment) {
  if (props.parentComment) {
    props.parentComment.num_of_comments--
  }
  emit('commentDelete', comment)
}
</script>

<template>
  <!-- Create comment -->
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
    <div v-for="comment of data.comments" :key="comment.id" class="mb-4">
      <div class="flex gap-2 justify-between">
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
        <!-- Menu Dropdown -->
        <EditDeleteDropdown
          :user="comment.user"
          @edit="startCommentEdit(comment)"
          @delete="deleteComment(comment)"
        />
      </div>
      <div class="pl-12">
        <!-- Contenu commentaires -->
        <div  v-if="editingComment && editingComment.id === comment.id">
          <!-- Edition d'un commentaire -->
          <InputTextarea
            v-model="editingComment.comment"
            rows="1"
            class="w-full resize-none max-h-[160px]"
            placeholder="Enter your comment here"
          />
          <div class="flex gap-2 justify-end">
            <button @click="editingComment = null" class="text-sky-600">cancel</button>
            <SkyButton
              @click="updateComment()"
              :type="button"
              :outline="true"
              class="w-[100px]"
            >
              update
            </SkyButton>
          </div>
        </div>
        <!-- Affichage du commentaire -->
        <ReadMoreReadLess
          v-else
          :content="comment.comment"
          content-class="text-sm flex flex-1"
        />
        <!-- like/Unlike Comments-->
        <Disclosure>
          <div class="mt-1 flex gap-2">
            <button
              @click="sendCommentReaction(comment)"
              class="flex items-center text-xs text-sky-600 py-0.5 px-1 rounded-lg"
              :class="[
                      comment.current_user_has_reaction ?
                      'bg-sky-50 hover:bg-sky-100' :
                      'hover:bg-sky-50',
                    ]"
            >
              <HandThumbUpIcon class="w-3 h-3 mr-1" />
              <span class="mr-2">{{ comment.num_of_reactions }}</span>
              {{ comment.current_user_has_reaction ? 'unlike' : 'like' }}
            </button>
            <DisclosureButton
              class="flex items-center text-xs text-sky-600 py-0.5 px-1 hover:bg-sky-100 rounded-lg">
              <ChatBubbleLeftEllipsisIcon class="w-3 h-3 mr-1"
              />
              <span class="mr-2">{{ comment.num_of_comments }}</span>
              comments
            </DisclosureButton>
          </div>
          <!-- Display sub comments -->
          <DisclosurePanel class="mt-3">
            <CommentList
              :post="post"
              :data="{comments: comment.comments}"
              :parent-comment="comment"
              @comment-create="onCommentCreate"
              @comment-delete="onCommentDelete"
            />
          </DisclosurePanel>
        </Disclosure>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>

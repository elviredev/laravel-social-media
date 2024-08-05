<script setup>
  import { computed, ref, watch } from 'vue'
  import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
  import PostUserHeader from "@/Components/app/PostUserHeader.vue";
  import { XMarkIcon, BookmarkIcon } from '@heroicons/vue/24/solid'
  import { PaperClipIcon, VideoCameraIcon } from "@heroicons/vue/24/outline/index.js";
  import { useForm } from "@inertiajs/vue3";
  import { ClassicEditor, Bold, Essentials, Italic, Heading, Paragraph, List, Link, Indent, BlockQuote } from "ckeditor5";
  import 'ckeditor5/ckeditor5.css';
  import { isImage } from "@/helpers.js";

  const editor = ClassicEditor
  const editorConfig = {
    plugins: [ Bold, Essentials, Italic, Paragraph, Heading, Link, List, Indent, BlockQuote],
    toolbar: [ 'heading', '|', 'bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote']
  }

  const props = defineProps({
    post: {
      type: Object,
      required: true
    },
    modelValue: Boolean
  })

  /**
   * {
   *   file: File,
   *   url: ''
   * }
   * @type {Ref<UnwrapRef<*[]>>}
   */
  const attachmentFiles = ref([])

  const form = useForm({
    id: null,
    body: '',
    attachments: []
  })

  const show = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
  })

  const emit = defineEmits(['update:modelValue'])

  watch(() => props.post, () => {
    // console.log("Changed", props.post)
    form.id = props.post.id
    form.body = props.post.body
  })

  function closeModal() {
    show.value = false
    resetModal()
  }

  function resetModal() {
    form.reset()
    attachmentFiles.value = []
  }

  function handleSubmit() {
    form.attachments = attachmentFiles.value.map((myFile) => myFile.file)
    if (form.id) { // update Post
      form.put(route('post.update', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
          closeModal()
        }
      })
    } else { // create Post
      form.post(route('post.create'), {
        onSuccess: () => {
          closeModal()
        }
      });
    }
  }

/**
 * Sélectionner les pièces-jointes pour les lire
 * @param $event
 * @returns {Promise<void>}
 */
async function onAttachmentChoose($event) {
  // console.log($event.target.files)
  for (const file of $event.target.files) {
    const myFile = {
      file,
      url: await readFile(file)
    }
    attachmentFiles.value.push(myFile)
  }
  $event.target.value = null
  console.log(attachmentFiles.value)
}

/**
 * Lire les fichiers
 * @param file
 * @returns {Promise<unknown>}
 */
async function readFile(file) {
  return new Promise((res, rej) => {
    if (isImage(file)) {
      const reader = new FileReader();
      reader.onload = () => {
        res(reader.result)
      }
      reader.onerror = rej
      reader.readAsDataURL(file)
    } else {
      res(null)
    }
  })
  }

  /**
   * Supprimer une pièce-jointe de l'aperçu
   * @param myFile
   */
  function removeFile(myFile) {
    attachmentFiles.value = attachmentFiles.value.filter(file => file !== myFile)
  }

</script>

<template>
  <teleport to="body">
    <TransitionRoot appear :show="show" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/25" />
        </TransitionChild>
        <div class="fixed inset-0 overflow-y-auto">
          <div
            class="flex min-h-full items-center justify-center p-4 text-center"
          >
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded bg-white text-left align-middle shadow-xl transition-all"
              >
                <DialogTitle
                  as="h3"
                  class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900"
                >
                  {{ form.id ? 'Update Post' : 'Create Post' }}
                  <button @click="closeModal" class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center">
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </DialogTitle>
                <div class="p-4">
                  <PostUserHeader :post="post" :show-time = false class="mb-4" />
                  <ckeditor
                    :editor="editor"
                    v-model="form.body"
                    :config="editorConfig">
                  </ckeditor>
                  <!-- Aperçu pièces-jointes -->
                  <div class="grid gap-3 my-3" :class="[
                    attachmentFiles.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                  ]">
                    <template v-for="myFile of attachmentFiles">
                      <div class="group aspect-square bg-sky-100 text-gray-500 flex flex-col items-center justify-center relative ">
                        <button
                          @click="removeFile(myFile)"
                          class="absolute z-20 right-3 top-3 w-7 h-7 flex items-center justify-center bg-black/60 text-white rounded-full hover:bg-black/90"
                        >
                          <XMarkIcon class="w-5 h-5" />
                        </button>

                        <img
                          v-if="isImage(myFile.file)"
                          :src="myFile.url"
                          alt=""
                          class="object-contain aspect-square"
                        />

                        <template v-else>
                          <template v-if="myFile.file.type === 'video/mp4'">
                            <VideoCameraIcon class="w-10 h-10 mb-3" />
                          </template>
                          <template v-else>
                            <PaperClipIcon class="w-10 h-10 mb-3" />
                          </template>
                          <small class="text-center break-words w-full">
                            {{myFile.file.name}}
                          </small>
                        </template>
                      </div>
                    </template>
                  </div>
                </div>

                <div class=" flex gap-2 py-3 px-4">
                  <button type="button" class="btn-outline" @click="handleSubmit">
                    <PaperClipIcon class="w-4 h-4 mr-2" />
                    Attach Files
                    <input
                      @click.stop
                      @change="onAttachmentChoose"
                      type="file"
                      multiple
                      class="absolute left-0 top-0 right-0 bottom-0 opacity-0">
                  </button>
                  <button type="button" class="btn-not-outline" @click="handleSubmit">
                    <BookmarkIcon class="w-4 h-4 mr-2" />
                    Submit
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </teleport>
</template>



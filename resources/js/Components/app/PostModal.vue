<script setup>
  import { computed, ref, watch } from 'vue'
  import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
  import { XMarkIcon, BookmarkIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/solid'
  import { PaperClipIcon, VideoCameraIcon } from "@heroicons/vue/24/outline/index.js";
  import {useForm, usePage} from "@inertiajs/vue3";
  import { ClassicEditor, Bold, Essentials, Italic, Heading, Paragraph, List, Link, Indent, BlockQuote } from "ckeditor5";
  import 'ckeditor5/ckeditor5.css';
  import { isImage } from "@/helpers.js";
  import PostUserHeader from "@/Components/app/PostUserHeader.vue";

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
   * variable globale provenant de HandleInertiaRequests.php
   */
  const attachmentExtensions = usePage().props.attachmentExtensions;

  /**
   * {
   *   file: File,
   *   url: ''
   * }
   * @type {Ref<UnwrapRef<*[]>>}
   */
  const attachmentFiles = ref([])
  const attachmentErrors = ref([])
  const showExtensionsText = ref(false)

  const form = useForm({
    body: '',
    attachments: [],
    deleted_file_ids: [],
    _method: 'POST'
  })

  const show = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
  })

  const computedAttachments = computed(() => {
      return [...attachmentFiles.value, ...(props.post.attachments || [])]
  })

  const emit = defineEmits(['update:modelValue', 'hide'])

  watch(() => props.post, () => {
    // console.log("Changed", props.post)
    form.body = props.post.body || ''
  })

  function closeModal() {
    show.value = false
    emit('hide')
    resetModal()
  }

  function resetModal() {
    form.reset()
    attachmentFiles.value = []
    showExtensionsText.value = false
    attachmentErrors.value = []
    if (props.post.attachments) {
      props.post.attachments.forEach(file => file.deleted = false)
    }
  }

  function handleSubmit() {
    form.attachments = attachmentFiles.value.map((myFile) => myFile.file)
    // console.log(form)
    if (props.post.id) { // update Post
      form._method = 'PUT'
      form.post(route('post.update', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
          closeModal()
        },
        onError: (errors) => {
          processErrors(errors)
        }
      })
    } else { // create Post
      form.post(route('post.create'), {
        onSuccess: () => {
          closeModal()
        },
        onError: (errors) => {
          processErrors(errors)
        }
      });
    }
  }

  function processErrors(errors) {
    for (const key in errors) {
      if (key.includes('.')) {
        const [, index] = key.split('.')
        attachmentErrors.value[index] = errors[key]
      }
    }
    console.error(errors)
  }

/**
 * Sélectionner les pièces-jointes pour les lire
 * @param $event
 * @returns {Promise<void>}
 */
async function onAttachmentChoose($event) {
  // console.log($event.target.files)
  showExtensionsText.value = false
  for (const file of $event.target.files) {
    // récupérer extension depuis le name
    let parts = file.name.split('.')
    // récupérer la dernière partie
    let ext = parts.pop().toLowerCase()
    if (!attachmentExtensions.includes(ext)) {
      showExtensionsText.value = true
    }

    const myFile = {
      file,
      url: await readFile(file)
    }
    attachmentFiles.value.push(myFile)
  }
  $event.target.value = null
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
    if (myFile.file) {
      attachmentFiles.value = attachmentFiles.value.filter(file => file !== myFile)
    } else {
      form.deleted_file_ids.push(myFile.id)
      myFile.deleted = true
    }
  }

  /**
   * Annuler l'action de suppression d'un fichier existant
   * @param myFile
   */
  function undoDelete(myFile) {
    myFile.deleted = false
    form.deleted_file_ids = form.deleted_file_ids.filter(id => id !== myFile.id)
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
                  {{ post.id ? 'Update Post' : 'Create Post' }}
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

                  <!-- Information sur les extensions de fichiers autorisés -->
                  <div v-if="showExtensionsText" class="border-l-4 border-amber-500 py-2 px-3 bg-amber-100 mt-3 text-gray-800">
                    Files must be one of the following extensions
                    <small>{{ attachmentExtensions.join(', ') }}</small>
                  </div>

                  <!-- Aperçu pièces-jointes -->
                  <div class="grid gap-3 my-3" :class="[
                    computedAttachments.length === 1 ? 'grid-cols-1' : 'grid-cols-2'
                  ]">
                    <div v-for="(myFile, index) of computedAttachments">
                      <div
                        class="group aspect-square bg-sky-100 text-gray-500 flex flex-col items-center justify-center relative border"
                        :class="attachmentErrors[index] ? 'border-red-500' : ''"
                      >
                        <div v-if="myFile.deleted" class="absolute left-0 bottom-0 right-0 py-2 px-3 bg-black text-sm text-white flex justify-between items-center z-10">
                          To be deleted
                          <ArrowUturnLeftIcon @click="undoDelete(myFile)" class="w-4 h-4 cursor-pointer" />
                        </div>

                        <button
                          @click="removeFile(myFile)"
                          class="absolute z-20 right-3 top-3 w-7 h-7 flex items-center justify-center bg-black/60 text-white rounded-full hover:bg-black/90"
                        >
                          <XMarkIcon class="w-5 h-5" />
                        </button>

                        <img
                          v-if="isImage(myFile.file || myFile)"
                          :src="myFile.url"
                          alt=""
                          class="object-contain aspect-square"
                          :class="myFile.deleted ? 'opacity-50' : ''"
                        />

                        <div
                          v-else
                          class="flex flex-col justify-center items-center px-3"
                          :class="myFile.deleted ? 'opacity-50' : ''"
                        >
                          <template v-if="(myFile.file || myFile).type === 'video/mp4'">
                            <VideoCameraIcon class="w-10 h-10 mb-3" />
                          </template>
                          <template v-else>
                            <PaperClipIcon class="w-10 h-10 mb-3" />
                          </template>
                          <small class="text-center break-words w-full">
                            {{ (myFile.file || myFile).name }}
                          </small>
                        </div>
                      </div>
                     <small class="text-red-500"> {{ attachmentErrors[index] }}</small>
                    </div>
                  </div>
                </div>

                <div class=" flex gap-2 py-3 px-4">
                  <button type="button" class="btn-outline" >
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



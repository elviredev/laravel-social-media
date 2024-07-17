<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";
import { ChevronRightIcon } from '@heroicons/vue/24/outline'
import GroupListItems from "@/Components/app/GroupListItems.vue";
</script>

<template>
  <div class="px-3 py-3 bg-white rounded border h-full overflow-hidden">
    <div class="block lg:hidden">
      <Disclosure v-slot="{ open }">
        <DisclosureButton class="w-full">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold">My Groups</h2>
            <ChevronRightIcon class="w-6 h-6 transition-all" :class="open ? 'rotate-90 transform' : ''" />
          </div>
        </DisclosureButton>
        <DisclosurePanel>
          <GroupListItems />
        </DisclosurePanel>
      </Disclosure>
    </div>

      <div class="h-full flex-col overflow-hidden hidden lg:flex">
        <h2 class="text-xl font-bold">My Groups</h2>
        <GroupListItems />
      </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import BaseLayout from '@/Components/BaseLayout.vue';

const props = defineProps({
  hall: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  is_scrape: props.hall.data.is_scrape,
  note: props.hall.data.note,
  external_url: props.hall.data.external_url,
})
</script>

<template>
  <base-layout>
    <template #main>
      <div class="my-8">
        <h1 class="text-3xl font-bold">{{ hall.data.name }}&nbsp;ホール編集</h1>
      </div>

      <form @submit.prevent="form.put('/halls/' + hall.data.id)">
        <button type="submit" :disabled="form.processing" class="mb-8 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">更新</button>
        <div class="mb-8">
          <label for="is-scrape" class="text-lg inline-flex items-center cursor-pointer">データ取得フラグ
            <input
              id="is-scrape"
              v-model="form.is_scrape"
              type="checkbox"
              class="sr-only peer"
              :true-value="1"
              :false-value="0"
            >
            <div class="ml-4 relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
          </label>
        </div>

        <div class="mb-8">
          <label for="note" class="text-lg block mb-2 font-medium text-gray-900">備考</label>
          <textarea
            v-model="form.note"
            id="note"
            rows="20"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            placeholder="Leave a comment..."
          ></textarea>
        </div>

        <div>
          <label for="external-url" class="text-lg block mb-2 font-medium text-gray-900">外部URL</label>
          <textarea
            v-model="form.external_url"
            id="external-url"
            rows="20"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
            placeholder="Leave a comment..."
          ></textarea>
        </div>
      </form>
    </template>
  </base-layout>
</template>

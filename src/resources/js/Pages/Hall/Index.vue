<script setup>
import { Link } from '@inertiajs/vue3'
import BaseLayout from '@/Components/BaseLayout.vue';

defineProps({
  halls: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <base-layout>
    <template #main>
      <div class="my-8">
        <h1 class="text-3xl font-bold">ホール一覧</h1>
      </div>

      <div class="flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
            <div class="overflow-hidden">
              <table class="min-w-full text-left text-sm font-light">
                <thead class="border-b font-medium dark:border-neutral-500">
                  <tr>
                    <th scope="col" class="px-2 sm:px-6 py-4">店名</th>
                    <th scope="col" class="px-2 sm:px-6 py-4">データ</th>
                    <th scope="col" class="px-2 sm:px-6 py-4">外部URL</th>
                    <th scope="col" class="hidden sm:table-cell px-2 sm:px-6 py-4">備考</th>
                    <th scope="col" class="hidden sm:table-cell px-2 sm:px-6 py-4">更新日</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="hall in halls.data"
                    :key="hall.id"
                    class="border-b dark:border-neutral-500"
                  >
                    <td class="whitespace-nowrap px-2 sm:px-6 py-4 font-medium">
                      <Link :href="'/halls/' + hall.id + '/edit'">
                        {{ hall.name }}
                      </Link>
                    </td>
                    <td class="px-2 sm:px-6 py-4 font-medium">
                      <div class="flex whitespace-nowrap">
                        <Link :href="'/halls/' + hall.id + '/hall-data'">
                          <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">編集</span>
                        </Link>
                        <Link :href="'/halls/' + hall.id + '/hall-data/detail'">
                          <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">詳細</span>
                        </Link>
                      </div>
                    </td>
                    <td class="px-2 sm:px-6 py-4 font-medium">
                      <div v-html="hall.external_url" class="cursor-pointer"></div>
                    </td>
                    <td class="hidden sm:table-cell px-2 sm:px-6 py-4 font-medium">
                      <div v-html="hall.note"></div>
                    </td>
                    <td class="hidden sm:table-cell px-2 sm:px-6 py-4 font-medium">
                      <div>{{ hall.updated_at }}</div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </template>
  </base-layout>
</template>

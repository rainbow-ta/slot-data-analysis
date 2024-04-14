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
        <h1 class="text-4xl font-bold">ホール一覧</h1>
      </div>

      <div class="flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
            <div class="overflow-hidden">
              <table class="min-w-full text-left text-sm font-light">
                <thead class="border-b font-medium dark:border-neutral-500">
                  <tr>
                    <th scope="col" class="px-6 py-4">店名</th>
                    <th scope="col" class="px-6 py-4">P-WORLD</th>
                    <th scope="col" class="px-6 py-4">DMMぱちタウン</th>
                    <th scope="col" class="px-6 py-4">イベント媒体</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="hall in halls"
                    class="border-b dark:border-neutral-500"
                  >
                    <td class="whitespace-nowrap px-6 py-4 font-medium">
                      {{ hall.name }}
                      <div class="py-2">
                        <Link :href="'/halls/' + hall.id + '/hall-data/event'">
                          <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">イベント日</span>
                        </Link>
                        <Link :href="'/halls/' + hall.id + '/hall-data/detail'">
                          <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">詳細</span>
                        </Link>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium cursor-pointer">
                      <a :href="hall.p_world_url" target="_blank">{{ hall.p_world_url }}</a>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium cursor-pointer">
                      <a :href="hall.dmm_url" target="_blank">{{ hall.dmm_url }}</a>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium cursor-pointer">
                      <a :href="hall.event_url" target="_blank">{{ hall.event_url }}</a>
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

<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BaseLayout from '@/Components/BaseLayout.vue';

const props = defineProps({
  hallCollection: {
    type: Object,
    required: true,
  },
});

const date = ref(props.hallCollection.data.date);
const fetchData = () => {
  router.visit('/halls/'  + props.hallCollection.data.hall.id + '/hall-data', {
    method: 'get',
    data: {
      date: date.value,
    },
  });
};

const update = (isHighSetting, id) => {
  router.put('/halls/'  + props.hallCollection.data.hall.id + '/hall-data/' + id, {
    is_high_setting: isHighSetting,
    date: date.value,
  }, {
    preserveScroll: true,
  });
};
</script>

<template>
  <base-layout>
    <template #main>
      <div class="my-8">
        <h1 class="text-4xl font-bold">{{ hallCollection.data.hall.name }}&nbsp;データ編集</h1>
      </div>

      <input
        @change="fetchData"
        type="date"
        id="date"
        v-model="date"
        class="appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
      >

      <div class="my-5" v-if="!hallCollection.data.hallData.length">
        <h2 class="text-lg">該当する日付のデータが存在しません</h2>
      </div>
      <div v-else class="flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
            <div class="overflow-hidden">
              <table class="min-w-full text-left text-sm font-light">
                <thead class="border-b font-medium dark:border-neutral-500">
                  <tr>
                    <th scope="col" class="px-6 py-4">高設定フラグ</th>
                    <th scope="col" class="px-6 py-4">台番号</th>
                    <th scope="col" class="px-6 py-4">機種名</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="data in hallCollection.data.hallData"
                    class="border-b dark:border-neutral-500"
                    >
                    <td v-show="false" class="whitespace-nowrap px-6 py-4 font-medium cursor-pointer">
                      {{ data.id }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium">
                      <label class="inline-flex items-center cursor-pointer">
                        <input
                          v-model="data.is_high_setting"
                          @click="update(!data.is_high_setting, data.id)"
                          type="checkbox"
                          class="sr-only peer"
                          :true-value="1"
                          :false-value="0"
                        >
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                      </label>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium">
                      {{ data.slot_number }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium">
                      {{ data.slot_machine_name }}
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

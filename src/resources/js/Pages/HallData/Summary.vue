<script setup>
import BaseLayout from '@/Components/BaseLayout.vue';

const props = defineProps({
  hallName: {
    type: String,
    required: true,
  },
  differenceCoinsBySlotMachines: {
    type: Object,
    required: true,
  },

});
</script>

<style scoped>
.table-container {
  overflow-x: auto;
  max-height: 80vh;
}

th.sticky {
  position: sticky;
}
</style>

<template>
  <base-layout>
    <div class="my-8">
      <h1 class="text-4xl font-bold">{{ hallName }}&nbsp;データ概要</h1>
    </div>

    <div class="my-8">
      <h2 class="text-3xl font-bold">機種ごとの差枚</h2>
    </div>

    <div class="table-container">
      <table
        v-if="Object.keys(differenceCoinsBySlotMachines).length > 0"
        class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
      >
        <thead class="text-xs text-gray-700 uppercase bg-gray-200">
          <tr>
            <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
            <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">差枚</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(coins, machineName) in differenceCoinsBySlotMachines"
            :key="machineName"
          >
            <td class="border px-4 py-2 text-gray-700">{{ machineName }}</td>
            <td class="border px-4 py-2 text-gray-700">{{ coins }}</td>
          </tr>
        </tbody>
      </table>
      <table v-else>
        データがありません。
      </table>
    </div>
  </base-layout>
</template>

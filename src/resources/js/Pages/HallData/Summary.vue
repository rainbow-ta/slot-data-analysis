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
  sortSumDifferenceCoins: {
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
    <template #main>
      <div class="my-8">
        <h1 class="text-4xl font-bold">{{ hallName }}&nbsp;データ概要</h1>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">機種ごとの差枚</h2>
      </div>

      <div class="table-container inline-block">
        <table
          v-if="Object.keys(differenceCoinsBySlotMachines).length > 0"
          class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
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
              <td
                class="border px-4 py-2 text-gray-700"
                :class="{
                  'bg-yellow-50': coins >= 1 && coins <= 10000,
                  'bg-green-100': coins >= 10001 && coins <= 50000,
                  'bg-red-200': coins >= 50001,
                }"
              >
                {{ coins.toLocaleString('ja-JP') }}枚
              </td>
            </tr>
          </tbody>
        </table>
        <table v-else>
          データがありません。
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">台番号ごとの差枚</h2>
      </div>

      <div class="table-container inline-block">
        <table
          v-if="sortSumDifferenceCoins.length > 0"
          class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
        >
          <thead class="text-xs text-gray-700 uppercase bg-gray-200">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">台番号</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">差枚</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="coin in sortSumDifferenceCoins"
              :key="coin"
            >
              <td class="border px-4 py-2 text-gray-700">{{ coin.slot_number }}</td>
              <td class="border px-4 py-2 text-gray-700">{{ coin.slot_machine_name }}</td>
              <td
                class="border px-4 py-2 text-gray-700"
                :class="{
                  'bg-yellow-50': coin.sum_difference_coins >= 1 && coin.sum_difference_coins <= 10000,
                  'bg-green-100': coin.sum_difference_coins >= 10001 && coin.sum_difference_coins <= 50000,
                  'bg-red-200': coin.sum_difference_coins >= 50001,
                }"
              >
                {{ coin.sum_difference_coins.toLocaleString('ja-JP') }}枚
              </td>
            </tr>
          </tbody>
        </table>
        <table v-else>
          データがありません。
        </table>
      </div>
    </template>
  </base-layout>
</template>

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
  allDate: {
    type: Object,
    required: true,
  },
  machineWinRates: {
    type: Object,
    required: true,
  },
  allDateData: {
    type: Object,
    required: true,
  },
});

// TODO:他の画面でも使う処理を共通化する
const highlightColorForDifferenceCoins = (coins) => {
  return coins > 0 ? true : false;
}

const formatDifferenceCoins = (coins) => {
  return coins > 0 ? `+${coins}` : coins;
};

const maxLength = 17;
const truncateText = (text) => {
  return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
}
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
        <h1 class="text-4xl font-bold">{{ hallName }}&nbsp;イベント日</h1>
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

      <div class="my-8">
        <h2 class="text-3xl font-bold">機種ごとのデータ</h2>
      </div>

      <div class="table-container">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
              <th
                v-for="date in allDate"
                :key="date"
                class="sticky top-0 z-10 px-4 py-2 bg-gray-200"
              >
                {{ date }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(dateArray, machineName) in machineWinRates"
              :key="machineName"
            >
              <th class="sticky left-0 bg-gray-200 px-4 py-2 text-gray-700">{{ machineName }}</th>
              <td
                v-for="date in allDate"
                :key="date"
                class="border px-4 py-2 text-gray-700"
                :class="{
                  'bg-yellow-50': dateArray[date] && dateArray[date]['average_difference_coins'] >= 1 && dateArray[date]['average_difference_coins'] <= 1000,
                  'bg-green-100': dateArray[date] && dateArray[date]['average_difference_coins'] > 1000 && dateArray[date]['average_difference_coins'] <= 2000,
                  'bg-red-200': dateArray[date] && dateArray[date]['average_difference_coins'] > 2000
                }"
              >
                <template v-if="dateArray[date]">
                  <div>{{ dateArray[date]['win_count'] }}/{{ dateArray[date]['count'] }}台</div>
                  <div>{{ dateArray[date]['average_kikaiwari'] }}%</div>
                  <div>{{ dateArray[date]['average_game_count'].toLocaleString('ja-JP') }}G</div>
                  <div>{{ dateArray[date]['average_difference_coins'].toLocaleString('ja-JP') }}枚</div>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">日付ごとのデータ</h2>
      </div>

      <div class="table-container">
        <table class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">台番号</th>
              <th
                v-for="date in allDate"
                :key="date"
                class="sticky top-0 z-10 px-4 py-2 bg-gray-200"
              >
                {{ date }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(dateData, slotNumber) in allDateData" :key="slotNumber">
              <th class="sticky left-0 bg-gray-200 px-4 py-2 text-gray-700">{{ slotNumber }}</th>
              <td
                v-for="(item, date) in dateData"
                :key="date"
                :class="{ 'bg-red-200': item.is_high_setting }"
                class="border px-4 py-2 text-gray-700"
              >
                <div class="truncate">{{ truncateText(item.name) }}</div>
                <div>{{ item.game_count?.toLocaleString('ja-JP') }}G</div>
                <div :class="{ 'text-red-500': highlightColorForDifferenceCoins(item.difference_coins) }">
                  {{ formatDifferenceCoins(item.difference_coins?.toLocaleString('ja-JP')) }}枚
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </base-layout>
</template>

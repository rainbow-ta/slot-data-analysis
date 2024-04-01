<script setup>
import BaseLayout from '@/Components/BaseLayout.vue';

const props = defineProps({
  hallName: {
    type: String,
    required: true,
  },
  uniqueDateCount: {
    type: Number,
    required: true,
  },
  highSettingSlotNumbers: {
    type: Object,
    required: true,
  },
  allDate: {
    type: Object,
    required: true,
  },
  highSettingMachines: {
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

const generateInjectionRateString = (count) => {
  const injectionRate = calculateInjectionRate(count);

  return `${injectionRate}%（${count}/${props.uniqueDateCount}回）`;
};

const calculateInjectionRate = (count) => {
  return (count / props.uniqueDateCount * 100).toFixed(1);
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

.bg-gold {
  background: linear-gradient(45deg, #B67B03 0%, #DAAF08 45%, #FEE9A0 70%, #DAAF08 85%, #B67B03 90% 100%);
}
</style>

<template>
  <base-layout>
    <template #main>
      <div class="my-8">
        <h1 class="text-4xl font-bold">{{ hallName }}&nbsp;イベント日</h1>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">機種ごとの投入率</h2>
      </div>

      <div class="table-container">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">投入率</th>
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
              v-for="(dateArray, machineName) in highSettingMachines"
              :key="machineName"
            >
              <th class="sticky left-0 bg-gray-200 px-4 py-2 text-gray-700">{{ machineName }}</th>

              <td 
              :class="{
                'bg-green-100': calculateInjectionRate(dateArray['total']) >= 50 && calculateInjectionRate(dateArray['total']) <= 80,
                'bg-red-200': calculateInjectionRate(dateArray['total']) >= 80,
                'bg-gold': calculateInjectionRate(dateArray['total']) === 100,
              }"
              class="border px-4 py-2 text-gray-700">{{ generateInjectionRateString(dateArray['total']) }}</td>
              <td
                v-for="date in allDate"
                :key="date"
                class="border px-4 py-2 text-gray-700"
              >
                <template v-if="dateArray[date]">
                  <div>{{ dateArray[date]['high_setting_count'] }}/{{ dateArray[date]['count'] }}台</div>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">台番号ごとの投入率</h2>
      </div>

      <div class="table-container inline-block">
        <table
          v-if="highSettingSlotNumbers.length > 0"
          class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
        >
          <thead class="text-xs text-gray-700 uppercase bg-gray-200">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">台番号</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">投入率</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">平均G数</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">平均機械割</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="slotNumber in highSettingSlotNumbers"
              :key="slotNumber"
            >
              <td class="border px-4 py-2 text-gray-700">{{ slotNumber.slot_number }}</td>
              <td class="border px-4 py-2 text-gray-700">{{ slotNumber.slot_machine_name }}</td>
              <td
                :class="{
                  'bg-green-100': calculateInjectionRate(slotNumber.count) >= 50 && calculateInjectionRate(slotNumber.count) <= 80,
                  'bg-red-200': calculateInjectionRate(slotNumber.count) >= 80,
                  'bg-gold': calculateInjectionRate(slotNumber.count) === 100,
                }"
                class="border px-4 py-2 text-gray-700"
              >
                {{ generateInjectionRateString(slotNumber.count) }}
              </td>
              <td class="border px-4 py-2 text-gray-700">{{ slotNumber.average_game_count }}</td>
              <td class="border px-4 py-2 text-gray-700">{{ slotNumber.average_rtp }}%</td>
            </tr>
          </tbody>
        </table>
        <table v-else>
          データがありません。
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-3xl font-bold">全てのデータ</h2>
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

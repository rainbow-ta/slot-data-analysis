<script setup>
import BaseLayout from '@/Components/BaseLayout.vue';

const props = defineProps({
  hallName: {
    type: String,
    required: true,
  },
  matsubiArray: {
    type: Object,
    required: true,
  },
  matsubiTotals: {
    type: Object,
    required: true,
  },
  highSettingNumbers: {
    type: Object,
    required: true,
  },
});

const matsubiNumbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

function highlightColorForTotal(value) {
  const maxTotal = Math.max(...Object.values(props.matsubiTotals).map(item => item['total']));
  return value === maxTotal ? true : false;
}

function highlightColorForDate(date, value) {
  const maxInDate = Math.max(...Object.values(props.matsubiArray[date]));
  return value === maxInDate ? true : false;
}
</script>

<style scoped>
.table-container {
  overflow-x: auto;
  max-height: 80vh;
}

th.sticky {
  position: sticky;
  z-index: 999;
}
</style>

<template>
  <base-layout>
    <div class="my-8">
      <h1 class="text-4xl font-bold">{{ hallName }}</h1>
    </div>

    <div class="my-8">
      <h2 class="text-3xl font-bold">末尾ごとのデータ</h2>
    </div>

    <table
      v-if="Object.keys(matsubiArray).length > 0"
      class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
    >
      <thead class="text-xs text-gray-700 uppercase bg-gray-200">
        <tr>
          <th class="px-4 py-2">末尾</th>
          <th class="px-4 py-2">合計</th>
          <th
            v-for="(item, key) in matsubiArray"
            :key="key"
            class="px-4 py-2"
          >
            {{ key }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="matsubiNumber in matsubiNumbers"
          :key="matsubiNumber"
        >
          <td class="border px-4 py-2 text-gray-700">{{ matsubiNumber }}</td>
          <td
            class="border px-4 py-2 text-gray-700"
            :class="{ 'bg-red-200': highlightColorForTotal(matsubiTotals[matsubiNumber]['total']) }"
            :style="{ color: highlightColorForTotal(matsubiTotals[matsubiNumber]['total']) ? 'red' : 'inherit' }"
          >
            {{ matsubiTotals[matsubiNumber]['total'] }}
            （{{ matsubiTotals[matsubiNumber]['percentage'] }}）
          </td>
          <td
            v-for="(item, date) in matsubiArray"
            :key="date"
            class="border px-4 py-2"
            :class="{ 'bg-red-200': highlightColorForDate(date, item[matsubiNumber]) }"
            :style="{ color: highlightColorForDate(date, item[matsubiNumber]) ? 'red' : 'inherit' }"
          >
            {{ item[matsubiNumber] }}
          </td>
        </tr>
      </tbody>
    </table>
    <table v-else>
      データがありません。
    </table>

    <div class="my-8">
      <h2 class="text-3xl font-bold">台番号ごとのデータ</h2>
    </div>

    <div class="table-container">
      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase">
          <tr>
            <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">台番号</th>
            <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">投入回数</th>
            <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="highSettingNumber in highSettingNumbers"
            :key="highSettingNumber.slot_number"
          >
            <td class="border px-4 py-2 text-gray-700">{{ highSettingNumber.slot_number }}</td>
            <td class="border px-4 py-2 text-gray-700">{{ highSettingNumber.count }}</td>
            <td class="border px-4 py-2 text-gray-700">{{ highSettingNumber.slot_machine_name }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </base-layout>
</template>

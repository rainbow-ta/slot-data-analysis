<script setup>
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import BaseLayout from '@/Components/BaseLayout.vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';
import { Line } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
)

const props = defineProps({
  hall: {
    type: Object,
    required: true,
  },
  slotMachineCountsByDate: {
    type: Object,
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
  allDate: {
    type: Object,
    required: true,
  },
  selectedAllDates: {
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
  slumpSlotNumbers: {
    type: Object,
    required: true,
  },
  slotMachineName: {
    type: String,
    required: true,
  },
  startDate: {
    type: [Date, String],
    required: true,
  },
  endDate: {
    type: [Date, String],
    required: true,
  },
  selectedDates: {
    type: Object,
    required: true,
  },
});

const form = reactive({
  slotMachineName: props.slotMachineName,
  startDate: props.startDate,
  endDate: props.endDate,
  selectedDates: props.selectedDates,
});

const fetchData = () => {
  router.visit('/halls/' + props.hall.id + '/hall-data/detail', {
    method: 'get',
    data: form,
  });
};

// TODO:他の画面でも使う処理を共通化する
const matsubiNumbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

function highlightColorForTotal(value) {
  const maxTotal = Math.max(...Object.values(props.matsubiTotals).map(item => item['total']));
  return value === maxTotal ? true : false;
}

function highlightColorForDate(date, value) {
  const maxInDate = Math.max(...Object.values(props.matsubiArray[date]));
  return value === maxInDate ? true : false;
}

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

const generateOptions = (data) => {
  const count = props.allDate.length;
  const prefix = data['total'] < 0 ? '' : '+';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      title: {
        display: true,
        text: count + '日間の差枚数：' + prefix + data['total'],
        color: prefix ? '#ff0000' : '#374151',
      }
    }
  }
}

const generateData = (data) => {
  const newData = props.allDate.map(date => data[date] || null);

  return {
    labels: props.allDate,
    datasets: [
      {
        label: '差枚数',
        backgroundColor: '#f87979',
        data: newData
      }
    ]
  }
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
  <BaseLayout>
    <template #main>
      <div class="my-8">
        <h1 class="text-3xl font-bold">{{ hall.name }}&nbsp;データ詳細</h1>
      </div>

      <div class="my-8">
        <h2 class="text-2xl font-bold">データ絞り込み</h2>
      </div>

      <form @submit.prevent="fetchData" class="bg-gray-200 shadow-md rounded px-4 py-6 mb-4 max-w-lg">
        <div class="mb-5">
          <label for="slot-machine-name" class="block text-gray-700 text-sm font-bold mb-2">機種名</label>
          <input id="slot-machine-name" v-model="form.slotMachineName" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="機種名を入力してください" />
        </div>
        <div class="mb-5">
          <label for="start-date" class="block text-gray-700 text-sm font-bold mb-2">開始日</label>
          <input type="date" id="start-date" v-model="form.startDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
        </div>
        <div class="mb-5">
          <label for="end-date" class="block text-gray-700 text-sm font-bold mb-2">終了日</label>
          <input type="date" id="end-date" v-model="form.endDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" />
        </div>
        <div class="mb-5">
          <label for="selected-dates" class="block text-gray-700 text-sm font-bold mb-2">特定日</label>
          <select
            v-model="form.selectedDates"
            multiple
            id="selected-dates"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 h-64"
          >
            <option
              v-for="date in selectedAllDates"
              :value="date"
              :key="date"
            >
              {{ date }}
            </option>
          </select>
        </div>
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">絞り込み</button>
      </form>

      <div class="my-8">
        <h2 class="text-2xl font-bold">末尾ごとのデータ</h2>
      </div>

      <div class="table-container">
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
                :class="{ 'bg-red-200': highlightColorForTotal(matsubiTotals[matsubiNumber]?.['total']) }"
                :style="{ color: highlightColorForTotal(matsubiTotals[matsubiNumber]?.['total']) ? 'red' : 'inherit' }"
              >
                {{ matsubiTotals[matsubiNumber]?.['total'] }}
                （{{ matsubiTotals[matsubiNumber]?.['percentage'] }}）
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
      </div>

      <div class="my-8">
        <h2 class="text-2xl font-bold">台番号ごとのデータ</h2>
      </div>

      <div class="table-container inline-block">
        <table class="table-auto text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
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

      <div class="my-8">
        <h2 class="text-2xl font-bold">機種ごとのデータ</h2>
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
        <h2 class="text-2xl font-bold">日付ごとのデータ</h2>
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
                <div>B:{{ item.big_bonus_count }}（{{ item.big_bonus_probability }}）</div>
                <div>R:{{ item.regular_bonus_count }}（{{ item.regular_bonus_probability }}）</div>
                <div>合成確率:{{ item.synthesis_probability }}</div>
                <div>ART:{{ item.art_count }}（{{ item.art_probability }}）</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-2xl font-bold">台番号ごとのスランプグラフ</h2>
      </div>

      <div
        v-for="(data, slotNumber) in slumpSlotNumbers"
        class="mb-8"
      >
        <h3 class="text-xl font-bold">{{ slotNumber }}番台：{{ data['slotName'] }}</h3>

        <div style="height:300px;">
          <Line :data=generateData(data) :options=generateOptions(data) />
        </div>
      </div>

      <div class="my-8">
        <h2 class="text-2xl font-bold">月単位の機種データ</h2>
      </div>

      <div
        v-for="(counts, slotName) in slotMachineCountsByDate"
        class="mb-8"
      >
        <h3 class="text-xl font-bold mb-8">{{ slotName }}</h3>

        <div class="table-container">
          <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase">
              <tr>
                <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">台番号</th>
                <th
                  v-for="slotNumber in counts.slot_number"
                  class="sticky top-0 z-10 px-4 py-2 bg-gray-200"
                >
                  {{ slotNumber }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(count, ym) in counts"
              >
                <template v-if="ym == '合計'">
                  <th class="sticky left-0 bg-gray-200 px-4 py-2 text-gray-700">{{ ym }}</th>
                  <td
                    v-for="slotNumber in counts.slot_number"
                    :class="{'bg-red-200': count[slotNumber]['is_top5'] === true}"
                    class="border px-4 py-2 text-gray-700"
                  >
                    {{ count[slotNumber]['count'] }}
                  </td>
                </template>
                <template v-else-if="ym !== 'slot_number'">
                  <th class="sticky left-0 bg-gray-200 px-4 py-2 text-gray-700">{{ ym }}</th>
                  <td
                    v-for="slotNumber in counts.slot_number"
                    class="border px-4 py-2 text-gray-700"
                  >
                    {{ count[slotNumber] }}
                  </td>
                </template>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </BaseLayout>
</template>
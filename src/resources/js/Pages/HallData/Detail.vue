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
  matsubiArray: {
    type: Object,
    required: true,
  },
  matsubiTotals: {
    type: Object,
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
  highSettingMachines: {
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
  allSlotMachineData: {
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
  dataType: {
    type: String,
    required: true,
  },
  floorMapImagePath: {
    type: String,
    required: true,
  },
});

const dataNotFoundMessage = '該当のデータは存在しません。';

const form = reactive({
  slotMachineName: props.slotMachineName,
  startDate: props.startDate,
  endDate: props.endDate,
  selectedDates: props.selectedDates,
  dataType: props.dataType,
  get slotMachineNameArray() {
    return this.slotMachineName.split(',').map(name => name.trim());
  }
});

const fetchData = () => {
  const params = {
    startDate: form.startDate,
    endDate: form.endDate,
    selectedDates: form.selectedDates,
    dataType: form.dataType,
    slotMachineNameArray: form.slotMachineNameArray,
  };

  router.visit(`/halls/${props.hall.id}/hall-data/detail`, {
    method: 'get',
    data: params,
  });
};

const resetForm = () => {
  router.visit('/halls/' + props.hall.id + '/hall-data/detail', {
    method: 'get',
  });
};

// TODO:他の画面でも使う処理を共通化する
const matsubiNumbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
const dayOfWeekMap = ['日', '月', '火', '水', '木', '金', '土'];

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

const generateInjectionRateString = (count) => {
  const injectionRate = calculateInjectionRate(count);

  return `${injectionRate}%（${count}/${props.uniqueDateCount}回）`;
};

const calculateInjectionRate = (count) => {
  return (count / props.uniqueDateCount * 100).toFixed(1);
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

const formattedDate = (dateValue) => {
  const date = new Date(dateValue);
  const dayOfWeek = date.getDay();
  // yyyy-mm-dd 曜日 の形式でフォーマット
  const formattedDate = `${date.getFullYear()}-${('0' + (date.getMonth() + 1)).slice(-2)}-${('0' + date.getDate()).slice(-2)}（${dayOfWeekMap[dayOfWeek]}）`;

  return formattedDate;
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
      <div class="flex my-8">
        <h1 class="text-xl sm:text-3xl font-bold me-3">{{ hall.name }}&nbsp;データ詳細</h1>
        <a
          :href="'/halls/' + hall.id + '/hall-data'"
          class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-xs sm:text-sm px-4 sm:px-5 py-2 sm:py-2.5 text-center"
        >
          編集
        </a>
      </div>

      <div class="my-8">
        <h2 class="text-l sm:text-lg sm:text-2xl font-bold">データ絞り込み</h2>
      </div>

      <form @submit.prevent="fetchData" class="bg-gray-200 shadow-md rounded px-4 py-6 mb-4 max-w-lg">
        <div class="mb-5">
          <label for="slot-machine-name" class="block text-gray-700 text-sm font-bold mb-2">データ種別</label>
          <div class="flex">
            <div class="flex items-center me-4">
              <input
                v-model="form.dataType"
                id="data-type-all"
                type="radio"
                value="all"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
              >
              <label for="data-type-all" class="ms-2 text-sm font-medium text-gray-900">全て</label>
            </div>
            <div class="flex items-center me-4">
              <input
                v-model="form.dataType"
                id="data-type-event"
                type="radio"
                value="event"
                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500"
              >
              <label for="data-type-event" class="ms-2 text-sm font-medium text-gray-900">イベント日</label>
            </div>
          </div>
        </div>
        <div class="mb-5">
          <label for="slot-machine-name" class="block text-gray-700 text-sm font-bold mb-2">機種名</label>
          <input id="slot-machine-name" v-model="form.slotMachineName" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="カンマで区切ることで複数の機種を検索することが出来ます" />
        </div>
        <div v-show="form.dataType === 'all'">
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
                {{ formattedDate(date) }}
              </option>
            </select>
          </div>
        </div>
        <div class="flex">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center me-3">絞り込み</button>

          <button
            @click="resetForm"
            type="button"
            class="text-white bg-gray-600 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center"
          >
            リセット
          </button>
        </div>
      </form>

      <div
        v-if="floorMapImagePath"
        class="my-8"
      >
        <h2 class="text-l sm:text-lg sm:text-2xl font-bold">フロアマップ</h2>
        <img :src=floorMapImagePath alt="Seating Chart">
      </div>

      <div class="my-8">
        <h2 class="text-lg sm:text-2xl font-bold">末尾ごとのデータ</h2>
      </div>

      <template
        v-if="Object.keys(matsubiArray).length > 0"
      >
        <div class="h-[355px] w-[335px] md:w-[700px] xl:w-[1400px] overflow-scroll">
          <table
            class="text-sm text-left rtl:text-right text-gray-500"
          >
            <thead class="text-xs text-gray-700 uppercase bg-gray-200">
              <tr>
                <th
                  class="text-xs sm:text-sm sticky left-0 top-0 z-30 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 p-[0.5rem]"
                >
                  末尾
                </th>
                <th
                  class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]"
                >
                  合計
                </th>
                <th
                  v-for="(item, date) in matsubiArray"
                  :key="date"
                  class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]"
                >
                  {{ formattedDate(date) }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="matsubiNumber in matsubiNumbers"
                :key="matsubiNumber"
              >
                <th class="text-xs sm:text-sm sticky left-0 z-10 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 text-gray-700 p-[0.5rem]">{{ matsubiNumber }}</th>
                <td
                  class="text-xs sm:text-sm text-nowrap max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[6rem] p-[0.5rem] text-gray-700"
                  :class="{ 'bg-red-200': highlightColorForTotal(matsubiTotals[matsubiNumber]?.['total']) }"
                  :style="{ color: highlightColorForTotal(matsubiTotals[matsubiNumber]?.['total']) ? 'red' : 'inherit' }"
                >
                  {{ matsubiTotals[matsubiNumber]?.['total'] }}
                  （{{ matsubiTotals[matsubiNumber]?.['percentage'] }}）
                </td>
                <td
                  v-for="(item, date) in matsubiArray"
                  :key="date"
                  class="text-xs sm:text-sm text-nowrap max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[6rem] p-[0.5rem] text-gray-700"
                  :class="{ 'bg-red-200': highlightColorForDate(date, item[matsubiNumber]) }"
                  :style="{ color: highlightColorForDate(date, item[matsubiNumber]) ? 'red' : 'inherit' }"
                >
                  {{ item[matsubiNumber] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
      <template v-else>
        {{ dataNotFoundMessage }}
      </template>

      <div class="my-8">
        <h2 class="text-lg sm:text-2xl font-bold">台番号ごとの高設定投入率</h2>
      </div>

      <div class="h-[415px] w-[335px] md:w-[700px] xl:w-[795px] overflow-scroll">
        <table
          v-if="highSettingSlotNumbers.length > 0"
          class="text-sm text-left rtl:text-right text-gray-500"
        >
          <thead class="text-xs text-gray-700 uppercase bg-gray-200">
            <tr>
              <th class="text-xs sm:text-sm sticky left-0 top-0 z-30 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 p-[0.5rem]">台番号</th>
              <th class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]">機種名</th>
              <th class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]">投入率</th>
              <th class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]">平均G数</th>
              <th class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]">平均機械割</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="slotNumber in highSettingSlotNumbers"
              :key="slotNumber"
            >
              <td class="text-xs sm:text-sm sticky left-0 z-10 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 text-gray-700 p-[0.5rem]">{{ slotNumber.slot_number }}</td>
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
          {{ dataNotFoundMessage }}
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-lg sm:text-2xl font-bold">機種ごとの高設定投入率</h2>
      </div>

      <div class="table-container">
        <table
          v-if="Object.keys(highSettingMachines).length > 0"
          class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400"
        >
          <thead class="text-xs text-gray-700 uppercase">
            <tr>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">機種名</th>
              <th class="sticky top-0 z-10 px-4 py-2 bg-gray-200">投入率</th>
              <th
                v-for="date in allDate"
                :key="date"
                class="sticky top-0 z-10 px-4 py-2 bg-gray-200"
              >
                {{ formattedDate(date) }}
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
        <table v-else>
          {{ dataNotFoundMessage }}
        </table>
      </div>

      <div class="my-8">
        <h2 class="text-lg sm:text-2xl font-bold">詳細データ</h2>
      </div>

      <template
        v-if="Object.keys(allSlotMachineData).length > 0"
      >
        <template
          v-for="(slotMachineData, slotMachineName) in allSlotMachineData"
        >
          <h3 class="text-base font-bold mt-6 mb-4">{{ slotMachineName }}</h3>

          <div class="h-[350px] w-[335px] md:h-[450px] md:w-[700px] xl:h-[600px] xl:w-[1400px] overflow-scroll">
            <table
              class="text-sm text-left rtl:text-right text-gray-500"
            >
              <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                <tr>
                  <th
                    class="text-xs sm:text-sm sticky left-0 top-0 z-30 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 p-[0.5rem]"
                  >
                    台番号
                  </th>
                  <th
                    v-for="date in allDate"
                    :key="date"
                    class="text-xs sm:text-sm text-nowrap sticky left-0 top-0 z-20 max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[8rem] bg-gray-200 p-[0.5rem]"
                  >
                    {{ formattedDate(date) }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(data, slotNumber) in slotMachineData"
                  :key="slotNumber"
                >
                  <th class="text-xs sm:text-sm sticky left-0 z-10 max-h-[2rem] min-h-[2rem] min-w-[4rem] max-w-[6rem] bg-gray-200 text-gray-700 p-[0.5rem]">{{ slotNumber }}</th>
                  <td
                    v-for="date in allDate"
                    :key="date"
                    class="text-xs sm:text-sm text-nowrap max-h-[2rem] min-h-[2rem] min-w-[6rem] max-w-[6rem] p-[0.5rem] text-gray-700"
                  >
                    <template v-if="data[date]">
                      <div>{{ data[date]['game_count']?.toLocaleString('ja-JP') }}G</div>
                      <div :class="{ 'text-red-500': highlightColorForDifferenceCoins(data[date]['difference_coins']) }">
                        {{ formatDifferenceCoins(data[date]['difference_coins']?.toLocaleString('ja-JP')) }}枚
                      </div>
                      <div>B:{{ data[date]['big_bonus_count'] }}（{{ data[date]['big_bonus_probability'] }}）</div>
                      <div>R:{{ data[date]['regular_bonus_count'] }}（{{ data[date]['regular_bonus_probability'] }}）</div>
                      <div>合成確率:{{ data[date]['synthesis_probability'] }}</div>
                      <div>ART:{{ data[date]['art_count'] }}（{{ data[date]['art_probability'] }}）</div>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </template>
      <template v-else>
        {{ dataNotFoundMessage }}
      </template>

      <div class="my-8">
        <h2 class="text-lg sm:text-2xl font-bold">台番号ごとのスランプグラフ</h2>
      </div>

      <template
        v-if="Object.keys(slumpSlotNumbers).length > 0"
      >
        <div
          v-for="(data, slotNumber) in slumpSlotNumbers"
          class="mb-8"
        >
          <h3 class="text-base font-bold">{{ slotNumber }}番台：{{ data['slotName'] }}</h3>

          <div style="height:300px;">
            <Line :data=generateData(data) :options=generateOptions(data) />
          </div>
        </div>
      </template>
      <template v-else>
        {{ dataNotFoundMessage }}
      </template>
    </template>
  </BaseLayout>
</template>
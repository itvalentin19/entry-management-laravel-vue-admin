<script setup>
import AnalyticsCongratulations from "@/views/dashboard/AnalyticsCongratulations.vue";
import AnalyticsFinanceTabs from "@/views/dashboard/AnalyticsFinanceTab.vue";
import AnalyticsOrderStatistics from "@/views/dashboard/AnalyticsOrderStatistics.vue";
import AnalyticsProfitReport from "@/views/dashboard/AnalyticsProfitReport.vue";
import AnalyticsTotalRevenue from "@/views/dashboard/AnalyticsTotalRevenue.vue";
import AnalyticsTransactions from "@/views/dashboard/AnalyticsTransactions.vue";

// 👉 Images
import chart from "@images/cards/chart-success.png";
import card from "@images/cards/credit-card-primary.png";
import paypal from "@images/cards/paypal-error.png";
import wallet from "@images/cards/wallet-info.png";
import { onMounted } from "vue";
import ApiService from "@/services/api";
import { useStore } from "vuex";
import EntitiesTable from "@/layouts/components/EntitiesTable.vue";

const store = useStore();
const user = computed(() => store.getters.user);
const stats = ref(null);
// Reactive state
const state = reactive({
  current_page: 1,
  data: [],
  first_page_url: null,
  from: 1,
  last_page: 1,
  last_page_url: null,
  links: [],
  next_page_url: null,
  path: null,
  per_page: 10,
  prev_page_url: null,
  to: 2,
  total: 2,
});

const fetchStats = async () => {
  try {
    const res = await ApiService.getStats();
    stats.value = res.data;
    if (res.data?.entities) {
      Object.keys(res.data.entities).map((key) => {
        state[key] = res.data.entities[key];
      });
    }
  } catch (error) {}
};
onMounted(() => {
  if (user) {
    fetchStats();
  }
});
</script>

<template>
  <VRow>
    <!-- 👉 Congratulations -->
    <VCol cols="12">
      <AnalyticsCongratulations />
    </VCol>

    <VCol cols="12">
      <VCard title="Entities" prepend-icon="mdi-company">
        <EntitiesTable
          :data="state.data"
          @edit="handleEdit"
          @delete="handleDelete"
        />
        <VPagination
          v-model="state.current_page"
          :length="Math.ceil(state.total / state.per_page)"
        ></VPagination>
      </VCard>
      <VRow>
        <!-- 👉 Admins -->
        <!-- <VCol cols="12" md="4" sm="6" lg="3">
          <CardStatisticsVertical
            v-bind="{
              title: 'Admins',
              icon: 'mdi-users',
              stats: stats?.admins?.length,
            }"
          />
        </VCol> -->

        <!-- 👉 Users -->
        <!-- <VCol cols="12" md="4" sm="6" lg="3">
          <CardStatisticsVertical
            v-bind="{
              title: 'Users',
              icon: 'mdi-users',
              stats: stats?.users?.length,
            }"
          />
        </VCol> -->

        <!-- 👉 My Companies -->
        <!-- <VCol cols="12" md="4" sm="6" lg="3">
          <CardStatisticsVertical
            v-bind="{
              title: 'My Entities',
              icon: 'mdi-company',
              stats: stats?.own_companies?.length || 0,
            }"
          />
        </VCol> -->

        <!-- 👉 Others Companies -->
        <!-- <VCol cols="12" md="4" sm="6" lg="3">
          <CardStatisticsVertical
            v-bind="{
              title: 'Other Entities',
              icon: 'mdi-company',
              stats: stats?.other_companies?.length || 0,
            }"
          />
        </VCol> -->
      </VRow>
    </VCol>
  </VRow>
</template>

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

const store = useStore();
const user = computed(() => store.getters.user);
const stats = ref(null);

const fetchStats = async () => {
  try {
    const res = await ApiService.getStats();
    stats.value = res.data;
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
      <VRow>
        <!-- 👉 Admins -->
        <VCol cols="12" sm="4" v-if="user?.admin">
          <CardStatisticsVertical
            v-bind="{
              title: 'Admins',
              image: chart,
              stats: stats?.admins?.length,
            }"
          />
        </VCol>

        <!-- 👉 Users -->
        <VCol cols="12" sm="4" v-if="user?.admin">
          <CardStatisticsVertical
            v-bind="{
              title: 'Users',
              image: chart,
              stats: stats?.users?.length,
            }"
          />
        </VCol>

        <!-- 👉 Companies -->
        <VCol cols="12" sm="4">
          <CardStatisticsVertical
            v-bind="{
              title: 'My Companies',
              image: wallet,
              stats: stats?.own_companies?.length || 0,
            }"
          />
        </VCol>
      </VRow>
    </VCol>
  </VRow>
</template>

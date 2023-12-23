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
import { useToast } from "vue-toastification";
import { useRouter } from "vue-router";

const router = useRouter();
const toast = useToast();
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

const dialog = ref(false);
let ownerIdToDelete = ref(null);
const loaded = ref(false);
const loading = ref(false);
const searchText = ref("");
const sort = reactive({
  field: "id",
  order: "asc",
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

const handleView = (id) => {
  router.push("entities/entity/" + id);
};

const handleEdit = (id) => {
  router.push("entities/entity?id=" + id);
};

const handleDelete = (id) => {
  console.log(id);
  ownerIdToDelete.value = id;
  dialog.value = true;
  // Logic to handle user deletion
};
const handleSort = ({ field, order }) => {
  sort.field = field;
  sort.order = order;
  searchEntities();
};

// Search entities method
async function searchEntities() {
  loading.value = true;

  const params =
    "page=" +
    state.current_page +
    "&search=" +
    searchText.value +
    "&field=" +
    sort.field +
    "&order=" +
    sort.order;
  const response = await ApiService.getEntities(params);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
  loading.value = false;
}

const confirmDelete = async () => {
  if (ownerIdToDelete.value) {
    try {
      // Perform the delete action
      await ApiService.deleteEntity(ownerIdToDelete.value);
      // Show success message
      toast.success("Entity successfully deleted.");
      searchEntities();
      // Reset ownerIdToDelete for future deletions
      ownerIdToDelete.value = null;
    } catch (error) {
      // Handle error
      const errorMsg =
        error.response?.data?.message ||
        "Failed to delete the user. Please try again later.";
      toast.error(errorMsg);
      console.error(error);
    } finally {
      // Close the dialog regardless of the outcome
      dialog.value = false;
    }
  }
};

const handleReport = async (id) => {
  try {
    toast.info(
      "Your entity report is currently being generated. Please stand by for the download prompt."
    );

    const res = await ApiService.downloadReport(id);
    const fileURL = window.URL.createObjectURL(new Blob([res.data]));
    // Create a link element, hide it, direct it towards the blob, and then 'click' it programatically
    const fileLink = document.createElement("a");
    fileLink.href = fileURL;
    fileLink.setAttribute("download", "report-" + id + ".pdf"); // or any other extension
    document.body.appendChild(fileLink);
    fileLink.click();

    toast.success("Report generated successfully.");
    // Clean up and remove the link
    fileLink.parentNode.removeChild(fileLink);
    window.URL.revokeObjectURL(fileURL);
  } catch (error) {}
};

const handleSearch = () => {
  state.current_page = 1;
  searchEntities();
};

const handleKeyPress = (e) => {
  if (e.key === "Enter") {
    searchEntities();
  }
};
onMounted(() => {
  if (user) {
    fetchStats();
    searchEntities();
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
      <VCard>
        <VCardTitle>
          <VRow>
            <VCol cols="12" sm="6">
              <div class="d-flex align-center gap-2">
                <VIcon icon="mdi-company" color="secondary" size="24" />
                <h3>Entities</h3>
              </div>
            </VCol>
            <VCol cols="12" sm="6">
              <v-text-field
                :loading="loading"
                v-model="searchText"
                density="compact"
                variant="solo"
                label="Search Entities"
                append-inner-icon="mdi-magnify"
                single-line
                hide-details
                @click:append-inner="handleSearch"
                @keypress="handleKeyPress"
              ></v-text-field>
            </VCol>
          </VRow>
        </VCardTitle>
        <EntitiesTable
          :data="state.data"
          @edit="handleEdit"
          @delete="handleDelete"
          @sort="handleSort"
          @report="handleReport"
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

  <VDialog v-model="dialog" width="500">
    <VCard>
      <VCardTitle class="headline">Confirm Deletion</VCardTitle>
      <VCardText> Are you sure you want to delete this entity? </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="dialog = false">Cancel</VBtn>
        <VBtn color="red darken-1" text @click="confirmDelete">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

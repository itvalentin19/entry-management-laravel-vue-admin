<script setup>
import { ref, onMounted, reactive, watch } from "vue";
import ApiService from "@/services/api";
import UsersTable from "@/layouts/components/UsersTable.vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useStore } from "vuex";

const router = useRouter();
const toast = useToast();
const store = useStore();
const _user = computed(() => store.getters.user);
const loaded = ref(false);
const loading = ref(false);
const searchText = ref("");
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
let userIdToDelete = ref(null);
const sort = reactive({
  field: "id",
  order: "asc",
});

// Fetch users method
async function fetchUsers() {
  const params =
    "page=" +
    state.current_page +
    "&field=" +
    sort.field +
    "&order=" +
    sort.order;

  const response = await ApiService.getUsers(params);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
}

// Navigate to User Create component
function goToCreateUser() {
  router.push("/users/user");
}

// Search users method
async function searchUsers() {
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

  const response = await ApiService.getUsers(params);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
  loading.value = false;
}

const handleEdit = (id) => {
  router.push("users/user?id=" + id);
};

const handleDelete = (id) => {
  userIdToDelete.value = id;
  dialog.value = true;
};

const handleSort = ({ field, order }) => {
  sort.field = field;
  sort.order = order;
  searchUsers();
};

const confirmDelete = async () => {
  if (userIdToDelete.value) {
    try {
      // Perform the delete action
      await ApiService.deleteUser(userIdToDelete.value);
      // Show success message
      toast.success("User successfully deleted.");

      // Reset userIdToDelete for future deletions
      userIdToDelete.value = null;
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

const handleSearch = () => {
  state.current_page = 1;
  searchUsers();
};

const handleKeyPress = (e) => {
  if (e.key === "Enter") {
    state.current_page = 1;
    searchUsers();
  }
};

watch(
  () => state.current_page,
  (newPage, oldPage) => {
    if (newPage !== oldPage) {
      searchUsers();
    }
  },
  { immediate: false } // Set to true if you also want to run on initial setup
);

// Mounted lifecycle hook
onMounted(() => {
  fetchUsers();
});
</script>

<template>
  <div class="d-flex align-center">
    <VBtn variant="text" class="ms-n3 mb-3" to="/">
      <VIcon icon="bx-arrow-back" />
      Go To Home
    </VBtn>
  </div>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between gap-10">
        <v-btn
          color="primary"
          @click="goToCreateUser"
          v-if="_user?.admin == true"
          >Create User</v-btn
        >
        <v-text-field
          :loading="loading"
          v-model="searchText"
          density="compact"
          variant="solo"
          label="Search Users"
          append-inner-icon="mdi-magnify"
          single-line
          hide-details
          @click:append-inner="handleSearch"
          @keypress="handleKeyPress"
        ></v-text-field>
      </div>
    </VCol>
  </VRow>
  <VRow>
    <VCol cols="12">
      <VCard title="Users" prepend-icon="mdi-users">
        <UsersTable
          :user-list="state.data"
          @edit="handleEdit"
          @delete="handleDelete"
          @sort="handleSort"
        />
        <VPagination
          v-model="state.current_page"
          :length="Math.ceil(state.total / state.per_page)"
        ></VPagination>
      </VCard>
    </VCol>
  </VRow>
  <VDialog v-model="dialog" width="500">
    <VCard>
      <VCardTitle class="headline">Confirm Deletion</VCardTitle>
      <VCardText> Are you sure you want to delete this user? </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="dialog = false">Cancel</VBtn>
        <VBtn color="red darken-1" text @click="confirmDelete">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

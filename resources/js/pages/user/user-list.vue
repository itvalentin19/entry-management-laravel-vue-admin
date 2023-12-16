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

// Fetch users method
async function fetchUsers() {
  const params = "page=" + state.current_page;

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
  const params = "page=" + state.current_page + "&search=" + searchText.value;

  const response = await ApiService.getUsers(params);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
  loading.value = false;
}

const handleEdit = (user) => {
  console.log(user);
  // Logic to handle user editing
  router.push("users/user?id=" + user.id);
};

const handleDelete = (userId) => {
  console.log(userId);
  userIdToDelete.value = userId;
  dialog.value = true;
  // Logic to handle user deletion
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

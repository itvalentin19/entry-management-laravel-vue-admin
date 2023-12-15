<script setup>
import { ref, onMounted, reactive } from "vue";
import ApiService from "@/services/api";
import EntitiesTable from "@/layouts/components/EntitiesTable.vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";

const router = useRouter();
const toast = useToast();

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

// Fetch users method
async function fetchEntities() {
  const params = "page=" + state.current_page;
  const response = await ApiService.getEntities(params);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
}

// Navigate to Entity Create component
function goToCreateUser() {
  router.push("/entities/entity");
}

// Search users method
async function searchEntities() {
  if (searchText.value) {
    const params = "page=1&search=" + searchText.value;
    const response = await ApiService.getEntities(params);
    if (response.data) {
      Object.keys(response.data).map((key) => {
        state[key] = response.data[key];
      });
    }
  }
}

const handleEdit = (id) => {
  router.push("entities/entity?id=" + id);
};

const handleDelete = (id) => {
  console.log(id);
  ownerIdToDelete.value = id;
  dialog.value = true;
  // Logic to handle user deletion
};
const confirmDelete = async () => {
  if (ownerIdToDelete.value) {
    try {
      // Perform the delete action
      await ApiService.deleteEntity(ownerIdToDelete.value);
      // Show success message
      toast.success("Entity successfully deleted.");
      fetchEntities();
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

const handleChangeSearchText = (e) => {
  console.log(e.target.value);
  searchText.value = e.target.value;
};

const handleKeyPress = (e) => {
  if (e.key === "Enter") {
    searchEntities();
  }
};

// Mounted lifecycle hook
onMounted(() => {
  fetchEntities();
});
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex align-center justify-space-between gap-10">
        <v-btn color="primary" @click="goToCreateUser">Create Entity</v-btn>
        <v-text-field
          :loading="loading"
          v-model="searchText"
          density="compact"
          variant="solo"
          label="Search Entities"
          append-inner-icon="mdi-magnify"
          single-line
          hide-details
          @click:append-inner="searchEntities"
          @keypress="handleKeyPress"
        ></v-text-field>
      </div>
    </VCol>
  </VRow>
  <VRow>
    <VCol cols="12">
      <VCard title="Entities">
        <EntitiesTable
          :data="state.data"
          @edit="handleEdit"
          @delete="handleDelete"
        />
      </VCard>
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

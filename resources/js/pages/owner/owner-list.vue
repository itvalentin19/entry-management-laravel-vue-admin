<script setup>
import { ref, onMounted, reactive } from "vue";
import ApiService from "@/services/api";
import OwnersTable from "@/layouts/components/OwnersTable.vue";
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

// Fetch users method
async function fetchOwners() {
  const response = await ApiService.getOwners(state.current_page);
  if (response.data) {
    Object.keys(response.data).map((key) => {
      state[key] = response.data[key];
    });
  }
}

// Navigate to User Create component
function goToCreateUser() {
  router.push("/owners/owner");
}

// Search users method
async function searchOwners(searchTerm) {
  const response = await ApiService.get(`/owners?search=${searchTerm}`);
  state.users = response.data;
}

const handleEdit = (id) => {
  router.push("owners/owner?id=" + id);
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
      await ApiService.deleteOwner(ownerIdToDelete.value);
      // Show success message
      toast.success("User successfully deleted.");
      fetchOwners();
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

// Mounted lifecycle hook
onMounted(() => {
  fetchOwners();
});
</script>

<template>
  <VRow>
    <VCol cols="12">
      <v-btn color="primary" @click="goToCreateUser">Create Entity Owner</v-btn>
    </VCol>
  </VRow>
  <VRow>
    <VCol cols="12">
      <VCard title="Owners">
        <OwnersTable
          :user-list="state.data"
          @edit="handleEdit"
          @delete="handleDelete"
        />
      </VCard>
    </VCol>
  </VRow>
  <VDialog v-model="dialog" width="500">
    <VCard>
      <VCardTitle class="headline">Confirm Deletion</VCardTitle>
      <VCardText> Are you sure you want to delete this owner? </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="dialog = false">Cancel</VBtn>
        <VBtn color="red darken-1" text @click="confirmDelete">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

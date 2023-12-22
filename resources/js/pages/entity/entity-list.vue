<script setup>
import { ref, onMounted, reactive, watch } from "vue";
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
const sort = reactive({
  field: "id",
  order: "asc",
});

// Fetch users method
async function fetchEntities() {
  const params =
    "page=" +
    state.current_page +
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
}

// Navigate to Entity Create component
function goToCreateUser() {
  router.push("/entities/entity");
}

// Search users method
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

const handleSearch = () => {
  state.current_page = 1;
  searchEntities();
};

const handleKeyPress = (e) => {
  if (e.key === "Enter") {
    searchEntities();
  }
};

function prepareDataForExport(entities) {
  return entities.map((entity) => {
    // Remove unnecessary fields
    const { owner_ids, document_ids, is_deleted, user_id, ...cleanedEntity } =
      entity;

    // Concatenate owner names
    if (entity.owners && entity.owners.length) {
      cleanedEntity.owners = entity.owners
        .map((owner) => `${owner.first_name} ${owner.last_name}`)
        .join(", ");
    }

    // Extract document URLs
    if (entity.documents && entity.documents.length) {
      cleanedEntity.documents = entity.documents
        .map((doc) => doc.url)
        .join(", ");
    }

    return cleanedEntity;
  });
}

function downloadCSV() {
  const filename = "entities.csv";
  const jsonData = prepareDataForExport(state.data);
  // Create CSV headers from the keys of the first object (assuming all objects have the same keys)
  const csvHeaders = Object.keys(jsonData[0]).join(",");

  // Map each JSON object to a CSV string
  const csvRows = jsonData.map((row) =>
    Object.values(row)
      .map((value) =>
        typeof value === "string" ? `"${value.replace(/"/g, '""')}"` : value
      )
      .join(",")
  );

  // Combine headers and rows
  const csvString = [csvHeaders, ...csvRows].join("\n");

  // Create a Blob and trigger a download
  const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
  URL.revokeObjectURL(link.href);
}
watch(
  () => state.current_page,
  (newPage, oldPage) => {
    if (newPage !== oldPage) {
      searchEntities();
    }
  },
  { immediate: false } // Set to true if you also want to run on initial setup
);
// Mounted lifecycle hook
onMounted(() => {
  fetchEntities();
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
          @click:append-inner="handleSearch"
          @keypress="handleKeyPress"
        ></v-text-field>
      </div>
    </VCol>
  </VRow>
  <VRow>
    <VCol cols="12">
      <VCard title="Entities" prepend-icon="mdi-company">
        <template v-slot:append>
          <v-btn icon="mdi-download" @click="downloadCSV"></v-btn>
        </template>
        <EntitiesTable
          :data="state.data"
          @view="handleView"
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
      <VCardText> Are you sure you want to delete this entity? </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="dialog = false">Cancel</VBtn>
        <VBtn color="red darken-1" text @click="confirmDelete">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

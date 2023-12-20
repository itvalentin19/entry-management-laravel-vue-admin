<script setup>
import ApiService from "@/services/api";
import { ref } from "vue";
import { useToast } from "vue-toastification";

const entityFile = ref(null);
const uploading = ref(false);
const toast = useToast();

function onFileChange(event) {
  const file = event.target.files[0];
  if (!file) return;
  entityFile.value = file;
}

async function handleUpload() {
  const file = entityFile.value;
  if (!file) return;
  uploading.value = true; // Start loading animation

  let formData = new FormData();
  formData.append("file", file);

  try {
    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    const response = await ApiService.entityUpload(formData, config);
    toast.success(response.data?.message || "Entities added successfully!");
    console.log(response.data);
    // Handle success - maybe refresh data or show a success message
  } catch (error) {
    if (error.response) {
      // Handle HTTP errors
      const errorMsg =
        error.response.data.message ||
        "An error occurred while creating the entity.";
      toast.error(errorMsg);
    } else if (error.request) {
      // The request was made but no response was received
      toast.error("No response from server. Please try again later.");
    } else {
      // Something else happened in setting up the request
      toast.error("Error: " + error.message);
    }
    console.error(error);
  } finally {
    uploading.value = false; // Stop loading animation
    entityFile.value = null; // Clear loaded file
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard title="Upload Sheet">
        <VDivider />
        <VCardText>
          <!-- Loading Indicator -->
          <VDialog v-model="uploading" persistent hide-overlay width="300">
            <VCard>
              <VCardText class="text-center">
                Uploading and processing...
                <VProgressCircular
                  indeterminate
                  color="primary"
                ></VProgressCircular>
              </VCardText>
            </VCard>
          </VDialog>
          <VAlert
            title="Bulk Creation Instructions"
            icon="mdi-info"
            color="info"
          >
            <span>
              👉 After completing the bulk creation process, you will need to
              manually add documents for each entity.
            </span>
            <br />
            <span>
              👉 You need to manually enter the owner IDs in the owners field
              before uploading the sheet file. Please refer to the
              <a href="/owners" target="_blank">Owners</a>
              table to create or find the appropriate owner.
            </span>
            <br />
            <a href="/entities_sample_sheet.xlsx" download="Sample_Sheet.xlsx">
              👉 Download the Sample Excel Sheet
            </a>
          </VAlert>
          <!-- 👉 Document -->
          <VCol cols="12" md="6">
            <VFileInput
              clearable
              label="Upload Entities Document"
              accept=".xls,.xlsx"
              variant="outlined"
              show-size
              chips
              multiple
              @change="onFileChange"
            ></VFileInput>
          </VCol>
          <VCol cols="12" class="d-flex flex-wrap gap-4">
            <VBtn @click="handleUpload" :disabled="!entityFile"> Upload </VBtn>
          </VCol>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

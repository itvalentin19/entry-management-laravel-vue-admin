<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import ApiService from "@/services/api";
import { useToast } from "vue-toastification";
import { useRoute, useRouter } from "vue-router";
import VueDatePicker from "@vuepic/vue-datepicker";
import { useStore } from "vuex";
import { onMounted, watch } from "vue";

const accountData = {
  first_name: null,
  last_name: null,
  email: null,
  phone: null,
  address1: null,
  address2: null,
  city: null,
  state: null,
  zip: null,
  country: "USA",
  ownership_stake: null,
  document_type: "DL",
  document_expiration: null,
  document: null,
  kyc_document: null,
};
const route = useRoute();
const store = useStore();
const _user = computed(() => store.getters.user);
const ownerId = ref(null);
const refInputEl = ref();
const accountDataLocal = ref(structuredClone(accountData));
const toast = useToast();
const router = useRouter();
const menu = ref(true);

const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData);
};

const addDocument = (file) => {
  const { files } = file.target;
  console.log(files[0]);
  if (files && files.length) {
    accountDataLocal.value.document = files[0];
  }
};

// reset avatar image
const deleteDocument = () => {
  accountDataLocal.value.document = null;
};

// handle save owner information

const onCreate = async () => {
  try {
    const formData = new FormData();

    console.log(accountDataLocal.value);
    console.log(document.value);
    // Append owner data
    for (const key in accountDataLocal.value) {
      if (key !== "document") {
        formData.append(key, accountDataLocal.value[key]);
      }
    }

    // Append avatar image if it's a File object (not a URL string)
    if (accountDataLocal.value.document) {
      formData.append("document", accountDataLocal.value.document);
    }

    let res;

    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    if (ownerId.value) {
      // Update existing Owner
      res = await ApiService.updateOwner(ownerId.value, formData, config);
      toast.success("Owner updated successfully!");
    } else {
      // Create new Owner
      res = await ApiService.createOwner(formData, config);
      toast.success("Owner created successfully!");
    }

    router.push("/owners");
  } catch (error) {
    if (error.response) {
      // Handle HTTP errors
      const errorMsg =
        error.response.data.message ||
        "An error occurred while creating the owner.";
      toast.error(errorMsg);
    } else if (error.request) {
      // The request was made but no response was received
      toast.error("No response from server. Please try again later.");
    } else {
      // Something else happened in setting up the request
      toast.error("Error: " + error.message);
    }
    console.error(error);
  }
};

watch(
  route,
  async (currentRoute) => {
    ownerId.value = currentRoute.query.id || null;
    if (ownerId?.value) {
      try {
        const response = await ApiService.getOwner(ownerId.value);
        accountDataLocal.value = response.data;
      } catch (error) {
        toast.error("Failed to load owner data.");
        router.push("/owners/owner");
        console.error(error);
      }
    } else {
      resetForm();
    }
    // if (_user.value?.admin == true) {
    // } else {
    //   router.push("/owners");
    // }
  },
  { immediate: true }
);
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
      <VCard :title="ownerId ? 'Edit Owner Detail' : 'Add Owner Detail'">
        <VDivider />

        <VCardText>
          <!-- 👉 Form -->
          <VForm class="mt-6">
            <VRow>
              <!-- 👉 First Name -->
              <VCol md="6" cols="12">
                <VTextField
                  v-model="accountDataLocal.first_name"
                  placeholder="John"
                  label="First Name"
                />
              </VCol>

              <!-- 👉 Last Name -->
              <VCol md="6" cols="12">
                <VTextField
                  v-model="accountDataLocal.last_name"
                  placeholder="Doe"
                  label="Last Name"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.email"
                  label="E-mail"
                  placeholder="johndoe@gmail.com"
                  type="email"
                />
              </VCol>

              <!-- 👉 Phone -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.phone"
                  label="Phone Number"
                  placeholder="+1 (917) 543-9876"
                />
              </VCol>

              <!-- 👉 Address 1 -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.address1"
                  label="Address 1"
                  placeholder="5645 Coral Ridge Drive"
                />
              </VCol>
              <!-- 👉 Address 2 -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.address2"
                  label="Address 2"
                  placeholder="Suite 410"
                />
              </VCol>
              <!-- 👉 Address 2 -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.city"
                  label="City"
                  placeholder="Coral Springs"
                />
              </VCol>

              <!-- 👉 State -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.state"
                  label="State"
                  placeholder="FL"
                />
              </VCol>

              <!-- 👉 Zip Code -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.zip"
                  label="Zip Code"
                  placeholder="10001"
                />
              </VCol>

              <!-- 👉 Country -->
              <VCol cols="12" md="6">
                <VSelect
                  v-model="accountDataLocal.country"
                  label="Country"
                  :items="['USA', 'Canada', 'UK', 'India', 'Australia']"
                  placeholder="Select Country"
                />
              </VCol>

              <!-- 👉 Ownership Stack -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.ownership_stake"
                  label="Ownership Stake"
                  placeholder="Ownership"
                />
              </VCol>

              <!-- 👉 Document Type -->
              <VCol cols="12" md="6">
                <VSelect
                  v-model="accountDataLocal.document_type"
                  label="Document Type"
                  :items="['DL', 'Passport']"
                  placeholder="Select Country"
                />
              </VCol>

              <!-- 👉 Document Expiration -->
              <VCol cols="12" md="6">
                <VueDatePicker
                  v-model="accountDataLocal.document_expiration"
                  placeholder="Document Expiration Date"
                ></VueDatePicker>
              </VCol>
              <!-- 👉 Document -->
              <VCol cols="12" md="6">
                <VFileInput
                  :v-model="accountDataLocal.document"
                  clearable
                  label="Upload Document"
                  accept=".pdf"
                  variant="outlined"
                  show-size
                  @change="addDocument"
                ></VFileInput>
                <a
                  v-if="accountDataLocal.kyc_document"
                  :href="accountDataLocal.kyc_document.url"
                  target="_blank"
                  >Current Document</a
                >
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn @click="onCreate">
                  {{ ownerId ? "Update Owner" : "Create Owner" }}
                </VBtn>

                <VBtn
                  color="secondary"
                  variant="tonal"
                  type="reset"
                  @click.prevent="resetForm"
                >
                  Reset
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

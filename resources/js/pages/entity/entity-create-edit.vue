<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import ApiService from "@/services/api";
import { useToast } from "vue-toastification";
import { useRoute, useRouter } from "vue-router";
import { onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import EntityUpload from "@/layouts/components/EntityUpload.vue";
import states from "@/pages/entity/us_states.json";

const route = useRoute();
const toast = useToast();
const router = useRouter();

const accountData = {
  firm_name: null,
  doing_business_as: null,
  entity_name: null,
  address_1: null,
  address_2: null,
  city: null,
  state: null,
  zip: null,
  country: "USA",
  type: null,
  contact_first_name: null,
  contact_last_name: null,
  contact_phone: null,
  contact_email: null,
  services: null,
  annual_fees: null,
  first_tax_year: null,
  directors: null,
  ein_number: null,
  form_id: false,
  date_created: null,
  date_signed: null,
  person: null,
  jurisdiction: null,
  owners: null,
  documents: null,
  files: [],
  notes: null,
  ref_by: null,
};
const accountDataLocal = ref(structuredClone(accountData));
const serviceItem = {
  service: null,
  fee: null,
};
const serviceFeeData = [serviceItem];
const serviceFeeDataLocal = ref(structuredClone(serviceFeeData));

const directorItem = {
  first_name: null,
  last_name: null,
  email: null,
  phone: null,
};
const directorListInitial = [directorItem];
const directorList = ref(structuredClone(directorListInitial));

const services = ref([]);
const types = ref([]);
const refers = ref([]);
const users = ref([]);
const owners = ref([]);
const entityId = ref(null);
const refInputEl = ref();
const menu = ref(true);
const dialog = ref(false);
const documentIdToDelete = ref(null);

const activeTab = ref(0);
// tabs
const tabs = [
  {
    title: "Add Entity",
    icon: "bx-edit",
    tab: "add_entity",
  },
  {
    title: "Bulk Upload",
    icon: "bx-upload",
    tab: "bulk_upload",
  },
  // {
  //   title: "Notifications",
  //   icon: "bx-bell",
  //   tab: "notification",
  // },
];
const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData);
  serviceFeeDataLocal.value = structuredClone(serviceFeeData);
};

const addDocument = (event) => {
  const { files } = event.target;
  console.log(files[0]);
  if (files && files.length) {
    accountDataLocal.value.files = [];
    Object.keys(files).forEach((key) => {
      const file = files[key];
      accountDataLocal.value.files.push(file);
    });
  }
};

// handle save entity information

const onCreate = async () => {
  try {
    const formData = new FormData();

    // Append entity data
    for (const key in accountDataLocal.value) {
      if (key !== "files") {
        formData.append(key, accountDataLocal.value[key]);
      }
    }
    let services = [];
    let annual_fees = [];
    let validate = true;
    for (const item of serviceFeeDataLocal.value) {
      console.log(item);
      console.log(item.service);
      console.log(item.fee);
      if (item.service) {
        services.push(item.service.trim());
        // if (item.fee == null) {
        //   toast.error(
        //     "Please input the annual fee for the corresponding service name."
        //   );
        //   validate = false;
        //   break;
        // }
        annual_fees.push(item.fee);
      }
    }
    if (!validate) return;
    formData.append("services", services);
    formData.append("annual_fees", annual_fees);

    if (accountDataLocal.value.directors) {
      formData.append("director_list", JSON.stringify(directorList.value));
    }

    console.log(accountDataLocal.value.files);
    if (accountDataLocal.value.files) {
      // Append each document file to formData
      accountDataLocal.value.files.forEach((document, index) => {
        formData.append(`files[]`, document);
      });
    }

    let res;

    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    if (entityId.value) {
      // Update existing Entity
      res = await ApiService.updateEntity(entityId.value, formData, config);
      toast.success("Entity updated successfully!");
    } else {
      // Create new Entity
      res = await ApiService.createEntity(formData, config);
      toast.success("Entity created successfully!");
    }

    router.push("/entities");
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
  }
};

watch(
  route,
  async (currentRoute) => {
    entityId.value = currentRoute.query.id || null;
    if (entityId?.value) {
      try {
        const response = await ApiService.getEntity(entityId.value);
        accountDataLocal.value = response.data;
        const annual_fees = response.data.annual_fees?.split(",") || [];
        serviceFeeDataLocal.value = [];
        response.data.services?.split(",").forEach((item, index) => {
          const serviceItem = {
            service: item,
            fee: annual_fees[index] || null,
          };
          serviceFeeDataLocal.value.push(serviceItem);
        });
        accountDataLocal.value.services = null;
        accountDataLocal.value.annual_fees = null;
        accountDataLocal.value.ref_by = response.data.ref_by?.split(",") || [];
        // Update Director List
        directorList.value = response.data.director_list || [];
      } catch (error) {
        toast.error("Failed to load entity data.");
        router.push("/entities/entity");
        console.error(error);
      }
    } else {
      resetForm();
    }
  },
  { immediate: true }
);

const addNewServiceFee = async () => {
  const newServiceFeeItem = structuredClone(serviceItem);
  serviceFeeDataLocal.value.push(newServiceFeeItem);
};

const addNewDirector = async () => {
  const newItem = structuredClone(directorItem);
  directorList.value.push(newItem);
};

const removeServiceFee = (index) => {
  serviceFeeDataLocal.value.splice(index, 1);
};

const removeDirector = (index) => {
  console.log(index);
  console.log(directorList.value);
  directorList.value.splice(index, 1);
};

const getProps = async () => {
  try {
    const res = await ApiService.getEntityProps();
    services.value = res.data.services;
    types.value = res.data.types;
    refers.value = res.data.refs;
    users.value = res.data.users;
    owners.value = res.data.owners.map((owner) => ({
      id: owner.id,
      name: owner.first_name + " " + owner.last_name,
    }));
    accountDataLocal.value.type = types.value?.[0] || null;
  } catch (error) {}
};

const removeDocument = (id) => {
  documentIdToDelete.value = id;
  dialog.value = true;
};

const confirmDelete = () => {
  accountDataLocal.value.document_ids =
    accountDataLocal.value.document_ids.filter(
      (item) => item != documentIdToDelete.value
    );
  accountDataLocal.value.documents = accountDataLocal.value.documents.filter(
    (item) => item.id !== documentIdToDelete.value
  );
  console.log(accountDataLocal.value.document_ids);
  console.log(accountDataLocal.value.documents);
  dialog.value = false;
  documentIdToDelete.value = null;
};

onMounted(() => {
  getProps();
});

const years = [
  "2020",
  "2021",
  "2022",
  "2023",
  "2024",
  "2025",
  "2026",
  "2027",
  "2028",
  "2029",
  "2030",
  "2031",
];
</script>

<template>
  <div class="d-flex align-center">
    <VBtn variant="text" class="ms-n3 mb-3" to="/">
      <VIcon icon="bx-arrow-back" />
      Go To Home
    </VBtn>
  </div>
  <VTabs v-model="activeTab" show-arrows>
    <VTab v-for="item in tabs" :key="item.icon" :value="item.tab">
      <VIcon size="20" start :icon="item.icon" />
      {{ item.title }}
    </VTab>
  </VTabs>
  <VDivider />
  <VWindow v-model="activeTab" class="mt-5 disable-tab-transition">
    <VWindowItem value="add_entity">
      <VRow>
        <VCol cols="12">
          <VCard :title="entityId ? 'Edit Entity Detail' : 'Add Entity Detail'">
            <VDivider />

            <VCardText>
              <!-- 👉 Form -->
              <VForm class="mt-6">
                <VRow>
                  <!-- 👉 Firm Name -->
                  <VCol md="6" cols="12">
                    <VTextField
                      v-model="accountDataLocal.firm_name"
                      placeholder="Vauban Technologies Ltd"
                      label="Firm Name"
                    />
                  </VCol>
                  <!-- 👉 Doing Business Name -->
                  <VCol md="6" cols="12">
                    <VTextField
                      v-model="accountDataLocal.doing_business_as"
                      placeholder="Vauban Ltd"
                      label="Doing Business Name"
                    />
                  </VCol>

                  <!-- 👉 Entity Name -->
                  <VCol md="6" cols="12">
                    <VTextField
                      v-model="accountDataLocal.entity_name"
                      placeholder="Nano I a Series of S5V Coinvest"
                      label="Entity Name"
                    />
                  </VCol>

                  <!-- 👉 Address 1 -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.address_1"
                      label="Address 1"
                      placeholder="5645 Coral Ridge Drive"
                    />
                  </VCol>
                  <!-- 👉 Address 2 -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.address_2"
                      label="Address 2"
                      placeholder="Suite 410"
                    />
                  </VCol>
                  <!-- 👉 City -->
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

                  <!-- 👉 Type -->
                  <VCol cols="12" md="6">
                    <VCombobox
                      v-model="accountDataLocal.type"
                      label="Type"
                      :items="types"
                      placeholder="Select Type"
                    />
                  </VCol>

                  <!-- 👉 Person -->
                  <VCol cols="12" md="6">
                    <VAutocomplete
                      v-model="accountDataLocal.person"
                      label="Person"
                      :items="users"
                      clearable
                      placeholder="Select Person"
                    />
                  </VCol>

                  <!-- 👉 First Tax Year -->
                  <VCol cols="12" md="6">
                    <VSelect
                      v-model="accountDataLocal.first_tax_year"
                      label="First Tax Year"
                      :items="years"
                      placeholder="Select Year"
                    />
                  </VCol>
                  <!-- 👉 EIN Number -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.ein_number"
                      label="EIN Number"
                      placeholder="93-4459228"
                    />
                  </VCol>
                  <!-- 👉 Jurisdiction -->
                  <VCol cols="12" md="6">
                    <VAutocomplete
                      v-model="accountDataLocal.jurisdiction"
                      label="Jurisdiction"
                      placeholder=""
                      item-title="name"
                      item-value="name"
                      :items="states"
                    />
                  </VCol>
                  <!-- 👉 Created Date -->
                  <VCol cols="12" md="6">
                    <VueDatePicker
                      v-model="accountDataLocal.date_created"
                      placeholder="Created Date"
                    ></VueDatePicker>
                  </VCol>

                  <!-- 👉 Signed Date -->
                  <VCol cols="12" md="6">
                    <VueDatePicker
                      v-model="accountDataLocal.date_signed"
                      placeholder="Signed Date"
                    ></VueDatePicker>
                  </VCol>

                  <!-- 👉 Owners -->
                  <VCol cols="12" md="6">
                    <VAutocomplete
                      v-model="accountDataLocal.owner_ids"
                      label="Owners"
                      multiple
                      item-title="name"
                      item-value="id"
                      :items="owners"
                      placeholder="Select Owners"
                    />
                  </VCol>
                  <!-- 👉 Contact First Name -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.contact_first_name"
                      label="Contact First Name"
                      placeholder="Daniel"
                    />
                  </VCol>

                  <!-- 👉 Contact Last Name -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.contact_last_name"
                      label="Contact Last Name"
                      placeholder="Strachman"
                    />
                  </VCol>

                  <!-- 👉 Contact Email -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.contact_email"
                      label="Contact Email"
                      placeholder="johndoe@gmail.com"
                      type="email"
                    />
                  </VCol>

                  <!-- 👉 Contact Phone -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.contact_phone"
                      label="Contact Phone"
                      placeholder="+1 (917) 543-9876"
                    />
                  </VCol>

                  <VDivider />
                  <VCol cols="12">
                    <p>
                      Services
                      <VIcon icon="bx-plus-circle" @click="addNewServiceFee" />
                    </p>
                    <!-- 👉 Services -->
                    <VRow
                      v-for="(item, index) in serviceFeeDataLocal"
                      :key="index"
                    >
                      <VCol cols="12" md="6">
                        <d class="d-flex align-center justify-center">
                          <VIcon
                            icon="bx-minus-circle"
                            color="#c93903"
                            @click="removeServiceFee(index)"
                          />
                          <VCombobox
                            v-model="item.service"
                            label="Service"
                            :items="services"
                            placeholder="Select Type"
                          />
                        </d>
                      </VCol>

                      <!-- 👉 Contact First Name -->
                      <VCol cols="12" md="6">
                        <VTextField
                          v-model="item.fee"
                          label="Annual Fee"
                          prepend-inner-icon="bx-dollar"
                          placeholder="129"
                          type="number"
                        />
                      </VCol>
                    </VRow>
                  </VCol>

                  <VDivider />

                  <!-- 👉 Directors -->
                  <VCol cols="12">
                    <p class="directors">
                      <VCheckbox
                        v-model="accountDataLocal.directors"
                        label="Directors"
                        style="width: max-content"
                      />
                      <VIcon
                        icon="bx-plus-circle"
                        v-if="accountDataLocal.directors"
                        @click="addNewDirector"
                      />
                    </p>
                  </VCol>
                  <VCol cols="12" v-if="accountDataLocal.directors">
                    <VCard
                      v-for="(director, index) in directorList"
                      :key="index"
                      class="director-card"
                    >
                      <VIcon
                        class="item-delete-button"
                        icon="bx-minus-circle"
                        color="#c93903"
                        @click="removeDirector(index)"
                      />
                      <VCardText>
                        <VRow>
                          <!-- 👉 Contact First Name -->
                          <VCol cols="12" md="3">
                            <VTextField
                              v-model="director.first_name"
                              label="First Name"
                              placeholder="Daniel"
                            />
                          </VCol>

                          <!-- 👉 Last Name -->
                          <VCol cols="12" md="3">
                            <VTextField
                              v-model="director.last_name"
                              label="Last Name"
                              placeholder="Strachman"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="3">
                            <VTextField
                              v-model="director.email"
                              label="Email"
                              placeholder="johndoe@gmail.com"
                              type="email"
                            />
                          </VCol>

                          <!-- 👉 Phone -->
                          <VCol cols="12" md="3">
                            <VTextField
                              v-model="director.phone"
                              label="Phone"
                              placeholder="+1 (917) 543-9876"
                            />
                          </VCol>
                        </VRow>
                      </VCardText>
                    </VCard>
                  </VCol>
                  <VDivider />

                  <!-- 👉 Directors -->
                  <VCol cols="12">
                    <VCheckbox
                      v-model="accountDataLocal.form_id"
                      label="Form ID"
                    />
                  </VCol>
                  <VDivider />

                  <!-- 👉 Document -->
                  <VCol cols="12" md="6">
                    <VFileInput
                      :v-model="accountDataLocal.files"
                      clearable
                      label="Upload Document"
                      accept=".pdf"
                      variant="outlined"
                      show-size
                      chips
                      multiple
                      @change="addDocument"
                    ></VFileInput>
                  </VCol>

                  <!-- 👉 Ref By -->
                  <VCol cols="12" md="6">
                    <VCombobox
                      v-model="accountDataLocal.ref_by"
                      label="Ref By"
                      multiple
                      :items="refers"
                    />
                  </VCol>

                  <VCol
                    cols="4"
                    md="3"
                    v-for="document in accountDataLocal.documents"
                    :key="document.id"
                  >
                    <iframe :src="document.url" class="pdf-preview"></iframe>
                    <a :href="document.url" target="_blank">View Document</a>
                    <VIcon
                      icon="bx-minus-circle"
                      color="#c93903"
                      @click="removeDocument(document.id)"
                    />
                  </VCol>

                  <!-- 👉 Notes -->
                  <VCol cols="12">
                    <VTextarea
                      v-model="accountDataLocal.notes"
                      label="Notes"
                      placeholder="..."
                    />
                  </VCol>

                  <!-- 👉 Form Actions -->
                  <VCol cols="12" class="d-flex flex-wrap gap-4">
                    <VBtn @click="onCreate">
                      {{ entityId ? "Update Entity" : "Create Entity" }}
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
    </VWindowItem>
    <VWindowItem value="bulk_upload">
      <EntityUpload />
    </VWindowItem>
  </VWindow>
  <VDialog v-model="dialog" width="500">
    <VCard>
      <VCardTitle class="headline">Confirm Deletion</VCardTitle>
      <VCardText> Are you sure you want to delete this document? </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="dialog = false">Cancel</VBtn>
        <VBtn color="red darken-1" text @click="confirmDelete">Delete</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
<style scoped>
.pdf-preview {
  width: 100%;
  height: 200px; /* Adjust as needed */
  border: none;
}
.item-delete-button {
  position: absolute;
  left: 0;
  top: 0;
}
.directors {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 0.5rem;
  margin-bottom: 0px;
}
.director-card {
  margin-top: 10px;
}
</style>

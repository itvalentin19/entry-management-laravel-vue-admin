<script setup>
import OwnersTable from "@/layouts/components/OwnersTable.vue";
import ApiService from "@/services/api";
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";

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
const services = ref([]);
const types = ref([]);
const refers = ref([]);
const users = ref([]);
const owners = ref([]);
const entityId = ref(null);
const refInputEl = ref();
const menu = ref(true);
const deleteOwnerDialog = ref(false);
const ownerIdToDelete = ref(null);
const loading = ref(false);
const serviceIdToDelete = ref(null);
const deleteServiceDialog = ref(false);
const documentIdToDelete = ref(null);
const deleteDocumentDialog = ref(false);

const activeTab = ref(0);
// tabs
const tabs = [
  {
    title: "Entity",
    icon: "bx-building",
    tab: "entity",
  },
  {
    title: "Owners",
    icon: "bx-group",
    tab: "owners",
  },
  {
    title: "Directors",
    icon: "bx-group",
    tab: "directors",
  },
  {
    title: "Registered Agent/Office",
    icon: "bx-group",
    tab: "registered_agents",
  },
  {
    title: "Services",
    icon: "bx-building-house",
    tab: "services",
  },
  {
    title: "Files",
    icon: "bx-file",
    tab: "files",
  },
];
const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData);
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

watch(
  route,
  async (currentRoute) => {
    console.log(currentRoute.params);
    entityId.value = currentRoute.params.id || null;
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
        accountDataLocal.value.form_id = response.data.form_id ? "Yes" : "No";
        accountDataLocal.value.services = null;
        accountDataLocal.value.annual_fees = null;
        accountDataLocal.value.ref_by = response.data.ref_by?.split(",") || [];
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

const removeServiceFee = (index) => {
  console.log(index);
  console.log(serviceFeeDataLocal.value);
  serviceFeeDataLocal.value.splice(index, 1);
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

const removeOwner = (id) => {
  ownerIdToDelete.value = id;
  deleteOwnerDialog.value = true;
};

const confirmDeleteOwner = async () => {
  try {
    loading.value = true;
    // const updated_ids = accountDataLocal.value.owner_ids.filter(
    //   (item) => item != ownerIdToDelete.value
    // );
    const res = await ApiService.deleteOwner(ownerIdToDelete.value);
    if (res.data.message) toast.success(res.data.message);

    // accountDataLocal.value.owner_ids = updated_ids;
    accountDataLocal.value.owners = accountDataLocal.value.owners.filter(
      (item) => item.id !== ownerIdToDelete.value
    );
    // console.log(accountDataLocal.value.owner_ids);
    console.log(accountDataLocal.value.owners);
    deleteOwnerDialog.value = false;
    ownerIdToDelete.value = null;
  } catch (error) {
    loading.value = false;
    if (error.response) {
      // Handle HTTP errors
      const errorMsg =
        error.response.data.message ||
        "An error occurred while creating the user.";
      toast.error(errorMsg);
    } else if (error.request) {
      // The request was made but no response was received
      toast.error("No response from server. Please try again later.");
    } else {
      // Something else happened in setting up the request
      toast.error("Error: " + error.message);
    }
  }
};

const removeService = (id) => {
  serviceIdToDelete.value = id;
  deleteServiceDialog.value = true;
};

const confirmDeleteService = async () => {
  try {
    loading.value = true;
    let serviceItems = [...serviceFeeDataLocal.value];
    serviceItems.splice(serviceIdToDelete.value, 1);
    const services = serviceItems.map((item) => item.service).join(",");
    const annual_fees = serviceItems.map((item) => item.fee).join(",");
    const data = {
      services: services,
      annual_fees: annual_fees,
      entity_id: entityId.value,
    };
    const res = await ApiService.updateServices(data);
    if (res.data.message) toast.success(res.data.message);

    accountDataLocal.value.services = services;
    accountDataLocal.value.annual_fees = annual_fees;
    serviceFeeDataLocal.value = serviceItems;
    deleteServiceDialog.value = false;
    serviceIdToDelete.value = null;
  } catch (error) {
    deleteServiceDialog.value = false;
    loading.value = false;
    if (error.response) {
      // Handle HTTP errors
      const errorMsg =
        error.response.data.message ||
        "An error occurred while creating the user.";
      toast.error(errorMsg);
    } else if (error.request) {
      // The request was made but no response was received
      toast.error("No response from server. Please try again later.");
    } else {
      // Something else happened in setting up the request
      toast.error("Error: " + error.message);
    }
  }
};

const editEntity = () => {
  router.push("/entities/entity?id=" + entityId.value);
};

const getFileIcon = (url) => {
  if (url.includes('xlsx')) {
    return 'mdi-file-excel';
  } else if (url.includes('pdf')) {
    return 'mdi-file-pdf';
  } else if (url.includes('jpeg') || url.includes('jpg')) {
    return 'mdi-file-image';
  } else if (url.includes('xls')) {
    return 'mdi-file-excel';
  } else {
    return 'mdi-file-document';
  }
};

const downloadDocument = (url, fileName) => {
  const link = document.createElement('a');
  link.href = url;
  link.download = fileName ?? "download";
  link.click();
};
const removeDocument = (id) => {
  console.log("delete document: ", id);
  documentIdToDelete.value = id;
  deleteDocumentDialog.value = true;
};
const confirmDelete = async () => {
  try {
    var id = documentIdToDelete.value;
    if (!id) return;
    await ApiService.deleteDocument(id);
    deleteDocumentDialog.value = false;
    documentIdToDelete.value = null;
    accountDataLocal.value.documents = accountDataLocal.value.documents.filter(item => item.id != id);
    toast.success("Document was deleted correctly.");
  } catch (error) {
    console.log(error);
    toast.error("No response from server. Please try again later.");
  }
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

const headerTitle = () => {
  let title = "Entity Detail";
  if (activeTab.value == "owners") title = "Owners";
  if (activeTab.value == "directors") title = "Directors";
  if (activeTab.value == "services") title = "Services";
  if (activeTab.value == "files") title = "Files";
  return title;
};
</script>

<template>
  <VTabs
    v-model="activeTab"
    show-arrows
  >
    <VTab
      v-for="item in tabs"
      :key="item.icon"
      :value="item.tab"
    >
      <VIcon
        size="20"
        start
        :icon="item.icon"
      />
      {{ item.title }}
    </VTab>
  </VTabs>
  <VDivider />
  <VRow>
    <VCol
      cols="12"
      class="mt-6"
    >
      <VCard>
        <VCardTitle>
          {{ headerTitle() }}
          <VIcon
            size="25"
            start
            icon="bx-edit"
            class="edit-entity-icon"
            @click="editEntity"
          />
        </VCardTitle>
        <VDivider />
        <VCardText>
          <VWindow
            v-model="activeTab"
            class="mt-5 disable-tab-transition"
          >
            <VWindowItem value="entity">
              <!-- 👉 Form -->
              <VForm>
                <VRow>
                  <!-- 👉 Firm Name -->
                  <VCol
                    md="6"
                    lg="3"
                    cols="12"
                  >
                    <VTextField
                      v-model="accountDataLocal.firm_name"
                      placeholder="Vauban Technologies Ltd"
                      label="Firm Name"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Entity Name -->
                  <VCol
                    md="6"
                    lg="3"
                    cols="12"
                  >
                    <VTextField
                      v-model="accountDataLocal.entity_name"
                      placeholder="Nano I a Series of S5V Coinvest"
                      label="Entity Name"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Doing Business Name -->
                  <VCol
                    md="6"
                    lg="3"
                    cols="12"
                  >
                    <VTextField
                      v-model="accountDataLocal.doing_business_as"
                      placeholder="Vauban Ltd"
                      label="DBA"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 EIN Number -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.ein_number"
                      label="EIN Number"
                      placeholder="93-4459228"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Address 1 -->
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="accountDataLocal.address_1"
                      label="Address 1"
                      placeholder="5645 Coral Ridge Drive"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Address 2 -->
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="accountDataLocal.address_2"
                      label="Address 2"
                      placeholder="Suite 410"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 City -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.city"
                      label="City"
                      placeholder="Coral Springs"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 State -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.state"
                      label="State"
                      placeholder="FL"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Zip Code -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.zip"
                      label="Zip Code"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Country -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.country"
                      label="Country"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Type -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="4"
                  >
                    <VTextField
                      v-model="accountDataLocal.type"
                      label="Entity Type"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Type -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="4"
                  >
                    <VTextField
                      v-model="accountDataLocal.form_id"
                      label="Form ID"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 First Tax Year -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="4"
                  >
                    <VTextField
                      v-model="accountDataLocal.first_tax_year"
                      label="First Tax Year"
                      placeholder="Select Year"
                      readonly
                    />
                  </VCol>

                  <VCol cols="12">
                    Officers
                    <VTable>
                      <thead>
                        <tr>
                          <th class="text-left">
                            First Name
                          </th>
                          <th class="text-left">
                            Last Name
                          </th>
                          <th class="text-left">
                            Title
                          </th>
                          <th class="text-left">
                            Email
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="item in accountDataLocal.officer_list"
                          :key="item.email"
                        >
                          <td class="text-left">
                            {{ item.first_name }}
                          </td>
                          <td class="text-center">
                            {{ item.last_name }}
                          </td>
                          <td class="text-center">
                            {{ item.title }}
                          </td>
                          <td class="text-center">
                            {{ item.email }}
                          </td>
                        </tr>
                      </tbody>
                    </VTable>
                  </VCol>

                  <!-- 👉 Contact First Name -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.contact_first_name"
                      label="Contact First Name"
                      placeholder="Daniel"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Last Name -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.contact_last_name"
                      label="Contact Last Name"
                      placeholder="Strachman"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Email -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.contact_email"
                      label="Contact Email"
                      placeholder="johndoe@gmail.com"
                      type="email"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Phone -->
                  <VCol
                    cols="12"
                    md="6"
                    lg="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.contact_phone"
                      label="Contact Phone"
                      placeholder="1-917-543-9876"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Ref By -->
                  <VCol
                    cols="12"
                    md="6"
                  >
                    <VTextField
                      v-model="accountDataLocal.ref_by"
                      label="Referred By"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Notes -->
                  <VCol cols="12">
                    <VTextarea
                      v-model="accountDataLocal.notes"
                      label="Notes"
                      placeholder="..."
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Jurisdiction -->
                  <!-- <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.jurisdiction"
                      label="Jurisdiction"
                      placeholder=""
                    />
                  </VCol> -->
                  <!-- 👉 Created Date -->
                  <!-- <VCol cols="12" md="6">
                    <VueDatePicker
                      v-model="accountDataLocal.date_created"
                      placeholder="Created Date"
                    ></VueDatePicker>
                  </VCol> -->

                  <!-- 👉 Signed Date -->
                  <!-- <VCol cols="12" md="6">
                    <VueDatePicker
                      v-model="accountDataLocal.date_signed"
                      placeholder="Signed Date"
                    ></VueDatePicker>
                  </VCol> -->

                  <!-- 👉 Person -->
                  <VCol
                    cols="12"
                    md="3"
                  >
                    <VTextField
                      v-model="accountDataLocal.person"
                      label="Entered By"
                      readonly
                    />
                  </VCol>
                </VRow>
              </VForm>
            </VWindowItem>
            <VWindowItem value="owners">
              <OwnersTable
                :user-list="accountDataLocal.owners || []"
                @delete="removeOwner"
              />
            </VWindowItem>
            <VWindowItem value="directors">
              <VTable>
                <thead>
                  <tr>
                    <th class="text-left">
                      First Name
                    </th>
                    <th class="text-left">
                      Last Name
                    </th>
                    <th class="text-left">
                      Email
                    </th>
                    <th class="text-left">
                      Phone
                    </th>
                    <th class="text-left">
                      Address 1
                    </th>
                    <th class="text-left">
                      Address 2
                    </th>
                    <th class="text-left">
                      City
                    </th>
                    <th class="text-left">
                      State
                    </th>
                    <th class="text-left">
                      Zip
                    </th>
                    <th class="text-left">
                      Country
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="item in accountDataLocal.director_list"
                    :key="item.email"
                  >
                    <td class="text-left">
                      {{ item.first_name }}
                    </td>
                    <td class="text-center">
                      {{ item.last_name }}
                    </td>
                    <td class="text-center">
                      {{ item.email }}
                    </td>
                    <td class="text-center">
                      {{ item.phone }}
                    </td>
                    <td class="text-center">
                      {{ item.address1 }}
                    </td>
                    <td class="text-center">
                      {{ item.address2 }}
                    </td>
                    <td class="text-center">
                      {{ item.city }}
                    </td>
                    <td class="text-center">
                      {{ item.state }}
                    </td>
                    <td class="text-center">
                      {{ item.zip }}
                    </td>
                    <td class="text-center">
                      {{ item.country }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VWindowItem>
            <VWindowItem value="registered_agents">
              <VTable>
                <thead>
                  <tr>
                    <th class="text-left">
                      Entity Name
                    </th>
                    <th class="text-left">
                      Company Name
                    </th>
                    <th class="text-left">
                      Contact First Name
                    </th>
                    <th class="text-left">
                      Contact Last Name
                    </th>
                    <th class="text-left">
                      Address 1
                    </th>
                    <th class="text-left">
                      Address 2
                    </th>
                    <th class="text-left">
                      City
                    </th>
                    <th class="text-left">
                      State
                    </th>
                    <th class="text-left">
                      Zip
                    </th>
                    <th class="text-left">
                      Country
                    </th>
                    <th class="text-left">
                      Email
                    </th>
                    <th class="text-left">
                      Phone
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="item in accountDataLocal.registered_agent_list"
                    :key="item.email"
                  >
                    <td class="text-left">
                      {{ item.entity_name }}
                    </td>
                    <td class="text-left">
                      {{ item.company_name }}
                    </td>
                    <td class="text-left">
                      {{ item.first_name }}
                    </td>
                    <td class="text-center">
                      {{ item.last_name }}
                    </td>
                    <td class="text-center">
                      {{ item.address1 }}
                    </td>
                    <td class="text-center">
                      {{ item.address2 }}
                    </td>
                    <td class="text-center">
                      {{ item.city }}
                    </td>
                    <td class="text-center">
                      {{ item.state }}
                    </td>
                    <td class="text-center">
                      {{ item.zip }}
                    </td>
                    <td class="text-center">
                      {{ item.country }}
                    </td>
                    <td class="text-center">
                      {{ item.email }}
                    </td>
                    <td class="text-center">
                      {{ item.phone }}
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VWindowItem>
            <VWindowItem value="services">
              <VTable>
                <thead>
                  <tr>
                    <th class="text-left">
                      Service
                    </th>
                    <th class="text-left">
                      Annual Fee
                    </th>
                    <th class="text-left" />
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(item, index) in serviceFeeDataLocal"
                    :key="item.service"
                  >
                    <td>{{ item.service }}</td>
                    <td class="text-center">
                      {{ item.fee }}
                    </td>
                    <td class="text-center">
                      <VIcon
                        icon="bx-trash"
                        @click="removeService(index)"
                      />
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VWindowItem>
            <VWindowItem value="files">
              <VRow>
                <VCol
                  v-for="document in accountDataLocal.documents"
                  :key="document.id"
                  cols="12"
                >
                  <VRow align="center">
                    <VCol cols="1">
                      <VIcon
                        :icon="getFileIcon(document.url)"
                        color="primary"
                      />
                    </VCol>
                    <VCol cols="9">
                      <a
                        :href="document.url"
                        target="_blank"
                      >
                        {{ document.file_name || document.url.split('/').pop() }}
                      </a>
                    </VCol>
                    <VCol cols="1">
                      <VIcon
                        icon="mdi-download"
                        color="primary"
                        @click="downloadDocument(document.url, document.file_name)"
                      />
                    </VCol>
                    <VCol cols="1">
                      <VIcon
                        icon="bx-minus-circle"
                        color="primary"
                        @click="removeDocument(document.id)"
                      />
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
            </VWindowItem>
          </VWindow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <VDialog
    v-model="deleteOwnerDialog"
    width="500"
  >
    <VCard :loading="loading">
      <VCardTitle class="headline">
        Confirm Deletion
      </VCardTitle>
      <VCardText>
        Are you sure you want to remove owner
        <span style="color: black;">
          {{
            accountDataLocal.owners.filter((i) => i.id == ownerIdToDelete)[0]
              ?.first_name
          }}

          {{
            accountDataLocal.owners.filter((i) => i.id == ownerIdToDelete)[0]
              ?.last_name
          }}
        </span>
        ?
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          color="green darken-1"
          text
          @click="deleteOwnerDialog = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="red darken-1"
          text
          :loading="loading"
          @click="confirmDeleteOwner"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  <VDialog
    v-model="deleteServiceDialog"
    width="500"
  >
    <VCard :loading="loading">
      <VCardTitle class="headline">
        Confirm Deletion
      </VCardTitle>
      <VCardText>
        Are you sure you want to remove service
        <span style="color: black;">
          {{ serviceFeeDataLocal[serviceIdToDelete]?.service }}
        </span>
        ?
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          color="green darken-1"
          text
          @click="deleteServiceDialog = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="red darken-1"
          text
          :loading="loading"
          @click="confirmDeleteService"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  <VDialog
    v-model="deleteDocumentDialog"
    width="500"
  >
    <VCard>
      <VCardTitle class="headline">
        Confirm Deletion
      </VCardTitle>
      <VCardText> Are you sure you want to delete this document? </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn
          color="green darken-1"
          text
          @click="deleteDocumentDialog = false"
        >
          Cancel
        </VBtn>
        <VBtn
          color="red darken-1"
          text
          @click="confirmDelete"
        >
          Delete
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
<style scoped>
.pdf-preview {
  border: none;
  block-size: 200px; /* Adjust as needed */
  inline-size: 100%;
}

.edit-entity-icon {
  position: absolute;
  cursor: pointer;
  inset-inline-end: 10px;
}
</style>

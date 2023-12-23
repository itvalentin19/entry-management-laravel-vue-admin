<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import ApiService from "@/services/api";
import { useToast } from "vue-toastification";
import { useRoute, useRouter } from "vue-router";
import { onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import EntityUpload from "@/layouts/components/EntityUpload.vue";
import OwnersTable from "@/layouts/components/OwnersTable.vue";

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
const dialog = ref(false);
const documentIdToDelete = ref(null);

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
    title: "Services",
    icon: "bx-building-house",
    tab: "services",
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

const editEntity = () => {
  router.push("/entities/entity?id=" + entityId.value);
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
  return title;
};
</script>

<template>
  <VTabs v-model="activeTab" show-arrows>
    <VTab v-for="item in tabs" :key="item.icon" :value="item.tab">
      <VIcon size="20" start :icon="item.icon" />
      {{ item.title }}
    </VTab>
  </VTabs>
  <VDivider />
  <VRow>
    <VCol cols="12" class="mt-6">
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
          <VWindow v-model="activeTab" class="mt-5 disable-tab-transition">
            <VWindowItem value="entity">
              <!-- 👉 Form -->
              <VForm>
                <VRow>
                  <!-- 👉 Firm Name -->
                  <VCol md="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.firm_name"
                      placeholder="Vauban Technologies Ltd"
                      label="Firm Name"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Entity Name -->
                  <VCol md="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.entity_name"
                      placeholder="Nano I a Series of S5V Coinvest"
                      label="Entity Name"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Doing Business Name -->
                  <VCol md="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.doing_business_as"
                      placeholder="Vauban Ltd"
                      label="DBA"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 EIN Number -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.ein_number"
                      label="EIN Number"
                      placeholder="93-4459228"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Address 1 -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.address_1"
                      label="Address 1"
                      placeholder="5645 Coral Ridge Drive"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Address 2 -->
                  <VCol cols="12" md="6">
                    <VTextField
                      v-model="accountDataLocal.address_2"
                      label="Address 2"
                      placeholder="Suite 410"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 City -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.city"
                      label="City"
                      placeholder="Coral Springs"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 State -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.state"
                      label="State"
                      placeholder="FL"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Zip Code -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.zip"
                      label="Zip Code"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Country -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.country"
                      label="Country"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Type -->
                  <VCol cols="12" md="6" lg="4">
                    <VTextField
                      v-model="accountDataLocal.type"
                      label="Entity Type"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Type -->
                  <VCol cols="12" md="6" lg="4">
                    <VTextField
                      v-model="accountDataLocal.form_id"
                      label="Form ID"
                      placeholder="10001"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 First Tax Year -->
                  <VCol cols="12" md="6" lg="4">
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
                          <th class="text-left">First Name</th>
                          <th class="text-left">Last Name</th>
                          <th class="text-left">Title</th>
                          <th class="text-left">Email</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="item in accountDataLocal.officer_list"
                          :key="item.email"
                        >
                          <td class="text-left">{{ item.first_name }}</td>
                          <td class="text-center">{{ item.last_name }}</td>
                          <td class="text-center">{{ item.title }}</td>
                          <td class="text-center">{{ item.email }}</td>
                        </tr>
                      </tbody>
                    </VTable>
                  </VCol>

                  <!-- 👉 Person -->
                  <VCol cols="12">
                    <VTextField
                      v-model="accountDataLocal.person"
                      label="Contact Person"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Contact First Name -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_first_name"
                      label="Contact First Name"
                      placeholder="Daniel"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Last Name -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_last_name"
                      label="Contact Last Name"
                      placeholder="Strachman"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Email -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_email"
                      label="Contact Email"
                      placeholder="johndoe@gmail.com"
                      type="email"
                      readonly
                    />
                  </VCol>

                  <!-- 👉 Contact Phone -->
                  <VCol cols="12" md="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_phone"
                      label="Contact Phone"
                      placeholder="1-917-543-9876"
                      readonly
                    />
                  </VCol>
                  <!-- 👉 Ref By -->
                  <VCol cols="12" md="6">
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

                  <VCol
                    cols="4"
                    md="3"
                    v-for="document in accountDataLocal.documents"
                    :key="document.id"
                  >
                    <iframe :src="document.url" class="pdf-preview"></iframe>
                    <a :href="document.url" target="_blank">View Document</a>
                  </VCol>
                </VRow>
              </VForm>
            </VWindowItem>
            <VWindowItem value="owners">
              <OwnersTable
                :user-list="accountDataLocal.owners || []"
                :view-only="true"
              />
            </VWindowItem>
            <VWindowItem value="directors">
              <VTable>
                <thead>
                  <tr>
                    <th class="text-left">First Name</th>
                    <th class="text-left">Last Name</th>
                    <th class="text-left">Email</th>
                    <th class="text-left">Phone</th>
                    <th class="text-left">Address 1</th>
                    <th class="text-left">Address 2</th>
                    <th class="text-left">City</th>
                    <th class="text-left">State</th>
                    <th class="text-left">Zip</th>
                    <th class="text-left">Country</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="item in accountDataLocal.director_list"
                    :key="item.email"
                  >
                    <td class="text-left">{{ item.first_name }}</td>
                    <td class="text-center">{{ item.last_name }}</td>
                    <td class="text-center">{{ item.email }}</td>
                    <td class="text-center">{{ item.phone }}</td>
                    <td class="text-center">{{ item.address1 }}</td>
                    <td class="text-center">{{ item.address2 }}</td>
                    <td class="text-center">{{ item.city }}</td>
                    <td class="text-center">{{ item.state }}</td>
                    <td class="text-center">{{ item.zip }}</td>
                    <td class="text-center">{{ item.country }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VWindowItem>
            <VWindowItem value="services">
              <VTable>
                <thead>
                  <tr>
                    <th class="text-left">Service</th>
                    <th class="text-left">Annual Fee</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in serviceFeeDataLocal" :key="item.service">
                    <td>{{ item.service }}</td>
                    <td class="text-center">{{ item.fee }}</td>
                  </tr>
                </tbody>
              </VTable>
            </VWindowItem>
          </VWindow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
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
.edit-entity-icon {
  position: absolute;
  right: 10px;
  cursor: pointer;
}
</style>

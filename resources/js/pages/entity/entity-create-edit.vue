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
  officers: null,
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
  active: true,
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
  address1: null,
  address2: null,
  city: null,
  state: null,
  zip: null,
  country: null,
};
const directorListInitial = [directorItem];
const directorList = ref(structuredClone(directorListInitial));

const officerItem = {
  first_name: null,
  last_name: null,
  title: null,
  email: null,
  phone: null,
  address1: null,
  address2: null,
};
const officerListInitial = [];
const officerList = ref(structuredClone(officerListInitial));

const registeredAgentItem = {
  entity_name: null,
  company_name: null,
  first_name: null,
  last_name: null,
  address1: null,
  address2: null,
  city: null,
  state: null,
  zip: null,
  country: null,
  email: null,
  phone: null,
};
const registeredAgentListInitial = [];
const registeredAgentList = ref(structuredClone(registeredAgentListInitial));

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
const addReferDialog = ref(false);
const addOwnerDialog = ref(false);
const loading = ref(false);

const referredByItem = {
  name: null,
};
const referredByListInitial = [referredByItem];
const referredByList = ref(structuredClone(referredByListInitial));

const ownerItem = {
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
const ownerListInitial = [];
const ownerList = ref(structuredClone(ownerListInitial));

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
        formData.append(key, accountDataLocal.value[key] ?? "");
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

    if (officerList.value.length > 0) {
      formData.append("officer_list", JSON.stringify(officerList.value));
    }

    if (registeredAgentList.value.length > 0) {
      formData.append(
        "registered_agent_list",
        JSON.stringify(registeredAgentList.value)
      );
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
      console.log(res);
      if (res.data?.id) {
        // ownerList.value.length && toast.info("Adding/Updating Owners!");
        await addUpdateOwners(res.data?.id);
        toast.success("Entity updated successfully!");
      }
    } else {
      // Create new Entity
      res = await ApiService.createEntity(formData, config);
      console.log(res);
      if (res.data?.id) {
        // ownerList.value.length && toast.info("Adding Owners!");
        await addUpdateOwners(res.data?.id);
        toast.success("Entity created successfully!");
      }
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

const addUpdateOwners = async (entity_id) => {
  for (let i = 0; i < ownerList.value.length; i++) {
    const owner = ownerList.value[i];
    owner.entity_id = entity_id;
    const isLast = i == ownerList.value.length - 1;
    await addOwner(owner, isLast);
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
        accountDataLocal.value.form_id = response.data.form_id ? "yes" : "no";
        accountDataLocal.value.services = null;
        accountDataLocal.value.annual_fees = null;
        accountDataLocal.value.ref_by = response.data.ref_by?.split(",") || [];
        // Update Director List
        directorList.value = response.data.director_list || [];
        // Update Officer List
        officerList.value = response.data.officer_list || [];
        // Update Registered Agent List
        registeredAgentList.value = response.data.registered_agent_list || [];
        // Update Owner List
        ownerList.value = response.data.owners || [];
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

const addNewOfficer = async () => {
  const newItem = structuredClone(officerItem);
  officerList.value.push(newItem);
};

const addNewAgent = async () => {
  const newItem = structuredClone(registeredAgentItem);
  registeredAgentList.value.push(newItem);
};

const removeServiceFee = (index) => {
  serviceFeeDataLocal.value.splice(index, 1);
};

const removeDirector = (index) => {
  console.log(index);
  console.log(directorList.value);
  directorList.value.splice(index, 1);
};

const removeOfficer = (index) => {
  console.log(index);
  console.log(officerList.value);
  officerList.value.splice(index, 1);
};

const removeRegisteredAgent = (index) => {
  console.log(index);
  console.log(registeredAgentList.value);
  registeredAgentList.value.splice(index, 1);
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

const formatEIN = (value) => {
  if (!value) return "";
  const numericValue = value.replace(/\D/g, "");
  if (numericValue.length <= 2) {
    return numericValue;
  } else {
    return `${numericValue.slice(0, 2)}-${numericValue.slice(2)}`;
  }
};

const formatPhone = (value) => {
  if (!value) return "";
  let numericValue = value.replace(/\D/g, "");
  let formatted = "";

  if (numericValue.length > 1) {
    formatted += `${numericValue.charAt(0)}-`;
    numericValue = numericValue.substring(1);
  }
  if (numericValue.length > 3) {
    formatted += `${numericValue.substring(0, 3)}-`;
    numericValue = numericValue.substring(3);
  }
  if (numericValue.length > 3) {
    formatted += `${numericValue.substring(0, 3)}-${numericValue.substring(3)}`;
  } else {
    formatted += numericValue;
  }
  return formatted;
};

const handleAddRefer = () => {
  addReferDialog.value = true;
};

const addReferredByMore = () => {
  referredByList.value.push(structuredClone(referredByItem));
};

const confirmAddReferredBy = async () => {
  console.log(referredByList.value);
  try {
    if (referredByList.value.length && !referredByList.value[0].name) {
      toast.error("Please enter the name correctly!");
      return;
    }
    if (referredByList.value.length == 0) return;
    loading.value = true;
    const res = await ApiService.createReferredBy(referredByList.value);
    console.log(res);
    if (res.data.message) {
      toast.success(res.data.message);
    }
    loading.value = false;
    addReferDialog.value = false;
    getProps();
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
    console.error(error);
  }
};

const removeReferredBy = (index) => {
  referredByList.value.splice(index, 1);
};

const handleAddOwners = () => {
  addOwnerDialog.value = true;
};

const addNewOwner = () => {
  ownerList.value.push(structuredClone(ownerItem));
};

const addOwnerDocument = (event) => {
  const { files } = event.target;
  console.log(files[0]);
  if (files && files.length) {
    ownerList.value[0].document = files[0];
  }
};

const addOwner = async (owner, isLast) => {
  console.log("Adding Owner");
  console.log(owner);
  try {
    const formData = new FormData();

    // Append owner data
    for (const key in owner) {
      if (key !== "document") {
        formData.append(key, owner[key] || "");
      }
    }

    // Append avatar image if it's a File object (not a URL string)
    if (owner.document) {
      formData.append("document", owner.document);
    }

    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    // loading.value = true;
    if (owner.id) {
      await ApiService.updateOwner(owner.id, formData, config);
    } else {
      await ApiService.createOwner(formData, config);
    }
    // loading.value = false;
    // addOwnerDialog.value = false;
    // isLast && toast.success("Owner created successfully!");
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
    console.error(error);
  }
};

const removeOwner = (index) => {
  ownerList.value.splice(index, 1);
};

watch(
  () => accountDataLocal.value.ein_number,
  (newValue) => {
    accountDataLocal.value.ein_number = formatEIN(newValue);
  },
  { immediate: true }
);

watch(
  () => accountDataLocal.value.contact_phone,
  (newValue) => {
    accountDataLocal.value.contact_phone = formatPhone(newValue);
  },
  { immediate: true }
);

watch(
  directorList,
  (newDirectors) => {
    newDirectors?.forEach((director) => {
      director.phone = formatPhone(director.phone);
    });
  },
  { deep: true }
);

watch(
  officerList,
  (newOfficers) => {
    newOfficers.forEach((officer) => {
      officer.phone = formatPhone(officer.phone);
    });
  },
  { deep: true }
);

watch(
  registeredAgentList,
  (newAgents) => {
    newAgents.forEach((agent) => {
      agent.phone = formatPhone(agent.phone);
    });
  },
  { deep: true }
);

watch(
  ownerList,
  (newOwners) => {
    newOwners.forEach((owner) => {
      owner.phone = formatPhone(owner.phone);
    });
  },
  { deep: true }
);

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

onMounted(() => {
  getProps();
});
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
                  <VCol md="6" sm="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.firm_name"
                      placeholder="Vauban Technologies Ltd"
                      label="Firm Name"
                    />
                  </VCol>
                  <!-- 👉 Doing Business Name -->
                  <VCol md="6" sm="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.doing_business_as"
                      placeholder="Vauban Ltd"
                      label="Doing Business Name"
                    />
                  </VCol>

                  <!-- 👉 Entity Name -->
                  <VCol md="6" sm="6" lg="3" cols="12">
                    <VTextField
                      v-model="accountDataLocal.entity_name"
                      placeholder="Nano I a Series of S5V Coinvest"
                      label="Entity Name"
                    />
                  </VCol>

                  <!-- 👉 Address 1 -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.address_1"
                      label="Address 1"
                      placeholder="5645 Coral Ridge Drive"
                    />
                  </VCol>
                  <!-- 👉 Address 2 -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.address_2"
                      label="Address 2"
                      placeholder="Suite 410"
                    />
                  </VCol>
                  <!-- 👉 City -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.city"
                      label="City"
                      placeholder="Coral Springs"
                    />
                  </VCol>

                  <!-- 👉 State -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VAutocomplete
                      v-model="accountDataLocal.state"
                      label="State"
                      placeholder="FL"
                      item-title="name"
                      item-value="abbreviation"
                      :items="states"
                    />
                  </VCol>

                  <!-- 👉 Zip Code -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.zip"
                      label="Zip Code"
                      placeholder="10001"
                    />
                  </VCol>

                  <!-- 👉 Country -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VSelect
                      v-model="accountDataLocal.country"
                      label="Country"
                      :items="['USA', 'Canada', 'UK', 'India', 'Australia']"
                      placeholder="Select Country"
                    />
                  </VCol>

                  <!-- 👉 Type -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VCombobox
                      v-model="accountDataLocal.type"
                      label="Type"
                      :items="types"
                      placeholder="Select Type"
                    />
                  </VCol>

                  <!-- 👉 First Tax Year -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VSelect
                      v-model="accountDataLocal.first_tax_year"
                      label="First Tax Year"
                      :items="years"
                      placeholder="Select Year"
                    />
                  </VCol>
                  <!-- 👉 EIN Number -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.ein_number"
                      label="EIN Number"
                      placeholder="93-4459228"
                    />
                  </VCol>
                  <!-- 👉 Jurisdiction -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VAutocomplete
                      v-model="accountDataLocal.jurisdiction"
                      label="Jurisdiction"
                      placeholder=""
                      item-title="name"
                      item-value="name"
                      :items="states"
                    />
                  </VCol>
                  <!-- 👉 Launched Date -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VueDatePicker
                      v-model="accountDataLocal.date_created"
                      placeholder="Launched Date"
                    ></VueDatePicker>
                  </VCol>

                  <!-- 👉 Signed Date -->
                  <!-- <VCol cols="12" md="6" sm="6" lg="3">
                    <VueDatePicker
                      v-model="accountDataLocal.date_signed"
                      placeholder="Signed Date"
                    ></VueDatePicker>
                  </VCol> -->

                  <!-- 👉 Contact First Name -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_first_name"
                      label="Contact First Name"
                      placeholder="Daniel"
                    />
                  </VCol>

                  <!-- 👉 Contact Last Name -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_last_name"
                      label="Contact Last Name"
                      placeholder="Strachman"
                    />
                  </VCol>

                  <!-- 👉 Contact Email -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_email"
                      label="Contact Email"
                      placeholder="johndoe@gmail.com"
                      type="email"
                    />
                  </VCol>

                  <!-- 👉 Contact Phone -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VTextField
                      v-model="accountDataLocal.contact_phone"
                      label="Contact Phone"
                      placeholder="1-917-543-9876"
                    />
                  </VCol>
                  <VDivider />

                  <!-- 👉 Owners -->
                  <!-- <VCol cols="12" md="6" sm="6" lg="3">
                    <VAutocomplete
                      v-model="accountDataLocal.owner_ids"
                      label="Select Owners"
                      multiple
                      item-title="name"
                      item-value="id"
                      :items="owners"
                      placeholder="Select Owners"
                      prepend-inner-icon="bx-plus"
                      @click:prepend-inner="handleAddOwners"
                    />
                  </VCol>

                  <VDivider /> -->
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
                      <VCol cols="12" md="6" sm="6">
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
                      <VCol cols="12" md="6" sm="6">
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
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="director.first_name"
                              label="First Name"
                              placeholder="Daniel"
                            />
                          </VCol>

                          <!-- 👉 Last Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="director.last_name"
                              label="Last Name"
                              placeholder="Strachman"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="director.email"
                              label="Email"
                              placeholder="johndoe@gmail.com"
                              type="email"
                            />
                          </VCol>

                          <!-- 👉 Phone -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="director.phone"
                              label="Phone"
                              placeholder="1-917-543-9876"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="director.address1"
                              label="Address 1"
                              placeholder="5645 Coral Ridge Drive"
                            />
                          </VCol>

                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="director.address2"
                              label="Address 2"
                              placeholder="Suite 410"
                            />
                          </VCol>

                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="director.city"
                              label="City"
                              placeholder="City"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VAutocomplete
                              v-model="director.state"
                              label="State"
                              placeholder="FL"
                              item-title="name"
                              item-value="abbreviation"
                              :items="states"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="director.zip"
                              label="Zip"
                              placeholder="23456"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VSelect
                              v-model="director.country"
                              label="Country"
                              :items="[
                                'USA',
                                'Canada',
                                'UK',
                                'India',
                                'Australia',
                              ]"
                              placeholder="Select Country"
                            />
                          </VCol>
                        </VRow>
                      </VCardText>
                    </VCard>
                  </VCol>

                  <VDivider />

                  <!-- 👉 Directors -->
                  <VCol cols="12">
                    <p class="directors">
                      Owners
                      <VIcon icon="bx-plus-circle" @click="addNewOwner" />
                    </p>
                  </VCol>
                  <VCol cols="12">
                    <VCard
                      v-for="(owner, index) in ownerList"
                      :key="index"
                      class="director-card"
                    >
                      <VIcon
                        class="item-delete-button"
                        icon="bx-minus-circle"
                        color="#c93903"
                        @click="removeOwner(index)"
                      />
                      <VCardText>
                        <VRow>
                          <VCol md="6" cols="12">
                            <VTextField
                              v-model="owner.first_name"
                              placeholder="John"
                              label="First Name"
                            />
                          </VCol>

                          <VCol md="6" cols="12">
                            <VTextField
                              v-model="owner.last_name"
                              placeholder="Doe"
                              label="Last Name"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.email"
                              label="E-mail"
                              placeholder="johndoe@gmail.com"
                              type="email"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.phone"
                              label="Phone Number"
                              placeholder="1-917-543-9876"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.address1"
                              label="Address 1"
                              placeholder="5645 Coral Ridge Drive"
                            />
                          </VCol>
                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.address2"
                              label="Address 2"
                              placeholder="Suite 410"
                            />
                          </VCol>
                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.city"
                              label="City"
                              placeholder="Coral Springs"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VAutocomplete
                              v-model="owner.state"
                              label="State"
                              placeholder="FL"
                              item-title="name"
                              item-value="abbreviation"
                              :items="states"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.zip"
                              label="Zip Code"
                              placeholder="10001"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VSelect
                              v-model="owner.country"
                              label="Country"
                              :items="[
                                'USA',
                                'Canada',
                                'UK',
                                'India',
                                'Australia',
                              ]"
                              placeholder="Select Country"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VTextField
                              v-model="owner.ownership_stake"
                              label="Ownership Stake"
                              placeholder="Ownership"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VSelect
                              v-model="owner.document_type"
                              label="Document Type"
                              :items="['DL', 'Passport']"
                              placeholder="Select Country"
                            />
                          </VCol>

                          <VCol cols="12" md="6">
                            <VueDatePicker
                              v-model="owner.document_expiration"
                              placeholder="Document Expiration Date"
                            ></VueDatePicker>
                          </VCol>
                          <VCol cols="12" md="6">
                            <VFileInput
                              :v-model="owner.document"
                              clearable
                              label="Upload Document"
                              accept=".pdf"
                              variant="outlined"
                              show-size
                              @change="addOwnerDocument"
                            ></VFileInput>
                          </VCol>
                        </VRow>
                      </VCardText>
                    </VCard>
                  </VCol>

                  <VDivider />

                  <!-- 👉 Directors -->
                  <VCol cols="12">
                    <p class="directors">
                      Officers
                      <VIcon icon="bx-plus-circle" @click="addNewOfficer" />
                    </p>
                  </VCol>
                  <VCol cols="12">
                    <VCard
                      v-for="(officer, index) in officerList"
                      :key="index"
                      class="director-card"
                    >
                      <VIcon
                        class="item-delete-button"
                        icon="bx-minus-circle"
                        color="#c93903"
                        @click="removeOfficer(index)"
                      />
                      <VCardText>
                        <VRow>
                          <!-- 👉 Contact First Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="officer.first_name"
                              label="First Name"
                              placeholder="Daniel"
                            />
                          </VCol>

                          <!-- 👉 Last Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="officer.last_name"
                              label="Last Name"
                              placeholder="Strachman"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="officer.email"
                              label="Email"
                              placeholder="johndoe@gmail.com"
                              type="email"
                            />
                          </VCol>

                          <!-- 👉 Phone -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="officer.phone"
                              label="Phone"
                              placeholder="1-917-543-9876"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="officer.title"
                              label="Title"
                              placeholder="Input Title"
                            />
                          </VCol>

                          <!-- 👉 Email -->
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="officer.address1"
                              label="Address 1"
                              placeholder="5645 Coral Ridge Drive"
                            />
                          </VCol>

                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="officer.address2"
                              label="Address 2"
                              placeholder="Suite 410"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="officer.city"
                              label="City"
                              placeholder="City"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VAutocomplete
                              v-model="officer.state"
                              label="State"
                              placeholder="FL"
                              item-title="name"
                              item-value="abbreviation"
                              :items="states"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="officer.zip"
                              label="Zip"
                              placeholder="23456"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VSelect
                              v-model="officer.country"
                              label="Country"
                              :items="[
                                'USA',
                                'Canada',
                                'UK',
                                'India',
                                'Australia',
                              ]"
                              placeholder="Select Country"
                            />
                          </VCol>
                        </VRow>
                      </VCardText>
                    </VCard>
                  </VCol>
                  <VDivider />

                  <!-- 👉 Registered Offices -->
                  <VCol cols="12">
                    <p class="directors">
                      Registered Office/Agent
                      <VIcon icon="bx-plus-circle" @click="addNewAgent" />
                    </p>
                  </VCol>
                  <VCol cols="12">
                    <VCard
                      v-for="(agent, index) in registeredAgentList"
                      :key="index"
                      class="director-card"
                    >
                      <VIcon
                        class="item-delete-button"
                        icon="bx-minus-circle"
                        color="#c93903"
                        @click="removeRegisteredAgent(index)"
                      />
                      <VCardText>
                        <VRow>
                          <!-- 👉 Contact First Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.entity_name"
                              label="Entity Name"
                              placeholder="Nano I a Series of S5V Coinvest"
                            />
                          </VCol>
                          <!-- 👉 Contact First Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.company_name"
                              label="Company Name"
                              placeholder="Nano I a Series of S5V Coinvest"
                            />
                          </VCol>
                          <!-- 👉 Contact First Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.first_name"
                              label="First Name"
                              placeholder="Daniel"
                            />
                          </VCol>

                          <!-- 👉 Last Name -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.last_name"
                              label="Last Name"
                              placeholder="Strachman"
                            />
                          </VCol>

                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="agent.address1"
                              label="Address 1"
                              placeholder="5645 Coral Ridge Drive"
                            />
                          </VCol>

                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="agent.address2"
                              label="Address 2"
                              placeholder="Suite 410"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="agent.city"
                              label="City"
                              placeholder="City"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VAutocomplete
                              v-model="agent.state"
                              label="State"
                              placeholder="FL"
                              item-title="name"
                              item-value="abbreviation"
                              :items="states"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VTextField
                              v-model="agent.zip"
                              label="Zip"
                              placeholder="23456"
                            />
                          </VCol>
                          <VCol cols="12" md="4" sm="6">
                            <VSelect
                              v-model="agent.country"
                              label="Country"
                              :items="[
                                'USA',
                                'Canada',
                                'UK',
                                'India',
                                'Australia',
                              ]"
                              placeholder="Select Country"
                            />
                          </VCol>
                          <!-- 👉 Email -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.email"
                              label="Email"
                              placeholder="johndoe@gmail.com"
                              type="email"
                            />
                          </VCol>

                          <!-- 👉 Phone -->
                          <VCol cols="12" md="3" sm="6">
                            <VTextField
                              v-model="agent.phone"
                              label="Phone"
                              placeholder="1-917-543-9876"
                            />
                          </VCol>
                        </VRow>
                      </VCardText>
                    </VCard>
                  </VCol>
                  <VDivider />

                  <!-- 👉 Directors -->
                  <VCol cols="12">
                    <VLabel>Form ID</VLabel>
                    <VRadioGroup v-model="accountDataLocal.form_id" inline>
                      <VRadio label="Yes" value="yes"></VRadio>
                      <VRadio label="No" value="no"></VRadio>
                    </VRadioGroup>
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
                  <VCol cols="12" md="4" sm="6">
                    <VCombobox
                      v-model="accountDataLocal.ref_by"
                      label="Referred By"
                      multiple
                      prepend-inner-icon="bx-plus"
                      @click:prepend-inner="handleAddRefer"
                      :items="refers"
                    />
                  </VCol>
                  <VCol cols="12" md="2" sm="6">
                    <VCheckbox
                      v-model="accountDataLocal.active"
                      :label="accountDataLocal.active ? 'Active' : 'Inactive'"
                    >
                    </VCheckbox>
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

                  <!-- 👉 Person -->
                  <VCol cols="12" md="6" sm="6" lg="3">
                    <VAutocomplete
                      v-model="accountDataLocal.person"
                      label="Entered By"
                      :items="users"
                      clearable
                      placeholder="Select Person"
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
  <VDialog v-model="addReferDialog" width="500">
    <VCard :loading="loading">
      <VCardTitle class="headline"
        >Add Referred By
        <VIcon icon="bx-plus-circle" @click="addReferredByMore"
      /></VCardTitle>
      <VCardText>
        <VRow>
          <VCol cols="12" v-for="(item, index) in referredByList" :key="index">
            <VTextField
              v-model="item.name"
              label="Name"
              placeholder="Enter Name"
              append-icon="bx-minus-circle"
              @click:append="removeReferredBy(index)"
            />
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="addReferDialog = false"
          >Cancel</VBtn
        >
        <VBtn color="red darken-1" text @click="confirmAddReferredBy">Add</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  <VDialog v-model="addOwnerDialog" width="500">
    <VCard :loading="loading">
      <VCardTitle class="headline">Add Owner</VCardTitle>
      <VCardText>
        <VRow v-for="(item, index) in ownerList" :key="index">
          <!-- 👉 First Name -->
          <VCol md="6" cols="12">
            <VTextField
              v-model="item.first_name"
              placeholder="John"
              label="First Name"
            />
          </VCol>

          <!-- 👉 Last Name -->
          <VCol md="6" cols="12">
            <VTextField
              v-model="item.last_name"
              placeholder="Doe"
              label="Last Name"
            />
          </VCol>

          <!-- 👉 Email -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.email"
              label="E-mail"
              placeholder="johndoe@gmail.com"
              type="email"
            />
          </VCol>

          <!-- 👉 Phone -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.phone"
              label="Phone Number"
              placeholder="1-917-543-9876"
            />
          </VCol>

          <!-- 👉 Address 1 -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.address1"
              label="Address 1"
              placeholder="5645 Coral Ridge Drive"
            />
          </VCol>
          <!-- 👉 Address 2 -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.address2"
              label="Address 2"
              placeholder="Suite 410"
            />
          </VCol>
          <!-- 👉 Address 2 -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.city"
              label="City"
              placeholder="Coral Springs"
            />
          </VCol>

          <!-- 👉 State -->
          <VCol cols="12" md="6">
            <VTextField v-model="item.state" label="State" placeholder="FL" />
          </VCol>

          <!-- 👉 Zip Code -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.zip"
              label="Zip Code"
              placeholder="10001"
            />
          </VCol>

          <!-- 👉 Country -->
          <VCol cols="12" md="6">
            <VSelect
              v-model="item.country"
              label="Country"
              :items="['USA', 'Canada', 'UK', 'India', 'Australia']"
              placeholder="Select Country"
            />
          </VCol>

          <!-- 👉 Ownership Stack -->
          <VCol cols="12" md="6">
            <VTextField
              v-model="item.ownership_stake"
              label="Ownership Stake"
              placeholder="Ownership"
            />
          </VCol>

          <!-- 👉 Document Type -->
          <VCol cols="12" md="6">
            <VSelect
              v-model="item.document_type"
              label="Document Type"
              :items="['DL', 'Passport']"
              placeholder="Select Country"
            />
          </VCol>

          <!-- 👉 Document Expiration -->
          <VCol cols="12" md="6">
            <VueDatePicker
              v-model="item.document_expiration"
              placeholder="Document Expiration Date"
            ></VueDatePicker>
          </VCol>
          <!-- 👉 Document -->
          <VCol cols="12" md="6">
            <VFileInput
              :v-model="item.document"
              clearable
              label="Upload Document"
              accept=".pdf"
              variant="outlined"
              show-size
              @change="addOwnerDocument"
            ></VFileInput>
          </VCol>
        </VRow>
      </VCardText>
      <VCardActions>
        <VSpacer></VSpacer>
        <VBtn color="green darken-1" text @click="addOwnerDialog = false"
          >Cancel</VBtn
        >
        <VBtn color="red darken-1" text @click="confirmAddOwner">Add</VBtn>
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

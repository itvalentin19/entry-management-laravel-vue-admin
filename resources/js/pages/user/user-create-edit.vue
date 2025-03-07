<script setup>
import states from "@/pages/entity/us_states.json";
import ApiService from "@/services/api";
import countries from "@/store/countries.json";
import avatar1 from "@images/avatars/avatar-1.png";
import { useRoute, useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useStore } from "vuex";

const accountData = {
  first_name: "",
  last_name: "",
  company: "A&C Corporate Services LLC",
  email: "",
  phone: "",
  address: "",
  address2: "",
  avatarImg: avatar1,
  role: "User",
  city: "",
  state: "",
  zip: "",
  country: "USA",
};
const route = useRoute();
const userId = ref(null);
const refInputEl = ref();
const accountDataLocal = ref(structuredClone(accountData));
const isAccountDeactivated = ref(false);
const toast = useToast();
const router = useRouter();
const store = useStore();
const _user = computed(() => store.getters.user);
const loading = ref(false);

const resetForm = () => {
  accountDataLocal.value = structuredClone(accountData);
};

const changeAvatar = (file) => {
  const fileReader = new FileReader();
  const { files } = file.target;
  if (files && files.length) {
    fileReader.readAsDataURL(files[0]);
    fileReader.onload = () => {
      if (typeof fileReader.result === "string")
        accountDataLocal.value.avatarImg = fileReader.result;
    };
  }
};

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.avatarImg = accountData.avatarImg;
};

// handle save user information

const onCreateUser = async () => {
  try {
    const formData = new FormData();

    console.log(accountDataLocal.value);
    // Append user data
    for (const key in accountDataLocal.value) {
      if (key !== "avatarImg") {
        formData.append(key, accountDataLocal.value[key] ?? "");
      }
    }

    // Append avatar image if it's a File object (not a URL string)
    if (refInputEl.value?.files[0]) {
      formData.append("avatar", refInputEl.value.files[0]);
    }

    let res;

    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    loading.value = true;
    if (userId.value) {
      // Update existing user
      res = await ApiService.updateUser(userId.value, formData, config);
      toast.success("User updated successfully!");
    } else {
      // Create new user
      res = await ApiService.createUser(formData, config);
      toast.success("User created successfully!");
    }
    loading.value = false;

    router.push("/users");
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
watch(
  () => accountDataLocal.value.phone,
  (newValue) => {
    accountDataLocal.value.phone = formatPhone(newValue);
  },
  { immediate: true }
);

watch(
  route,
  async (currentRoute) => {
    if (_user.value?.admin == true) {
      userId.value = currentRoute.query.id || null;
      if (userId?.value) {
        try {
          const response = await ApiService.getUser(userId.value);
          accountDataLocal.value = response.data;
          accountDataLocal.value.avatarImg = response.data.photo || avatar1;
        } catch (error) {
          toast.error("Failed to load user data.");
          router.push("/users/user");
          console.error(error);
        }
      } else {
        resetForm();
        resetAvatar();
      }
    } else {
      router.push("/users");
    }
  },
  { immediate: true }
);
</script>

<template>
  <div class="d-flex align-center">
    <VBtn
      variant="text"
      class="ms-n3 mb-3"
      to="/"
    >
      <VIcon icon="bx-arrow-back" />
      Go To Home
    </VBtn>
  </div>
  <VRow>
    <VCol cols="12">
      <VCard :title="userId ? 'Edit User Detail' : 'Add User Detail'">
        <VCardText class="d-flex">
          <!-- 👉 Avatar -->
          <VAvatar
            rounded="full"
            size="100"
            class="me-6"
            :image="accountDataLocal.avatarImg"
          />

          <!-- 👉 Upload Photo -->
          <form class="d-flex flex-column justify-center gap-5">
            <div class="d-flex flex-wrap gap-2">
              <VBtn
                color="primary"
                @click="refInputEl?.click()"
              >
                <VIcon
                  icon="bx-cloud-upload"
                  class="d-sm-none"
                />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              >

              <VBtn
                type="reset"
                color="secondary"
                variant="tonal"
                @click="resetAvatar"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon
                  icon="bx-refresh"
                  class="d-sm-none"
                />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, GIF or PNG. Max size of 800K
            </p>
          </form>
        </VCardText>

        <VDivider />

        <VCardText>
          <VAlert
            color="success"
            icon="mdi-info"
            text="Default Password: "
          >
            <span
              style="
                padding: 4px;
                border-radius: 20px;
                background: #e8e8e8;
                color: blue;
"
            >Password</span>
          </VAlert>
          <!-- 👉 Form -->
          <VForm class="mt-6">
            <VRow>
              <!-- 👉 First Name -->
              <VCol
                md="3"
                cols="6"
              >
                <VTextField
                  v-model="accountDataLocal.first_name"
                  placeholder="John"
                  label="First Name"
                />
              </VCol>
              <!-- 👉 Name -->
              <VCol
                md="3"
                cols="6"
              >
                <VTextField
                  v-model="accountDataLocal.last_name"
                  placeholder="Doe"
                  label="Last Name"
                />
              </VCol>

              <!-- 👉 Company Name -->
              <VCol
                md="6"
                cols="12"
              >
                <VTextField
                  v-model="accountDataLocal.company"
                  placeholder="EAKAV LLC"
                  label="Company Name"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol
                cols="12"
                md="6"
                lg="3"
              >
                <VTextField
                  v-model="accountDataLocal.email"
                  label="E-mail"
                  placeholder="johndoe@gmail.com"
                  type="email"
                />
              </VCol>

              <!-- 👉 Phone -->
              <VCol
                cols="12"
                md="6"
                lg="3"
              >
                <VTextField
                  v-model="accountDataLocal.phone"
                  label="Phone Number"
                  placeholder="1-917-543-9876"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol
                cols="12"
                md="6"
                lg="3"
              >
                <VSelect
                  v-model="accountDataLocal.role"
                  :items="['Admin', 'User']"
                  label="Role"
                  placeholder="Select a role"
                />
              </VCol>

              <!-- 👉 Address -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="accountDataLocal.address"
                  label="Address 1"
                  placeholder="123 Main St"
                />
              </VCol>

              <!-- 👉 Address -->
              <VCol
                cols="12"
                md="6"
              >
                <VTextField
                  v-model="accountDataLocal.address2"
                  label="Address 2"
                  placeholder="New York, NY 10001"
                />
              </VCol>

              <!-- 👉 City -->
              <VCol
                cols="12"
                md="6"
                sm="6"
                lg="3"
              >
                <VTextField
                  v-model="accountDataLocal.city"
                  label="City"
                  placeholder="Coral Springs"
                />
              </VCol>

              <!-- 👉 State -->
              <VCol
                cols="12"
                md="6"
                sm="6"
                lg="3"
              >
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
              <VCol
                cols="12"
                md="6"
                sm="6"
                lg="3"
              >
                <VTextField
                  v-model="accountDataLocal.zip"
                  label="Zip Code"
                  placeholder="10001"
                />
              </VCol>

              <!-- 👉 Country -->
              <VCol
                cols="12"
                md="6"
                sm="6"
                lg="3"
              >
                <VCombobox
                  v-model="accountDataLocal.country"
                  label="Country"
                  :items="countries"
                  placeholder="Select Country"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol
                cols="12"
                class="d-flex flex-wrap gap-4"
              >
                <VBtn
                  :loading="loading"
                  @click="onCreateUser"
                >
                  <template #loader>
                    <v-progress-circular
                      indeterminate
                      size="23"
                      color="red"
                    />
                  </template>
                  {{ userId ? "Update User" : "Create User" }}
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

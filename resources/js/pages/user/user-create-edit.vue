<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import ApiService from "@/services/api";
import { useToast } from "vue-toastification";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";

const accountData = {
  name: "",
  email: "",
  phone: "",
  address: "",
  avatarImg: avatar1,
  role: "user",
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
        formData.append(key, accountDataLocal.value[key]);
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
    if (userId.value) {
      // Update existing user
      res = await ApiService.updateUser(userId.value, formData, config);
      toast.success("User updated successfully!");
    } else {
      // Create new user
      res = await ApiService.createUser(formData, config);
      toast.success("User created successfully!");
    }

    router.push("/users");
  } catch (error) {
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
  <VRow>
    <VCol cols="12">
      <VCard title="Add User Detail">
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
              <VBtn color="primary" @click="refInputEl?.click()">
                <VIcon icon="bx-cloud-upload" class="d-sm-none" />
                <span class="d-none d-sm-block">Upload new photo</span>
              </VBtn>

              <input
                ref="refInputEl"
                type="file"
                name="file"
                accept=".jpeg,.png,.jpg,GIF"
                hidden
                @input="changeAvatar"
              />

              <VBtn
                type="reset"
                color="error"
                variant="tonal"
                @click="resetAvatar"
              >
                <span class="d-none d-sm-block">Reset</span>
                <VIcon icon="bx-refresh" class="d-sm-none" />
              </VBtn>
            </div>

            <p class="text-body-1 mb-0">
              Allowed JPG, GIF or PNG. Max size of 800K
            </p>
          </form>
        </VCardText>

        <VDivider />

        <VCardText>
          <!-- 👉 Form -->
          <VForm class="mt-6">
            <VRow>
              <!-- 👉 Name -->
              <VCol md="6" cols="12">
                <VTextField
                  v-model="accountDataLocal.name"
                  placeholder="John"
                  label="Name"
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

              <!-- 👉 Address -->
              <VCol cols="12" md="6">
                <VTextField
                  v-model="accountDataLocal.address"
                  label="Address"
                  placeholder="123 Main St, New York, NY 10001"
                />
              </VCol>

              <!-- 👉 Role -->
              <VCol cols="12" md="6">
                <VSelect
                  v-model="accountDataLocal.role"
                  :items="['admin', 'user']"
                  label="Role"
                  placeholder="Select a role"
                />
              </VCol>

              <!-- 👉 Form Actions -->
              <VCol cols="12" class="d-flex flex-wrap gap-4">
                <VBtn @click="onCreateUser">
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

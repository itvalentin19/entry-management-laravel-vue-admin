<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import { useRoute, useRouter } from "vue-router";
import AccountSettingsAccount from "@/views/pages/account-settings/AccountSettingsAccount.vue";
import AccountSettingsNotification from "@/views/pages/account-settings/AccountSettingsNotification.vue";
import AccountSettingsSecurity from "@/views/pages/account-settings/AccountSettingsSecurity.vue";
import { useToast } from "vue-toastification";
import { computed, watchEffect } from "vue";
import { useStore } from "vuex";
import ApiService from "@/services/api";

const route = useRoute();
const store = useStore();
const user = computed(() => store.getters.user);
const accountData = {
  name: "",
  email: "",
  phone: "",
  address: "",
  avatarImg: avatar1,
  role: "user",
};
const refInputEl = ref();
const accountDataLocal = ref(accountData);
const isAccountDeactivated = ref(false);
const toast = useToast();
const router = useRouter();
const activeTab = ref(route.params.tab);

const isCurrentPasswordVisible = ref(false);
const isNewPasswordVisible = ref(false);
const isConfirmPasswordVisible = ref(false);
const currentPassword = ref(null);
const newPassword = ref(null);
const confirmPassword = ref(null);

// tabs
const tabs = [
  {
    title: "Account",
    icon: "bx-user",
    tab: "account",
  },
  {
    title: "Security",
    icon: "bx-lock-open",
    tab: "security",
  },
  // {
  //   title: "Notifications",
  //   icon: "bx-bell",
  //   tab: "notification",
  // },
];

const resetForm = () => {
  setUserDetail();
};

const changeAvatar = (file) => {
  const fileReader = new FileReader();
  const { files } = file.target;
  if (files && files.length) {
    fileReader.readAsDataURL(files[0]);
    fileReader.onload = () => {
      if (typeof fileReader.result === "string")
        accountDataLocal.value.photo = fileReader.result;
    };
  }
};

// reset avatar image
const resetAvatar = () => {
  accountDataLocal.value.photo = accountData.photo;
};

const setUserDetail = () => {
  accountDataLocal.value = user.value;
  accountDataLocal.value.avatarImg = user.value.photo || avatar1;
  accountDataLocal.value.role = user.value.admin ? "admin" : "user";
};

watchEffect(() => {
  if (user.value) {
    setUserDetail();
  }
});

const handleUpdateUser = async () => {
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
    const config = {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    };
    const res = await ApiService.updateAccount(formData, config);
    store.dispatch("updateUser", res.data);
    toast.success("Account information updated successfully!");
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

const handleUpdatePassword = async () => {
  try {
    const formData = {
      currentPassword: currentPassword.value,
      newPassword: newPassword.value,
      confirmPassword: confirmPassword.value,
    };
    const res = await ApiService.updatePassword(formData);
    toast.success("Password updated successfully!");
    currentPassword.value = null;
    newPassword.value = null;
    confirmPassword.value = null;
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
</script>

<template>
  <div>
    <VTabs v-model="activeTab" show-arrows>
      <VTab v-for="item in tabs" :key="item.icon" :value="item.tab">
        <VIcon size="20" start :icon="item.icon" />
        {{ item.title }}
      </VTab>
    </VTabs>
    <VDivider />

    <VWindow v-model="activeTab" class="mt-5 disable-tab-transition">
      <!-- Account -->
      <VWindowItem value="account">
        <VRow>
          <VCol cols="12">
            <VCard title="Add User Detail">
              <VCardText class="d-flex">
                <!-- 👉 Avatar -->
                <VAvatar
                  rounded="full"
                  size="100"
                  class="me-6"
                  :image="accountDataLocal?.avatarImg"
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
                    <!-- <VCol cols="12" md="6">
                      <VSelect
                        v-model="accountDataLocal.role"
                        :items="['admin', 'user']"
                        label="Role"
                        placeholder="Select a role"
                      />
                    </VCol> -->

                    <!-- 👉 Form Actions -->
                    <VCol cols="12" class="d-flex flex-wrap gap-4">
                      <VBtn @click="handleUpdateUser"> Update User </VBtn>
                      <!-- <VBtn v-else @click="onCreateUser">{{ "Create User" }}</VBtn> -->
                    </VCol>
                  </VRow>
                </VForm>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>

      <!-- Security -->
      <VWindowItem value="security">
        <VRow>
          <!-- SECTION: Change Password -->
          <VCol cols="12">
            <VCard title="Change Password">
              <VForm>
                <VCardText>
                  <!-- 👉 Current Password -->
                  <VRow>
                    <VCol cols="12" md="6">
                      <!-- 👉 current password -->
                      <VTextField
                        v-model="currentPassword"
                        :type="isCurrentPasswordVisible ? 'text' : 'password'"
                        :append-inner-icon="
                          isCurrentPasswordVisible ? 'bx-hide' : 'bx-show'
                        "
                        label="Current Password"
                        placeholder="············"
                        @click:append-inner="
                          isCurrentPasswordVisible = !isCurrentPasswordVisible
                        "
                      />
                    </VCol>
                  </VRow>

                  <!-- 👉 New Password -->
                  <VRow>
                    <VCol cols="12" md="6">
                      <!-- 👉 new password -->
                      <VTextField
                        v-model="newPassword"
                        :type="isNewPasswordVisible ? 'text' : 'password'"
                        :append-inner-icon="
                          isNewPasswordVisible ? 'bx-hide' : 'bx-show'
                        "
                        label="New Password"
                        placeholder="············"
                        @click:append-inner="
                          isNewPasswordVisible = !isNewPasswordVisible
                        "
                      />
                    </VCol>

                    <VCol cols="12" md="6">
                      <!-- 👉 confirm password -->
                      <VTextField
                        v-model="confirmPassword"
                        :type="isConfirmPasswordVisible ? 'text' : 'password'"
                        :append-inner-icon="
                          isConfirmPasswordVisible ? 'bx-hide' : 'bx-show'
                        "
                        label="Confirm New Password"
                        placeholder="············"
                        @click:append-inner="
                          isConfirmPasswordVisible = !isConfirmPasswordVisible
                        "
                      />
                    </VCol>
                  </VRow>
                </VCardText>
                <!-- 👉 Action Buttons -->
                <VCardText
                  class="d-flex flex-wrap gap-4"
                  @click="handleUpdatePassword"
                >
                  <VBtn>Save changes</VBtn>
                </VCardText>
              </VForm>
            </VCard>
          </VCol>
        </VRow>
      </VWindowItem>
    </VWindow>
  </div>
</template>

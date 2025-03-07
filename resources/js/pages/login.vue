<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import axios from "axios";
import AuthProvider from "@/views/pages/authentication/AuthProvider.vue";
import logo from "@images/logo.png";
import { useRouter } from "vue-router";
import ApiService from "@/services/api";
import { useStore } from "vuex";
import { useToast } from "vue-toastification";

const form = reactive({
  email: "",
  password: "",
  remember: false,
});
const store = useStore();
const router = useRouter();
const toast = useToast();

const isPasswordVisible = ref(false);
const errorMessage = ref("");

const fieldTouched = reactive({
  email: false,
  password: false,
});

const isFormValid = computed(() => {
  const emailValid =
    form.email == "" || (form.email.includes("@") && form.email.includes("."));
  const passwordValid = form.password.length >= 8;
  return emailValid && passwordValid;
});

const handleBlur = (field) => {
  fieldTouched[field] = true;
};

const handleRememberme = (e) => {
  if (localStorage.getItem("__rm") == "true" && localStorage.getItem("__t")) {
    router.push("/");
  }
};

const login = async () => {
  console.log(form);
  if (isFormValid.value) {
    try {
      const response = await ApiService.login({
        email: form.email,
        password: form.password,
        remember: form.remember,
      });

      console.log(response.data);
      if (response.data.success) {
        localStorage.setItem("__t", response.data.response.token);
        const user = response.data.response.user;
        localStorage.setItem("__u", JSON.stringify(user));
        store.commit("SET_USER", user);
        if (form.remember) {
          /// Add handler for remember me here.
          localStorage.setItem("__rm", form.remember);
        }
        router.push("/");
      } else {
        errorMessage.value =
          "Login failed: " + (response.data.message || "Invalid credentials");
      }
    } catch (error) {
      console.log(error);
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
  } else {
    errorMessage.value = "Please correct the errors before submitting.";
  }
};

onMounted(() => {
  handleRememberme();
});
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard class="auth-card pa-4 pt-7" min-width="448">
      <VCardItem class="justify-center">
        <template #prepend>
          <div class="d-flex">
            <VImg :src="logo" height="100" />
          </div>
        </template>
      </VCardItem>

      <VCardText class="pt-2">
        <h5 class="text-h5 mb-1">Welcome – Please login below</h5>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="login">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                autofocus
                placeholder="johndoe@email.com"
                label="Email"
                type="email"
                required
                @blur="handleBlur('email')"
                :error-messages="
                  form.email != '' &&
                  (!form.email.includes('@') || !form.email.includes('.'))
                    ? 'Invalid email'
                    : ''
                "
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'bx-hide' : 'bx-show'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
                required
                @blur="handleBlur('password')"
                :error-messages="
                  form.password != '' && form.password.length < 8
                    ? 'Password must be at least 8 characters long'
                    : ''
                "
              />

              <!-- remember me checkbox -->
              <div
                class="d-flex align-center justify-space-between flex-wrap mt-1 mb-4"
              >
                <VCheckbox v-model="form.remember" label="Remember me" />

                <RouterLink
                  class="text-primary ms-2 mb-1"
                  to="/forgot-password"
                >
                  Forgot Password?
                </RouterLink>
              </div>

              <!-- login button -->
              <VBtn block type="submit" :disabled="!isFormValid"> Login </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

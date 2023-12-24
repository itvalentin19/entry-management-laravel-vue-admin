<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import axios from "axios";
import AuthProvider from "@/views/pages/authentication/AuthProvider.vue";
import logo from "@images/logo.png";
import { useRoute, useRouter } from "vue-router";
import ApiService from "@/services/api";
import { useStore } from "vuex";
import { useToast } from "vue-toastification";

const form = reactive({
  email: null,
  password: null,
  password_confirmation: null,
  token: null,
});
const store = useStore();
const router = useRouter();
const toast = useToast();
const route = useRoute();

const isPasswordVisible = ref(false);
const isPasswordConfirmVisible = ref(false);
const errorMessage = ref("");

const fieldTouched = reactive({
  email: false,
  password: false,
  password_confirmation: false,
});

const isFormValid = computed(() => {
  const emailValid =
    form.email == "" ||
    (form.email?.includes("@") && form.email?.includes("."));
  const passwordValid = form.password?.length >= 8;
  return emailValid && passwordValid;
});

const handleBlur = (field) => {
  fieldTouched[field] = true;
};

const handleToken = (e) => {};

const resetPassword = async () => {
  if (isFormValid.value) {
    try {
      const response = await ApiService.resetPassword(form);

      console.log(response.data);
      if (response.data.success) {
        toast.success("Password updated successfully!");
        router.push("/login");
      } else {
        toast.error(response.data.message || "Invalid credentials");
      }
    } catch (error) {
      console.log(error);
      toast.error(error.message || "Network error");
    }
  } else {
    toast.error("Please correct the errors before submitting.");
  }
};

onMounted(() => {
  handleToken();
});

watch(
  route,
  async (currentRoute) => {
    form.token = currentRoute.query.token || null;
  },
  { immediate: true }
);
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
        <h5 class="text-h5 mb-1">Reset Password</h5>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="resetPassword">
          <VRow>
            <!-- email -->
            <VCol cols="12">
              <VTextField
                v-model="form.email"
                placeholder="johndoe@email.com"
                label="Email"
                type="email"
                required
                @blur="handleBlur('email')"
                :error-messages="
                  !form.email &&
                  (!form.email?.includes('@') || !form.email?.includes('.'))
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
                  form.password != '' && form.password?.length < 8
                    ? 'Password must be at least 8 characters long'
                    : ''
                "
              />
            </VCol>

            <!-- confirm password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password_confirmation"
                label="Confirm Password"
                placeholder="············"
                :type="isPasswordConfirmVisible ? 'text' : 'password'"
                :append-inner-icon="
                  isPasswordConfirmVisible ? 'bx-hide' : 'bx-show'
                "
                @click:append-inner="
                  isPasswordConfirmVisible = !isPasswordConfirmVisible
                "
                required
                @blur="handleBlur('password_confirmation')"
                :error-messages="
                  form.password_confirmation != '' &&
                  form.password_confirmation?.length < 8
                    ? 'Password must be at least 8 characters long'
                    : ''
                "
              />
            </VCol>

            <VCol cols="12">
              <VBtn block type="submit" :disabled="!isFormValid">
                Reset Password
              </VBtn>
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

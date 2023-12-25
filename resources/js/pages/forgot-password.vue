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
});
const loading = ref(false);
const store = useStore();
const router = useRouter();
const toast = useToast();
const route = useRoute();

const errorMessage = ref("");

const fieldTouched = reactive({
  email: false,
});

function validateEmail(email) {
  const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
  return re.test(String(email).toLowerCase());
}

const isFormValid = computed(() => {
  const emailValid = validateEmail(form.email);
  return emailValid;
});

const handleBlur = (field) => {
  fieldTouched[field] = true;
};

const sendResetPasswordLink = async () => {
  if (isFormValid.value) {
    try {
      loading.value = true;
      const response = await ApiService.sendResetPasswordLink(form);

      if (response.data.success) {
        toast.success(
          "Your request to reset your password has been processed successfully. Please check your email for the link to reset your password."
        );
        router.push("/login");
      } else {
        toast.error(response.data.message || "Invalid credentials");
      }
      loading.value = false;
    } catch (error) {
      console.log(error);
      let message = "";
      if (error.isAxiosError && !error.response) {
        message = error.message;
      } else if (error.response) {
        message = error.response.data.message;
      } else {
        message = error.message;
      }
      toast.error(message || "Network error");
      loading.value = false;
    }
  } else {
    toast.error("Please correct the errors before submitting.");
  }
};
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
        <h5 class="text-h5 mb-1">Enter Your Email</h5>
        <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="sendResetPasswordLink">
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

            <VCol cols="12">
              <VBtn
                block
                type="submit"
                :disabled="!isFormValid"
                :loading="loading"
              >
                Send Reset Password Link
              </VBtn>
              <div
                class="d-flex align-center justify-center flex-wrap mt-1 mb-4"
              >
                <RouterLink class="text-primary ms-2 mt-2" to="/login">
                  <VIcon icon="bx-chevrons-left"> </VIcon>
                  Log in
                </RouterLink>
              </div>
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

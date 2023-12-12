<script setup>
import { useTheme } from "vuetify";
import VerticalNavSectionTitle from "@/@layouts/components/VerticalNavSectionTitle.vue";
import upgradeBannerDark from "@images/pro/upgrade-banner-dark.png";
import upgradeBannerLight from "@images/pro/upgrade-banner-light.png";
import VerticalNavLayout from "@layouts/components/VerticalNavLayout.vue";
import VerticalNavLink from "@layouts/components/VerticalNavLink.vue";

// Components
import Footer from "@/layouts/components/Footer.vue";
import NavbarThemeSwitcher from "@/layouts/components/NavbarThemeSwitcher.vue";
import UserProfile from "@/layouts/components/UserProfile.vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import ApiService from "@/services/api";

const store = useStore();
const vuetifyTheme = useTheme();

const token = localStorage.getItem("__t");
const isAuthenticated = computed(() => store.getters.isAuthenticated);
const user = computed(() => store.getters.user);
const router = useRouter();

if (!token) {
  // No token in Local Storage, redirect to login
  router.push("/login");
} else {
  // Proceed to validate the token
  validateToken();
}

async function validateToken() {
  if (!isAuthenticated.value) {
    // request user information
    try {
      const res = await ApiService.authentication();
      if (res.data.success) {
        const token = res.data.response.token;
        const user = res.data.response.user;
        localStorage.setItem("__t", token);
        localStorage.setItem("__u", JSON.stringify(user));
        store.dispatch("updateUser", user);
      } else {
        localStorage.removeItem("__t");
        localStorage.removeItem("__u");
        router.push("/login");
      }
    } catch (error) {
      localStorage.removeItem("__t");
      localStorage.removeItem("__u");
      router.push("/login");
    }
  }
}

const upgradeBanner = computed(() => {
  return vuetifyTheme.global.name.value === "light"
    ? upgradeBannerLight
    : upgradeBannerDark;
});
</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- 👉 Vertical nav toggle in overlay mode -->
        <IconBtn
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon icon="bx-menu" />
        </IconBtn>

        <VSpacer />

        <NavbarThemeSwitcher class="me-2" />

        <UserProfile />
      </div>
    </template>

    <template #vertical-nav-content>
      <VerticalNavLink
        :item="{
          title: 'Dashboard',
          icon: 'bx-home',
          to: '/dashboard',
        }"
      />

      <!-- 👉 Users -->
      <VerticalNavSectionTitle
        :item="{
          heading: 'Users',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Users',
          icon: 'bx-user',
          to: '/users',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Add/Edit User',
          icon: 'bx-user',
          to: '/users/user',
        }"
        v-if="user?.admin"
      />
      <VerticalNavLink
        :item="{
          title: 'Account Settings',
          icon: 'mdi-account-cog-outline',
          to: '/account-settings',
        }"
      />

      <!-- 👉 Pages -->
      <VerticalNavSectionTitle
        :item="{
          heading: 'Pages',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Login',
          icon: 'bx-log-in',
          to: '/login',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Register',
          icon: 'bx-user-plus',
          to: '/register',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Error',
          icon: 'bx-info-circle',
          to: '/no-existence',
        }"
      />

      <!-- 👉 User Interface -->
      <VerticalNavSectionTitle
        :item="{
          heading: 'User Interface',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Typography',
          icon: 'mdi-alpha-t-box-outline',
          to: '/typography',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Icons',
          icon: 'bx-show',
          to: '/icons',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Cards',
          icon: 'bx-credit-card',
          to: '/cards',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Tables',
          icon: 'bx-table',
          to: '/tables',
        }"
      />
      <VerticalNavLink
        :item="{
          title: 'Form Layouts',
          icon: 'mdi-form-select',
          to: '/form-layouts',
        }"
      />
    </template>

    <template #after-vertical-nav-items>
      <!-- 👉 illustration -->
      <a
        href="https://themeselection.com/item/sneat-vuetify-vuejs-laravel-admin-template"
        target="_blank"
        rel="noopener noreferrer"
        style="margin-left: 7px"
      >
        <img
          :src="upgradeBanner"
          alt="upgrade-banner"
          transition="scale-transition"
          class="upgrade-banner mx-auto"
          style="max-width: 230px"
        />
      </a>
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>

<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}
</style>

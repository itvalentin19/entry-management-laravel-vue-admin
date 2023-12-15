<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import moment from "moment";
import { useStore } from "vuex";

const store = useStore();
const _user = computed(() => store.getters.user);
const props = defineProps({
  userList: {
    type: Array,
    required: true,
  },
});
const emit = defineEmits(["edit", "delete"]);

const editUser = (userId) => {
  console.log(userId);
  emit("edit", userId);
};

const deleteUser = (userId) => {
  emit("delete", userId);
};

const isAdmin = (user) => {
  return user.role === "admin"; // Adjust according to how roles are defined in your data
};

const onClick = (item) => {
  console.log(item);
};

const menuList = ref([
  {
    text: "Edit",
    action: editUser,
  },
  {
    text: "Delete",
    action: deleteUser,
  },
]);
</script>

<template>
  <VTable fixed-header>
    <thead>
      <tr>
        <th class="text-uppercase">Id</th>
        <th>Photo</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address 1</th>
        <th>Address 2</th>
        <th>City</th>
        <th>State</th>
        <th>Zip</th>
        <th>Country</th>
        <th>Ownership Stake</th>
        <th>Document Type</th>
        <th>Document Expiration</th>
        <th>Document</th>
        <th>Created At</th>
        <th v-if="_user?.admin == true">Actions</th>
      </tr>
    </thead>

    <tbody v-if="props.userList">
      <tr v-for="user in props.userList" :key="user.id">
        <td>
          {{ user.id }}
        </td>
        <td class="text-center">
          {{ user.first_name }}
        </td>
        <td class="text-center">
          {{ user.last_name }}
        </td>
        <td class="text-center">
          {{ user.email }}
        </td>
        <td class="text-center">
          {{ user.phone }}
        </td>
        <td class="text-center">
          {{ user.address1 }}
        </td>
        <td class="text-center">
          {{ user.address2 }}
        </td>
        <td class="text-center">
          {{ user.city }}
        </td>
        <td class="text-center">
          {{ user.state }}
        </td>
        <td class="text-center">
          {{ user.zip }}
        </td>
        <td class="text-center">
          {{ user.country }}
        </td>
        <td class="text-center">
          {{ user.ownership_stake }}
        </td>
        <td class="text-center">
          {{ user.document_type }}
        </td>
        <td class="text-center">
          {{ user.document_expiration }}
        </td>
        <td class="text-center">
          <a
            v-if="user.kyc_document?.url"
            target="_blank"
            :href="user.kyc_document?.url"
          >
            <VBtn color="transparent" size="small" icon="bx-link-alt" />
          </a>
        </td>
        <td class="text-center">
          {{ moment(user.created_at).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center" v-if="_user?.admin == true">
          <MoreBtn :menu-list="menuList" :data="user" />
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

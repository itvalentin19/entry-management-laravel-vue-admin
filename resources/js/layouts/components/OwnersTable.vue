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
        <th>FirstName</th>
        <th>LastName</th>
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
        <th>Actions</th>
      </tr>
    </thead>

    <tbody v-if="props.userList">
      <tr v-for="owner in props.userList" :key="owner.id">
        <td>
          {{ owner.id }}
        </td>
        <td class="text-center">
          {{ owner.first_name }}
        </td>
        <td class="text-center">
          {{ owner.last_name }}
        </td>
        <td class="text-center">
          {{ owner.email }}
        </td>
        <td class="text-center">
          {{ owner.phone }}
        </td>
        <td class="text-center">
          {{ owner.address1 }}
        </td>
        <td class="text-center">
          {{ owner.address2 }}
        </td>
        <td class="text-center">
          {{ owner.city }}
        </td>
        <td class="text-center">
          {{ owner.state }}
        </td>
        <td class="text-center">
          {{ owner.zip }}
        </td>
        <td class="text-center">
          {{ owner.country }}
        </td>
        <td class="text-center">
          {{ owner.ownership_stake }}
        </td>
        <td class="text-center">
          {{ owner.document_type }}
        </td>
        <td class="text-center">
          {{ moment(owner.document_expiration).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center">
          <a
            v-if="owner.kyc_document?.url"
            target="_blank"
            :href="owner.kyc_document?.url"
          >
            <VBtn color="transparent" size="small" icon="bx-link-alt" />
          </a>
        </td>
        <td class="text-center">
          {{ moment(owner.created_at).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center" v-if="_user?.id == owner.user_id">
          <MoreBtn :menu-list="menuList" :data="owner" />
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

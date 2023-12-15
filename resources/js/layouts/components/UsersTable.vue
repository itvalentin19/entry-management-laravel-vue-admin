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

const editUser = (user) => {
  emit("edit", user);
};

const deleteUser = (userId) => {
  emit("delete", userId);
};

const isAdmin = (user) => {
  return user.role === "admin"; // Adjust according to how roles are defined in your data
};
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
        <th>Created At</th>
        <th>Role</th>
        <th v-if="_user?.admin == true">Actions</th>
      </tr>
    </thead>

    <tbody v-if="props.userList">
      <tr v-for="user in props.userList" :key="user.id">
        <td>
          {{ user.id }}
        </td>
        <td class="text-center">
          <VAvatar
            rounded="full"
            size="50"
            class="me-6"
            :image="user.photo || avatar1"
          />
        </td>
        <td class="text-center">
          {{ user.name }}
        </td>
        <td class="text-center">
          {{ user.email }}
        </td>
        <td class="text-center">
          {{ user.phone }}
        </td>
        <td class="text-center">
          {{ moment(user.created_at).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center">
          {{ isAdmin(user) ? "Admin" : "" }}
        </td>
        <td class="text-center" v-if="_user?.admin == true">
          <!-- Edit and Delete Buttons -->
          <VBtn small color="primary" @click="editUser(user)">Edit</VBtn>
          <VBtn small color="error" @click="deleteUser(user.id)">Delete</VBtn>
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

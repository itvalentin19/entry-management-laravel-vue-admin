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
const sort = reactive({
  field: "id",
  order: "asc",
});
const emit = defineEmits(["edit", "delete", "sort"]);

const editUser = (user) => {
  emit("edit", user);
};

const deleteUser = (userId) => {
  emit("delete", userId);
};

const isAdmin = (user) => {
  return user.role === "admin"; // Adjust according to how roles are defined in your data
};

const handleSort = (field) => {
  sort.order =
    sort.field == field ? (sort.order == "asc" ? "desc" : "asc") : "asc";
  sort.field = field;
  emit("sort", sort);
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
        <th v-if="_user?.admin == true" />
        <th
          class="text-uppercase sortable-header"
          @click="handleSort('id')"
        >
          Id
        </th>
        <th>Photo</th>
        <th
          class="sortable-header"
          @click="handleSort('first_name')"
        >
          Name
        </th>
        <th
          class="sortable-header"
          @click="handleSort('company')"
        >
          Company Name
        </th>
        <th
          class="sortable-header"
          @click="handleSort('email')"
        >
          Email
        </th>
        <th
          class="sortable-header"
          @click="handleSort('phone')"
        >
          Phone
        </th>
        <th
          class="sortable-header"
          @click="handleSort('address')"
        >
          Address
        </th>
        <th
          class="sortable-header"
          @click="handleSort('city')"
        >
          City
        </th>
        <th
          class="sortable-header"
          @click="handleSort('state')"
        >
          State
        </th>
        <th
          class="sortable-header"
          @click="handleSort('zip')"
        >
          Zip
        </th>
        <th
          class="sortable-header"
          @click="handleSort('country')"
        >
          Country
        </th>
        <th
          class="sortable-header"
          @click="handleSort('created_at')"
        >
          Created At
        </th>
        <th
          class="sortable-header"
          @click="handleSort('role')"
        >
          Role
        </th>
      </tr>
    </thead>

    <tbody v-if="props.userList">
      <tr
        v-for="user in props.userList"
        :key="user.id"
      >
        <td
          v-if="_user?.admin == true"
          class="text-center"
        >
          <MoreBtn
            :menu-list="menuList"
            :data="user"
          />
        </td>
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
          {{ user.first_name }} {{ user.last_name }}
        </td>
        <td class="text-center">
          {{ user.company }}
        </td>
        <td class="text-center">
          {{ user.email }}
        </td>
        <td class="text-center">
          {{ user.phone }}
        </td>
        <td class="text-center">
          {{ user.address }} {{ user.address2 }}
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
          {{ moment(user.created_at).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center">
          {{ isAdmin(user) ? "Admin" : "" }}
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

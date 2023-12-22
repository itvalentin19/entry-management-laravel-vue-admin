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
  viewOnly: {
    type: Boolean,
    required: false,
  },
});
const sort = reactive({
  field: "id",
  order: "asc",
});
const emit = defineEmits(["edit", "delete", "sort"]);

const editUser = (userId) => {
  console.log(userId);
  emit("edit", userId);
};

const deleteUser = (userId) => {
  emit("delete", userId);
};

const handleSort = (field) => {
  sort.order =
    sort.field == field ? (sort.order == "asc" ? "desc" : "asc") : "asc";
  sort.field = field;
  emit("sort", sort);
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
        <th v-if="props.viewOnly == false"></th>
        <th class="text-uppercase sortable-header" @click="handleSort('id')">
          Id
        </th>
        <th class="sortable-header" @click="handleSort('first_name')">
          FirstName
        </th>
        <th class="sortable-header" @click="handleSort('last_name')">
          LastName
        </th>
        <th class="sortable-header" @click="handleSort('ownership_stake')">
          Ownership (%)
        </th>
        <th class="sortable-header" @click="handleSort('address1')">
          Address 1
        </th>
        <th class="sortable-header" @click="handleSort('address2')">
          Address 2
        </th>
        <th class="sortable-header" @click="handleSort('city')">City</th>
        <th class="sortable-header" @click="handleSort('state')">State</th>
        <th class="sortable-header" @click="handleSort('zip')">Zip</th>
        <th class="sortable-header" @click="handleSort('country')">Country</th>
        <th class="sortable-header" @click="handleSort('email')">Email</th>
        <th class="sortable-header" @click="handleSort('phone')">Phone</th>
        <th v-if="props.viewOnly == false">Document Type</th>
        <th v-if="props.viewOnly == false">Document Expiration</th>
        <th v-if="props.viewOnly == false">Document</th>
        <th
          v-if="props.viewOnly == false"
          class="sortable-header"
          @click="handleSort('created_at')"
        >
          Created On
        </th>
      </tr>
    </thead>

    <tbody v-if="props.userList">
      <tr v-for="owner in props.userList" :key="owner.id">
        <td
          class="text-center"
          v-if="_user?.id == owner.user_id && props.viewOnly == false"
        >
          <MoreBtn :menu-list="menuList" :data="owner" />
        </td>
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
          {{ owner.ownership_stake }}
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
          {{ owner.email }}
        </td>
        <td class="text-center">
          {{ owner.phone }}
        </td>
        <td class="text-center" v-if="props.viewOnly == false">
          {{ owner.document_type }}
        </td>
        <td class="text-center" v-if="props.viewOnly == false">
          {{ moment(owner.document_expiration).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center" v-if="props.viewOnly == false">
          <a
            v-if="owner.kyc_document?.url"
            target="_blank"
            :href="owner.kyc_document?.url"
          >
            <VBtn color="transparent" size="small" icon="bx-link-alt" />
          </a>
        </td>
        <td class="text-center" v-if="props.viewOnly == false">
          {{ moment(owner.created_at).format("YYYY-MM-DD") }}
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

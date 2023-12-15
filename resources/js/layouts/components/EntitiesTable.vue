<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import moment from "moment";
import { useStore } from "vuex";

const store = useStore();
const _user = computed(() => store.getters.user);
const props = defineProps({
  data: {
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
        <th>Firm Name</th>
        <th>DBA Name</th>
        <th>Entity name</th>
        <th>Address 1</th>
        <th>Address 2</th>
        <th>City</th>
        <th>State</th>
        <th>Zip</th>
        <th>Country</th>
        <th>Type</th>
        <th>Services</th>
        <th>First Tax Year</th>
        <th>Directors</th>
        <th>Contact First Name</th>
        <th>Contact Last Name</th>
        <th>Contact Phone</th>
        <th>Contact Email</th>
        <th>EIN Number</th>
        <th>Form ID</th>
        <th>Signed Date</th>
        <th>Person</th>
        <th>Jurisdiction</th>
        <th>Owners</th>
        <th>Documents</th>
        <th>Notes</th>
        <th>Ref By</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody v-if="props.data">
      <tr v-for="entity in props.data" :key="entity.id">
        <td>
          {{ entity.id }}
        </td>
        <td class="text-center text-sm">
          {{ entity.firm_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.doing_business_as }}
        </td>
        <td class="text-center text-sm">
          {{ entity.entity_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.address_1 }}
        </td>
        <td class="text-center text-sm">
          {{ entity.address_2 }}
        </td>
        <td class="text-center text-sm">
          {{ entity.city }}
        </td>
        <td class="text-center text-sm">
          {{ entity.state }}
        </td>
        <td class="text-center text-sm">
          {{ entity.zip }}
        </td>
        <td class="text-center text-sm">
          {{ entity.country }}
        </td>
        <td class="text-center text-sm">
          {{ entity.type }}
        </td>
        <td class="text-center text-sm">
          <span
            style="white-space: nowrap"
            class="text-sm"
            v-for="(service, index) of entity.services?.split(',')"
            :key="service"
          >
            {{ service }}: ${{ entity.annual_fees?.split(",")[index] }}, <br />
          </span>
        </td>
        <td class="text-center text-sm">
          {{ entity.first_tax_year }}
        </td>
        <td class="text-center text-sm">
          <VCheckbox v-model="entity.directors" readonly />
        </td>
        <td class="text-center text-sm">
          {{ entity.contact_first_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.contact_last_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.contact_phone }}
        </td>
        <td class="text-center text-sm">
          {{ entity.contact_email }}
        </td>
        <td class="text-center text-sm">
          {{ entity.ein_number }}
        </td>
        <td class="text-center text-sm">
          <VCheckbox v-model="entity.form_id" readonly />
        </td>
        <td class="text-center text-sm">
          {{ moment(entity.date_signed).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center text-sm">
          {{ entity.person }}
        </td>
        <td class="text-center text-sm">
          {{ entity.jurisdiction }}
        </td>
        <td class="text-center text-sm">
          <span
            style="white-space: nowrap"
            class="text-sm"
            v-for="(owner, index) of entity.owners"
            :key="index"
          >
            {{ owner.first_name }} {{ owner.last_name }}, <br />
          </span>
        </td>
        <td class="text-center text-sm" style="white-space: nowrap">
          <span
            style="white-space: nowrap"
            class="text-sm"
            v-for="(document, index) of entity.documents"
            :key="index"
          >
            <a v-if="document?.url" target="_blank" :href="document?.url">
              <VBtn color="transparent" size="small" icon="bx-link-alt" />
            </a>
          </span>
        </td>
        <td class="text-center text-sm">
          {{ entity.notes }}
        </td>
        <td class="text-center text-sm">
          <span
            style="white-space: nowrap"
            class="text-sm"
            v-for="ref in entity.ref_by?.split(',')"
            :key="ref"
          >
            {{ ref }}, <br />
          </span>
        </td>
        <td class="text-center text-sm">
          {{ moment(entity.created_at).format("YYYY-MM-DD HH:mm:ss") }}
        </td>
        <td class="text-center text-sm" v-if="_user?.id == entity.user_id">
          <MoreBtn :menu-list="menuList" :data="entity" />
        </td>
      </tr>
    </tbody>
  </VTable>
</template>

<script setup>
import avatar1 from "@images/avatars/avatar-1.png";
import moment from "moment";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

const store = useStore();
const router = useRouter();
const _user = computed(() => store.getters.user);
const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
});
const sort = reactive({
  field: "id",
  order: "asc",
});
const emit = defineEmits(["view", "edit", "delete", "report", "sort"]);

const viewUser = (id) => {
  console.log(id);
  // emit("view", id);
  router.push("/entities/entity/" + id);
};
const editUser = (id) => {
  console.log(id);
  // emit("edit", id);
  router.push("/entities/entity?id=" + id);
};

const handleReport = (id) => {
  // Download report for an entity
  emit("report", id);
};

const deleteUser = (id) => {
  emit("delete", id);
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
    text: "View",
    action: viewUser,
  },
  {
    text: "Edit",
    action: editUser,
  },
  {
    text: "Report",
    action: handleReport,
  },
  {
    text: "Delete",
    action: deleteUser,
  },
]);

function parseAndFormatDate(dateStr) {
  if (dateStr.includes("/")) {
    const [month, day, year] = dateStr.split("/").map(Number);
    // Adjust the year to the correct century
    const adjustedYear = year < 50 ? 2000 + year : 1900 + year;

    // Create a new Date object
    const date = new Date(adjustedYear, month - 1, day);

    // Format the date as YYYY-MM-DD
    return date.toISOString().split("T")[0];
  } else {
    return moment(dateStr).format("YYYY-MM-DD");
  }
}
</script>

<template>
  <VTable fixed-header>
    <thead>
      <tr>
        <th></th>
        <th class="text-uppercase sortable-header" @click="handleSort('id')">
          Id
        </th>
        <th class="sortable-header" @click="handleSort('firm_name')">
          Firm Name
        </th>
        <th class="sortable-header" @click="handleSort('entity_name')">
          Entity name
        </th>
        <th class="sortable-header" @click="handleSort('doing_business_as')">
          DBA Name
        </th>
        <th class="sortable-header" @click="handleSort('address_1')">
          Address 1
        </th>
        <th class="sortable-header" @click="handleSort('address_2')">
          Address 2
        </th>
        <th class="sortable-header" @click="handleSort('city')">City</th>
        <th class="sortable-header" @click="handleSort('state')">State</th>
        <th class="sortable-header" @click="handleSort('zip')">Zip</th>
        <th class="sortable-header" @click="handleSort('country')">Country</th>
        <!-- <th>Type</th>
        <th>Services</th>
        <th>First Tax Year</th>
        <th>Directors</th>
        <th>Contact First Name</th>
        <th>Contact Last Name</th>
        <th>Contact Phone</th>
        <th>Contact Email</th>
        <th>EIN Number</th>
        <th>Created Date</th>
        <th>Form ID</th>
        <th>Signed Date</th>
        <th>Person</th>
        <th>Jurisdiction</th>
        <th>Owners</th>
        <th>Documents</th>
        <th>Notes</th>
        <th>Ref By</th> -->
        <!-- <th>Created On</th> -->
      </tr>
    </thead>

    <tbody v-if="props.data">
      <tr
        v-for="entity in props.data"
        :key="entity.id"
        @click="viewUser(entity.id)"
      >
        <td class="text-center text-sm" v-if="_user?.id == entity.user_id">
          <MoreBtn :menu-list="menuList" :data="entity" />
        </td>
        <td>
          {{ entity.id }}
        </td>
        <td class="text-center text-sm">
          {{ entity.firm_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.entity_name }}
        </td>
        <td class="text-center text-sm">
          {{ entity.doing_business_as }}
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
        <!-- <td class="text-center text-sm">
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
          {{ parseAndFormatDate(entity.date_created) }}
        </td>
        <td class="text-center text-sm">
          <VCheckbox v-model="entity.form_id" readonly />
        </td>
        <td class="text-center text-sm">
          {{ parseAndFormatDate(entity.date_signed) }}
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
        </td> -->
      </tr>
    </tbody>
  </VTable>
</template>

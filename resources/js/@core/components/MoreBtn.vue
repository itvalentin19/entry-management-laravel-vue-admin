<script setup>
const props = defineProps({
  menuList: {
    type: Array,
    required: false,
  },
  itemProps: {
    type: Boolean,
    required: false,
  },
  data: {
    type: Object,
    required: false,
  },
});

const actions = ["Edit", "Delete", "View", "Report"];

const onMenuItemClick = (item) => {
  if (typeof item.action === "function") {
    if (actions.includes(item.text)) {
      item.action(props.data?.id);
    }
  }
};
</script>

<template>
  <IconBtn>
    <VIcon icon="bx-dots-vertical" />

    <VMenu v-if="props.menuList" activator="parent">
      <VCard>
        <VListItem
          v-for="(item, index) in props.menuList"
          :key="index"
          @click="() => onMenuItemClick(item)"
        >
          <VIcon v-if="item.icon" :icon="item.icon" />
          {{ item.text }}
        </VListItem>
      </VCard>
    </VMenu>
  </IconBtn>
</template>

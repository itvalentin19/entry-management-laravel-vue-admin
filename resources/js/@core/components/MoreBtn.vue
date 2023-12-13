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

const onMenuItemClick = (item) => {
  if (typeof item.action === "function") {
    if (item.text == "Edit" || item.text == "Delete") {
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
        <template v-for="(item, index) in props.menuList" :key="index">
          <VListItem @click="() => onMenuItemClick(item)">
            <VListItemIcon v-if="item.icon">
              <VIcon :icon="item.icon" />
            </VListItemIcon>
            <VListItemContent>{{ item.text }}</VListItemContent>
          </VListItem>
        </template>
      </VCard>
    </VMenu>
  </IconBtn>
</template>

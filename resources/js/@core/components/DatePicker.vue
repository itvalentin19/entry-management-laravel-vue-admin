<script setup>
const menu1 = ref(false);
const dateFormatted = ref(new Date().toISOString());
const props = defineProps({
  date: {
    type: String,
    required: false,
  },
});
const dateCopy = ref(props.date);
const parseDate = (date) => {
  if (!date) return null;

  const [month, day, year] = date.split("/");
  return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
};
const formatDate = (date) => {
  if (!date) return null;

  const [year, month, day] = date.split("-");
  return `${month}/${day}/${year}`;
};
const updateDate = (date) => {
  menu1.value = false;
};
const date = (val) => {
  dateFormatted.value = formatDate(dateCopy.value);
};
</script>

<template>
  <v-menu
    ref="menu1"
    v-model="menu1"
    :close-on-content-click="false"
    :nudge-right="40"
    lazy
    transition="scale-transition"
    offset-y
    full-width
    max-width="290px"
    min-width="290px"
  >
    <template v-slot:activator="{ on }">
      <v-text-field
        v-model="dateFormatted"
        label="Date"
        hint="MM/DD/YYYY format"
        persistent-hint
        prepend-icon="mdi-calendar"
        @blur="$emit('update:date', parseDate(dateFormatted))"
        :v-on="on"
      ></v-text-field>
    </template>
    <v-date-picker
      v-model="dateCopy"
      no-title
      @input="updateDate(date)"
    ></v-date-picker>
  </v-menu>
</template>

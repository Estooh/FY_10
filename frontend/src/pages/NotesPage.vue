<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar class="">
        <div class="text-h6">Notes</div>
        <q-space />
        <q-btn flat dense icon="filter_list" class="q-mr-xs" label="Filter By">
          <q-menu>
            <q-list style="min-width: 100px">
              <q-item clickable>
                <q-checkbox v-model="checked_subject" label="Subjects" />
              </q-item>
              <q-item clickable>
                <q-item-section>
                  <q-checkbox v-model="checked_date" label="Date" />
                </q-item-section>
              </q-item>

              <q-item clickable>
                <q-item-section>
                  <q-checkbox v-model="checked_price" label="Price" />
                </q-item-section>
              </q-item>
              <q-item clickable>
                <q-item-section>
                  <q-checkbox v-model="checked_levels" label="Levels" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
        <!-- <q-btn flat round dense icon="more_vert" /> -->
      </q-toolbar>
      <q-banner
        inline-actions
        class=""
        v-if="
          checked_date || checked_levels || checked_price || checked_subject
        "
      >
        You have lost connection to the internet. This app is offline.
        <template v-slot:action>
          <q-btn outline color="primary" label="Apply" />
        </template>
      </q-banner>
      <q-table
        grid
        flat
        bordered
        card-class="bg-primary text-white"
        :rows="rows"
        :columns="columns"
        row-key="name"
        :filter="filter"
        hide-header
      >
        <template #top-right>
          <q-input
            outlined
            dense
            debounce="300"
            v-model="filter"
            placeholder="Search"
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>

        <template #item="props">

          <div class="q-pa-xs col-xs-12 col-sm-6 col-md-3">

            <q-btn
              class="my-card text"
              style="text-transform: unset; text-align: start"
              :to="'/app/notes/'+props.row.notes_id"
            >
              <img
                src="https://cdn.quasar.dev/img/mountains.jpg"
                style="width: 100%; height: auto"
              />

              <q-card-section>
                <div class="text-h6" style="text-align: start">
                  {{ props.row.title }}
                </div>
                <div class="text-subtitle2" style="text-align: start">
                  <!-- by Ombeni Nade -->
                  <span class="q-ml-md">
                    <q-badge color="blue"> {{ props.row.price }} TSH </q-badge></span
                  >
                </div>
              </q-card-section>

            </q-btn>
          </div>
        </template>
      </q-table>

    </div>
  </q-page>
</template>

<script setup>


import { api } from "src/boot/axios";
import { ref } from "vue";
import { onMounted } from "vue";
const checked_date = ref(false);
const checked_subject = ref(false);
const checked_price = ref(false);
const checked_levels = ref(false);
const filter = ref("");
// const server_url =process.env.;
const columns = ref([
  {
    name: "desc",
    label: "Dessert (100g serving)",
    align: "left",
    field: (row) => row.name,
  },
  {
    name: "calories",
    align: "center",
    label: "Calories",
    field: "calories",
    sortable: true,
  },
]);

const rows = ref([
]);

const getNotes= async ()=>{
      try {
        const { data } = await api.get('api/get-notes');
        if (data.length > 0) {
           rows.value = data;
        }
      } catch (error) {
        // handle error
      }
    }

onMounted(async () => {
  getNotes();
});
</script>

<style>
.my-card {
  width: 100%;
  font-weight: normal;
  padding: 2px 10px;
}
</style>

<template>
  <q-page padding>
    <!-- content -->
    <span class="text-h6">My Homework</span>
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
            >
              <img
                src="https://cdn.quasar.dev/img/mountains.jpg"
                style="width: 100%; height: auto"
              />

              <q-card-section>
                <div class="text-subtitle2" style="">
                  {{ props.row.title }} Title alskdkakskfkasfkakweraksdfkasf
                </div>
                <div class="text-subtitle2 row q-mt-md">
                   <q-btn  icon="view_kanban" class="col" color="primary" label="View Results" :to="'/app/homework-results/'+props.row.quiz_id">
                    <q-tooltip>View Results</q-tooltip>
                   </q-btn>
                </div>
              </q-card-section>

              <q-card-section class="q-pt-none" style="text-align: start">
                {{props.row.desc}}
              </q-card-section>
            </q-btn>
          </div>
        </template>
      </q-table>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useHomework } from "src/stores/homework";
const Store=useHomework();
const rows=ref([
]);
const columns=ref([

]);
const filter = ref("");


onMounted(async () => {
  rows.value=Store.homework;
});
</script>

<style scoped>
.my-card {
  width: 100%;
  font-weight: normal;
  padding: 2px 10px;
}
</style>

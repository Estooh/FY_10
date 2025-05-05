<template>
  <q-page padding>
    <div class="text-h6">Notes Details</div>
    <div class="text-h5 text-capitalize"> {{ notes.title }}</div>
    <div class="row">
      <div class="col-md-8 col-sm-12 col-xs-12" style="position: relative">

        <q-img :src="server_url+notes.image_url" style="width: 100%; height: auto" />
        <div
          style="
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.541);
          "
          class="column items-center justify-center"
        >
          <!-- <q-btn
            color="primary"
            class=""
            style="text-transform: unset"
            rounded=""
            :label="'Download for '+notes.price+' Tsh only'"
            icon="download"
          ></q-btn> -->

          <q-btn label="Buy Notes" type="submit" color="primary" class="q-mt-md" to="/app/payment/1/[500,'chemistry']"/>
        </div>
      </div>
      <div class="col-md-4 col-sm-12 col-xs-12">
        <div class="q-ml-lg">
         <span class="text-h6 text-center">Course content</span>
          <q-list style="overflow-y: auto; max-height: 500px;">

            <q-item
              v-for="item,i in notes.content_list"
              :key="i"
              clickable
              class="row items-center"
              style="padding-left: -13px"

            >
              <q-icon
                size="25px"
                name="chevron_right"
                class="q-mr-sm"
              />
              <q-item-section>{{ item }}</q-item-section>
            </q-item>
          </q-list>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="q-ma-sm">

          {{ notes.desc }}
        </div>
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12 text-center">
        <!-- <q-btn
          color="primary"
          class=""
          style="text-transform: unset"
          rounded=""
          :label="'Download for '+notes.price+' Tsh only'"
          icon="download"
        ></q-btn> -->
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { api } from 'src/boot/axios';
import { useRoute } from 'vue-router'


const notes = ref([]);
const server_url = process.env.API_URL;
const getNotes = async () => {
  const route = useRoute()
  const id = route.params.notes_id;
  const response = await api.get(`/api/notes/${id}`)
  notes.value = response.data
}

onMounted(async () => {
    await getNotes()
})
</script>

<style scoped>
.q-item{
  padding: 0px 0px !important;
}
</style>

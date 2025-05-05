<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar class="">
        <q-btn  icon="arrow_back" flat="" round="" color="primary" class="q-mr-sm" to="/c/questions/1/"></q-btn>
        <div class="text-h6">Add Answers</div>
        <q-space />

      </q-toolbar>

      <q-table
        flat
        bordered
        :rows="rows"
        :columns="columns"
        row-key="name"
        :filter="filter"
      >
      <template #top-left>
        <q-btn
          icon="add"
          label="Answer"
          color="primary"
          @click="dialog = true"
        />
        </template>
        <template #top-right>
          <q-input
            outlined
            dense
            debounce="300"
            v-model="filter"
            placeholder="Search"
          >
            <template #append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>

        <template #body-cell-actions>
          <q-td>
            <q-btn dense flat icon="edit" color="primary" @click="editRow" />
            <q-btn dense flat icon="delete" color="negative" />
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td>
            <q-badge v-if="props.row.status=='Correct'" dense flat :label="props.row.status" color="green"/>
            <q-badge v-if="props.row.status=='Wrong'" dense flat :label="props.row.status" color="red"/>
          </q-td>
        </template>
      </q-table>
    </div>
    <!-- Dialogs -->
    <q-dialog
      v-model="dialog"
      persistent
      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card class="">
        <q-bar>
          <q-space />

          <q-btn
            dense
            flat
            icon="minimize"
            @click="maximizedToggle = false"
            :disable="!maximizedToggle"
          >
            <q-tooltip
              v-if="maximizedToggle"
              class="bg-white text-primary"
            ></q-tooltip>
          </q-btn>
          <q-btn
            dense
            flat
            icon="crop_square"
            @click="maximizedToggle = true"
            :disable="maximizedToggle"
          >
            <q-tooltip
              v-if="!maximizedToggle"
              class="bg-white text-primary"
            ></q-tooltip>
          </q-btn>
          <q-btn dense flat icon="close" v-close-popup>
            <q-tooltip class="bg-white text-primary"></q-tooltip>
          </q-btn>
        </q-bar>

        <q-card-section>
          <div class="text-h6">Create new Answer</div>
        </q-card-section>

        <q-card-section  class="q-pt-none">
          <q-stepper v-model="step" vertical color="primary" animated flat>
            <q-step
              :name="1"
              title="Question details"
              prefix="1"
              :done="step > 1"
            >
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-input  label="Write an answer here.." v-model="answers_data.answer" outlined />
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select label="Select answer type" outlined v-model="answers_data.status" :options="['Wrong','Correct']" />
                  </div>
                </div>
              </div>
              <q-stepper-navigation>
                <q-btn color="primary" label="Save" @click="saveAnswer()"/>
              </q-stepper-navigation>
            </q-step>
          </q-stepper>
        </q-card-section>

      </q-card>
    </q-dialog>
    <!-- Dialogs -->
  </q-page>
</template>

<script setup>
import { reactive } from "vue";
import { ref } from "vue";
import { api } from "boot/axios";
import { useRouter,useRoute } from "vue-router";
import { Notify } from "quasar";
import { onMounted } from "vue";
const route = useRoute();
const dialog = ref(false);
const maximizedToggle = ref(true);
const filter = ref("");
const step = ref(1);
const answers_data = reactive({
  answer: "",
  status: "",
  question_id: route.params.question_id,
});

const columns = ref([

  {
    name: "answer",
    align: "left",
    label: "Answers",
    field: (row) => row.answer,
  },
  {
    name: "status",
    label: "Status",
    align: "left",
  },
  {
    name: "actions",
    align: "center",
    label: "Actions",
  },
]);
const rows = ref([]);

const saveAnswer = async () => {
  try {

    const { data } = await api.post(
      "api/answersToQuestion",
      answers_data
    );
    if(data.status==='okay'){
      dialog.value=false;
      Notify.create({
        'type':'positive',
        'message':'Answer added successfully',
        'position':'bottom'
      })
    }else if(data.status==='notokay'){
      dialog.value=false;
      Notify.create({
        'type':'negative',
        'message':data.message,
        'position':'bottom'
      })
    }
    console.log(data);
  } catch (error) {
    console.log(error);
  }

};

const getAnswers=async()=>{
  try {
    const { data } = await api.get("api/getAnswersToQuestion/"+route.params.question_id);
    rows.value = data.answers;
    console.log(data);
  } catch (error) {
    console.log(error);
  }
}


onMounted(async () => {
  getAnswers();
});
</script>

<style>
.my-card {
  width: 100%;
  font-weight: normal;
  padding: 2px 10px;
}
</style>

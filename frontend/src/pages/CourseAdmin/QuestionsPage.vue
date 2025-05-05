<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar>
        <q-btn icon="arrow_back" flat round color="primary" class="q-mr-sm" to="/c/home-work"></q-btn>
        <div class="text-h6">Add questions</div>
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
            label="Question"
            color="primary"
            @click="openDialog()"
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
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>
        <template #body-cell-actions="props">
          <q-td>
            <q-btn dense :to="'/c/answers/'+props.row.question_id+'/'+props.row.type" flat icon="dynamic_form" color="">
              <q-tooltip>Add new answer</q-tooltip>
            </q-btn>
            <q-btn
              dense
              flat
              icon="edit"
              @click="editRow(props.row)"
              color="primary"
            />
            <q-btn
              dense
              flat
              icon="delete"
              @click="confirmDelete(props.row)"
              color="negative"
            />
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog for Add/Edit Question -->
    <q-dialog v-model="dialog" persistent :maximized="maximizedToggle" transition-show="slide-up" transition-hide="slide-down">
      <q-card class="">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="minimize" @click="maximizedToggle = false" :disable="!maximizedToggle" />
          <q-btn dense flat icon="crop_square" @click="maximizedToggle = true" :disable="maximizedToggle" />
          <q-btn dense flat icon="close" @click="closeDialog()" />
        </q-bar>

        <q-card-section>
          <div class="text-h6">{{ editMode ? 'Edit' : 'Create' }} Question</div>
        </q-card-section>

        <q-card-section>
          <q-input label="Write a question here..." v-model="questions_data.question" outlined />
          <q-select label="Select question type" v-model="questions_data.type" :options="['Single choice', 'Multiple choice']" outlined />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Save" color="primary" @click="saveQuestion" />
          <q-btn label="Cancel" flat @click="closeDialog" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from 'boot/axios';
import { Notify, Dialog } from 'quasar';

const route = useRoute();
const router = useRouter();

const dialog = ref(false);
const maximizedToggle = ref(true);
const filter = ref('');
const step = ref(1);
const editMode = ref(false);
const rows = ref([]);
const columns = ref([
  { name: 'question', label: 'Question', align: 'left', field: row => row.question },
  { name: 'type', label: 'Question Type', align: 'left', field: row => row.type },
  { name: 'actions', label: 'Actions', align: 'left' }
]);

const questions_data = reactive({
  question: '',
  type: '',
  quiz_id: route.params.quiz_id
});

const openDialog = () => {
  questions_data.question = '';
  questions_data.type = '';
  dialog.value = true;
  editMode.value = false;
};

const closeDialog = () => {
  dialog.value = false;
};

const saveQuestion = async () => {
  try {
    const url = editMode.value ? `api/updateQuestion/${questions_data.id}` : 'api/addQuestion';
    const method = editMode.value ? 'put' : 'post';
    const { data } = await api[method](url, questions_data);

    if (data.status === 'okay') {
      dialog.value = false;
      getQuestions();
      Notify.create({
        type: 'positive',
        message: editMode.value ? 'Question updated successfully' : 'Question added successfully'
      });
    } else {
      Notify.create({
        type: 'warning',
        message: 'Question already exists'
      });
    }
  } catch (error) {
    console.error(error);
    Notify.create({
      type: 'negative',
      message: 'Failed to save question. Please try again.'
    });
  }
};

const getQuestions = async () => {
  try {
    const { data } = await api.get(`api/getQuestion/${route.params.quiz_id}`);
    rows.value = data.questions;
  } catch (error) {
    console.error(error);
  }
};

const editRow = row => {
  Object.assign(questions_data, row);
  dialog.value = true;
  editMode.value = true;
};

const confirmDelete = row => {
  Dialog.create({
    title: 'Confirm',
    message: 'Are you sure you want to delete this question?',
    ok: { label: 'Yes', color: 'negative' },
    cancel: { label: 'No', color: 'primary' }
  }).onOk(async () => {
    try {
      await api.delete(`api/deleteQuestion/${row.id}`);
      getQuestions();
      Notify.create({
        type: 'positive',
        message: 'Question deleted successfully'
      });
    } catch (error) {
      console.error(error);
      Notify.create({
        type: 'negative',
        message: 'Failed to delete question. Please try again.'
      });
    }
  });
};

getQuestions();
</script>

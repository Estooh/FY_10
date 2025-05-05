<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar>
        <div class="text-h6">Homework/Quiz</div>
        <q-space />
      </q-toolbar>

      <q-banner
        inline-actions
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
            label="Homework/Quiz"
            color="primary"
            @click="openDialog"
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

        <template #body-cell-quiz_preview="props">
          <q-td>
            <q-card class="my-card" flat>
              <q-card-section>
                <q-img :src="url + props.row.image_url" />
              </q-card-section>
            </q-card>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td>
            <q-btn
              dense
              :to="'/c/questions/' + props.row.quiz_id"
              flat
              icon="dynamic_form"
            >
              <q-tooltip>Add new question</q-tooltip>
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
        <template #body-cell-picture="props">
          <q-td class="text-center">
            <q-img :src="url + '/' + props.row.image_url" />
          </q-td>
        </template>
      </q-table>
    </div>

    <!-- Dialog for Creating/Editing Quiz -->
    <q-dialog
      v-model="dialog"
      persistent
      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
    >
      <q-card>
        <q-bar>
          <q-space />
          <q-btn
            dense
            flat
            icon="minimize"
            @click="maximizedToggle = false"
            :disable="!maximizedToggle"
          />
          <q-btn
            dense
            flat
            icon="crop_square"
            @click="maximizedToggle = true"
            :disable="maximizedToggle"
          />
          <q-btn dense flat icon="close" v-close-popup />
        </q-bar>

        <q-card-section>
          <div class="text-h6">Create new quiz</div>
        </q-card-section>

        <q-card-section>
    <q-stepper v-model="step" vertical color="primary" animated flat>
      <q-step
        :name="1"
        title="Homework/quiz details"
        prefix="1"
        :done="step > 1"
      >
        <div class="row">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="q-ma-sm">
              <q-select
                label="Select Level"
                v-model="quiz_data.level"
                :options="levels"
                outlined
              />
            </div>
          </div>
          <div class="col-md-6 col-sm-12 col-xs-12">
            <div class="q-ma-sm">
              <q-select
                label="Select subject"
                v-model="quiz_data.subject_id"
                :options="subjects"
                outlined
              />
            </div>
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="q-ma-sm">
              <q-input label="Price" v-model="quiz_data.price" outlined />
            </div>
          </div>
        </div>

        <div class="row q-pr-md">
          <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
            <q-input
              label="Quiz title"
              outlined
              v-model="quiz_data.title"
            />
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
            <q-select
              label="Quiz Duration type"
              outlined
              v-model="quiz_data.duration_type"
              :options="['Days','Hours','Minutes']"
            />
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
            <q-input
              label="Quiz Duration"
              outlined
              v-model="quiz_data.duration"
            />
          </div>

          <!-- Added Description Field -->
          <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
            <q-input
              label="Quiz Description"
              outlined
              v-model="quiz_data.desc"
              type="textarea"
              autogrow
            />
          </div>

          <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
            <q-uploader
              auto-upload
              max-files="1"
              field-name="image_url"
              :url="uploader_url + 'api/uploader?type=thumbnailQuiz'"
              style="width: 100%"
              label="Upload thumbnail or drag and drop thumbnail image here"
              accept=".jpg,.png,.jpeg image/*"
              max-file-size="4000000"
              @uploaded="thumbnailUrl"
              @rejected="
                $q.notify({
                  type: 'negative',
                  message: 'Please upload only image and max size of 4 MB',
                  position: 'bottom',
                })
              "
            />
          </div>
        </div>

        <div class="row q-ma-sm" style="border: 1px solid blue">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="q-ma-sm">
              <q-input
                outlined
                dense
                label="Start date"
                v-model="quiz_data.start_date"
              >
                <template #prepend>
                  <q-icon name="event" class="cursor-pointer">
                    <q-popup-proxy
                      cover
                      transition-show="scale"
                      transition-hide="scale"
                    >
                      <q-date
                        v-model="quiz_data.start_date"
                        mask="YYYY-MM-DD HH:mm"
                      >
                        <div class="row items-center justify-end">
                          <q-btn
                            v-close-popup
                            label="Close"
                            color="primary"
                            flat
                          />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>

                <template #append>
                  <q-icon name="access_time" class="cursor-pointer">
                    <q-popup-proxy
                      cover
                      transition-show="scale"
                      transition-hide="scale"
                    >
                      <q-time
                        v-model="quiz_data.start_date"
                        mask="YYYY-MM-DD HH:mm"
                        format24h
                      >
                        <div class="row items-center justify-end">
                          <q-btn
                            v-close-popup
                            label="Close"
                            color="primary"
                            flat
                          />
                        </div>
                      </q-time>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
          </div>
        </div>

        <q-stepper-navigation>
          <q-btn
            color="primary"
            label="Save"
            @click="addQuiz()"
            :loading="adding_quiz"
          />
        </q-stepper-navigation>
      </q-step>
    </q-stepper>
  </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { ref, reactive, onMounted } from "vue";
import { Notify, Dialog } from "quasar";
import { api } from "boot/axios";

export default {
  setup() {
    const dialog = ref(false);
    const maximizedToggle = ref(true);
    const filter = ref("");
    const step = ref(1);
    const editMode = ref(false);
    const adding_quiz = ref(false);
    const rows = ref([]);
    const uploader_url = process.env.API_URL

    const quiz_data = reactive({
      level: "",
      subject_id: "",
      start_date: "",
      end_date: "",
      title: "",
      image_url: "",
      desc: "",
      price: "",
      duration: "",
      user_id: 1,
      duration_type: "",
    });

    const levels = ref([
      { label: "Primary", value: 1 },
      { label: "Secondary", value: 2 },
    ]);

    const subjects = ref([
      { label: "Mathematics", value: 1 },
      { label: "Physics", value: 2 },
      { label: "Chemistry", value: 3 },
      { label: "Biology", value: 4 },
    ]);

    const columns = ref([
      {
        name: "quiz_preview",
        label: "Quiz preview",
        align: "left",
      },
      {
        name: "quiz_title",
        align: "left",
        label: "Quiz title",
        field: (row) => row.title,
      },
      {
        name: "quiz_subject",
        align: "left",
        label: "Quiz Subject",
        field: (row) => row.name,
      },
      {
        name: "start_date",
        align: "left",
        label: "Start Date",
        field: (row) => row.name,
      },
      {
        name: "end_date",
        align: "left",
        label: "End Date",
        field: (row) => row.name,
      },
      {
        name: "actions",
        align: "center",
        label: "Actions",
      },
    ]);


    const thumbnailUrl = (response) => {
      quiz_data.image_url = response.data.url;
    };


    const openDialog = () => {
      resetQuizData();
      dialog.value = true;
      editMode.value = false;
    };

    const resetQuizData = () => {
      quiz_data.level = "";
      quiz_data.subject_id = "";
      quiz_data.start_date = "";
      quiz_data.end_date = "";
      quiz_data.title = "";
      quiz_data.image_url = "";
      quiz_data.desc = "";
      quiz_data.price = "";
      quiz_data.duration = "";
      quiz_data.duration_type = "";
      // Preserve user_id
      quiz_data.user_id = 1;
    };

    const addQuiz = async () => {
  try {
    adding_quiz.value = true;

    // Calculate the end date based on the start date and duration
    const startDate = new Date(quiz_data.start_date);
    let endDate;
    if (quiz_data.duration_type === "Days") {
      endDate = new Date(startDate.getTime() + quiz_data.duration * 24 * 60 * 60 * 1000);
    } else if (quiz_data.duration_type === "Hours") {
      endDate = new Date(startDate.getTime() + quiz_data.duration * 60 * 60 * 1000);
    } else if (quiz_data.duration_type === "Minutes") {
      endDate = new Date(startDate.getTime() + quiz_data.duration * 60 * 1000);
    }

    quiz_data.end_date = endDate.toISOString().slice(0, 19).replace("T", " ");

    // Decide whether to add or update the quiz based on `editMode`
    let response;
    if (editMode.value) {
      response = await api.put(`api/updateQuiz/${quiz_data.quiz_id}`, quiz_data);
    } else {
      response = await api.post("api/insertquiz", quiz_data);
    }

    if (response.status === 200) {
      Notify.create({
        type: "positive",
        message: `Quiz ${editMode.value ? 'updated' : 'added'} successfully!`,
        position: "top-right",
      });
      dialog.value = false;
      step.value = 1;
      resetQuizData();
      getQuiz(); // Refresh the quiz list
    }
  } catch (error) {
    Notify.create({
      type: "negative",
      message: `Error ${editMode.value ? 'updating' : 'adding'} quiz: ${error.message}`,
      position: "top-right",
    });
  } finally {
    adding_quiz.value = false;
  }
};



    const getQuiz = async () => {
      try {
        const { data } = await api.get("api/getQuiz/");
        if (data.length > 0) {
           rows.value = data;
        }
      } catch (error) {
        Notify.create({
          type: "negative",
          message: "Failed to fetch quizzes. Please try again later.",
        });
      }
    };

    onMounted(async () => {
      await Promise.all([getQuiz()]);
    });


    const editRow = (row) => {
      Object.keys(quiz_data).forEach((key) => {
        quiz_data[key] = row[key];
      });
      dialog.value = true;
      editMode.value = true;
    };


    const confirmDelete = (row) => {
      Dialog.create({
        title: "Confirm",
        message: "Are you sure you want to delete this quiz?",
        ok: {
          label: "Yes",
          color: "negative",
        },
        cancel: {
          label: "No",
          color: "primary",
        },
      }).onOk(async () => {
        try {
          await api.delete(`api/deleteQuiz/${row.quiz_id}`);
          Notify.create({
            type: "positive",
            message: "Quiz deleted successfully.",
          });
          getQuiz();
        } catch (error) {
          Notify.create({
            type: "negative",
            message: "Failed to delete quiz. Please try again.",
          });
        }
      });
    };

  
    return {
      dialog,
      maximizedToggle,
      filter,
      step,
      editMode,
      adding_quiz,
      rows,
      quiz_data,
      levels,
      subjects,
      columns,
      openDialog,
      editRow,
      addQuiz,
      confirmDelete,
      getQuiz,
      thumbnailUrl,
      uploader_url
    };
  },
};
</script>

<style scoped>
.my-card {
  max-width: 300px;
  max-height: 200px;
}
</style>


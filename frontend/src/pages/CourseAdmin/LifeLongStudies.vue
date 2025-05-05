<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar class="">
        <div class="text-h6">Life Long Studies</div>
        <q-space />

        <q-btn
          icon="add"
          label="Life long studies"
          color="primary"
          @click="dialog = true"
        />
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
        flat
        bordered
        :rows="rows"
        :columns="columns"
        row-key="name"
        :filter="filter"
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

        <template #body-cell-note_preview="props">
          <q-td>
            <q-card class="my-card" flat="">
              <q-card-section>
                <q-img :src="url + props.row.image_url" basic contain />
              </q-card-section>
            </q-card>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td>
            <q-btn
              dense
              flat
              icon="edit"
              color="primary"
              @click="editRow(props.row)"
            />
            <q-btn
              dense
              flat
              icon="delete"
              color="negative"
              @click="deleteRow(props.row.study_id)"
            />
          </q-td>
        </template>

        <template #body-cell-status>
          <q-td>
            <q-badge dense flat label="Approved" color="green" />
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
          <div class="text-h6">Create new Post</div>
        </q-card-section>
        <q-card-section class="q-pt-none">
          <q-stepper v-model="step" vertical color="primary" animated flat>
            <q-step :name="1" title="Details" prefix="1" :done="step > 1">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select
                      label="Select category"
                      :options="categories"
                      v-model="life_long_data.cat"
                      outlined
                    />
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-input
                      label="Title"
                      v-model="life_long_data.title"
                      outlined
                    />
                  </div>
                </div>
              </div>
              <div
                class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm q-mb-md q-mb-md"
              >
                <q-uploader
                  auto-upload=""
                  max-files="1"
                  field-name="image_url"
                  style="width: 100%"
                  label="Upload thumbnail or drag and drop thumbnail image here"
                  :url="uploader_url + 'api/uploader?type=thumbnailCategory'"
                  accept=".jpg, image/*"
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
              <div class="row q-pr-md">
                <div class="col-md-12 col-sm-12 col-xs-12 q-ml-sm">
                  <q-editor
                    v-model="life_long_data.qeditor"
                    :dense="$q.screen.lt.md"
                    :toolbar="[
                      [
                        {
                          label: $q.lang.editor.align,
                          icon: $q.iconSet.editor.align,
                          fixedLabel: true,
                          options: ['left', 'center', 'right', 'justify'],
                        },
                      ],
                      [
                        'bold',
                        'italic',
                        'strike',
                        'underline',
                        'subscript',
                        'superscript',
                      ],
                      ['token', 'hr', 'link', 'custom_btn'],
                      ['print', 'fullscreen'],
                      [
                        {
                          label: $q.lang.editor.formatting,
                          icon: $q.iconSet.editor.formatting,
                          list: 'no-icons',
                          options: [
                            'p',
                            'h1',
                            'h2',
                            'h3',
                            'h4',
                            'h5',
                            'h6',
                            'code',
                          ],
                        },
                        {
                          label: $q.lang.editor.fontSize,
                          icon: $q.iconSet.editor.fontSize,
                          fixedLabel: true,
                          fixedIcon: true,
                          list: 'no-icons',
                          options: [
                            'size-1',
                            'size-2',
                            'size-3',
                            'size-4',
                            'size-5',
                            'size-6',
                            'size-7',
                          ],
                        },
                        {
                          label: $q.lang.editor.defaultFont,
                          icon: $q.iconSet.editor.font,
                          fixedIcon: true,
                          list: 'no-icons',
                          options: [
                            'default_font',
                            'arial',
                            'arial_black',
                            'comic_sans',
                            'courier_new',
                            'impact',
                            'lucida_grande',
                            'times_new_roman',
                            'verdana',
                          ],
                        },
                        'removeFormat',
                      ],
                      ['quote', 'unordered', 'ordered', 'outdent', 'indent'],

                      ['undo', 'redo'],
                      ['viewsource'],
                    ]"
                    :fonts="{
                      arial: 'Arial',
                      arial_black: 'Arial Black',
                      comic_sans: 'Comic Sans MS',
                      courier_new: 'Courier New',
                      impact: 'Impact',
                      lucida_grande: 'Lucida Grande',
                      times_new_roman: 'Times New Roman',
                      verdana: 'Verdana',
                    }"
                  />
                </div>
              </div>
              <q-stepper-navigation>
                <q-btn color="primary" label="Save" @click="saveLIfeLong()" />
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
import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import { storageApi } from "src/boot/storageapi";
import { api } from "boot/axios";
import { onMounted } from "vue";
const checked_date = ref(false);
const checked_subject = ref(false);
const checked_price = ref(false);
const checked_levels = ref(false);
const dialog = ref(false);
const url = storageApi;
const maximizedToggle = ref(true);
const filter = ref("");
const step = ref(1);
const $q = useQuasar();
const uploader_url = process.env.API_URL;

const categories = ref();

const life_long_data = reactive({
  cat: "",
  title: "",
  qeditor: "",
  image_url: "",
});
const editRow = (id) => {
  console.log(id);
  dialog.value = true;
  // alert(id);
};
const deleteRow = async (id) => {
  alert(id);
  try {
    const { data } = await api.post("api/deleteLifeLongStudies/" + id);
    rows.value = data.data;
    $q.notify({ type: "positive", message: data.message, position: "bottom" });
  } catch (error) {
    console.log(error);
  }
};
const saveLIfeLong = async () => {
  try {
    const { data } = await api.post("api/addLifeLongStudy", life_long_data);
    rows.value = data.data;
    $q.notify({ type: "positive", message: data.message, position: "bottom" });

    dialog.value = false;
  } catch (error) {
    console.log(error);
  }
};
const thumbnailUrl = async (info) => {
  life_long_data.image_url = info.xhr.response;
};

const columns = ref([
  {
    name: "note_preview",
    label: "Preview",
    align: "left",
    field: (row) => row.image_url,
  },
  {
    name: "notes_title",
    align: "left",
    label: "Title",
    field: (row) => row.content_url,
  },
  {
    name: "category",
    align: "left",
    label: "Category",
    field: (row) => row.categoryId,
  },
  {
    name: "status",
    align: "left",
    label: "Status",
  },
  {
    name: "actions",
    align: "center",
    label: "Actions",
  },
]);

const rows = ref([]);
onMounted(async () => {
  try {
    const { data } = await api.get("api/getCategories");

    categories.value = data;
    categories.value = categories.value.map((category) => ({
      label: category.category_name,
      value: category.category_id,
    }));
    console.log(categories.value);
  } catch (error) {
    console.log(error);
  }
  try {
    const { data } = await api.get("api/getLifeLongStudies");

    rows.value = data;
    console.log(rows.value);
  } catch (error) {
    console.log(error);
  }
});
</script>

<style>
.my-card {
  width: 100%;
  font-weight: normal;
  padding: 2px 10px;
}
</style>

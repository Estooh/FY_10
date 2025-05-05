<template>
  <q-page padding>
    <div class="q-pa-xs">
      <q-toolbar class="">
        <div class="text-h6">Notes</div>
        <q-space />

        <q-btn
          icon="add"
          label="Notes"
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

                <q-img
                  :src="STORAGE_URL+props.row.image_url"
                  basic
                  contain
                />
              </q-card-section>
            </q-card>
          </q-td>
        </template>

        <template #body-cell-actions="props">
          <q-td>
            <q-btn dense flat icon="edit" color="primary" @click="editRow(props.row)" />
            <q-btn dense flat icon="delete" color="negative"  @click=" confirmDelete(props.row)"/>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td>
            <q-badge v-if="props.row.status==='pending'" dense flat label="Pending" color="orange" />
            <q-badge v-if="props.row.status==='approved'" dense flat label="Approved" color="green" />
            <q-badge v-if="props.row.status==='rejected'" dense flat label="Rejected" color="red" />
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
          <div class="text-h6">Add new notes</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-stepper v-model="step" vertical color="primary" animated flat>
            <q-step :name="1" title="Notes details" prefix="1" :done="step > 1">
              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select v-model="notes.level_id" label="Select Level" outlined  emit-value="" map-options=""  :options="[{label:'Level 1',value:1}]"/>
                  </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select
                    outlined=""
                    v-model="class_id"
                    :options="options"
                    label="Select Class"
                    multiple
                    emit-value
                    map-options
                >
                  <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">

                      <q-item-section side>
                        <!-- <q-toggle  /> -->
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)"/>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>
                          {{ opt.label }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                  </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select v-model="notes.subject_id" label="Select subject" outlined :options="[{label:'Physics',value:'1'}]" emit-value="" map-options=""/>
                  </div>
                </div>
              </div>
              <div class="row q-pr-md">
                <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
                  <q-input v-model="notes.title" label="Notes title" outlined />
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
                  <q-uploader
                    auto-upload=""
                    max-files="1"
                    field-name="image_url"
                    :url="uploader_url+'api/uploader?type=thumbnail'"
                    style="width: 100%"
                    label="Upload thumbnail or drag and drop thumbanil image here"
                    accept=".jpg, image/*"
                    max-file-size="4000000"
                    @uploaded="thumbnailUrl"
                    @rejected="$q.notify({type:'negative',message:'Please upload only image and max size of 4 MB',position:'bottom'});"
                  />
                </div>
              </div>
              <q-stepper-navigation>
                <q-btn @click="step = 2" color="primary" label="Continue" />
              </q-stepper-navigation>
            </q-step>

            <q-step
              :name="2"
              title="Pricing"
              caption="Set notes price"
              prefix="2"
              :done="step > 2"
            >
              <div class="row" style="border: 1px solid blue">
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-checkbox
                      label="Allow download"
                      v-model="notes.is_downloaded"
                    />
                  </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-input
                      dense
                      outlined=""
                      label="Set price for this course"
                      v-model="notes.price"
                    ></q-input>
                  </div>
                </div>
              </div>

              <q-stepper-navigation>
                <q-btn @click="step = 3" color="primary" label="Continue" />
                <q-btn
                  flat
                  @click="step = 1"
                  color="primary"
                  label="Back"
                  class="q-ml-sm"
                />
              </q-stepper-navigation>
            </q-step>

            <q-step
              :name="3"
              title="Notes content"
              prefix="3"
              caption="Upload notes content"
            >
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <div class="q-my-sm">
                      <q-input
                        v-model="notes.desc"
                        label="Notes description"
                        outlined
                      />
                    </div>
                    <q-uploader
                    max-files="1"
                    auto-upload=""
                    accept="application/pdf"
                    max-file-size="50000000"
                    field-name="content_url"
                    :url="uploader_url+'api/uploader?type=content'"
                    style="width: 100%"
                    label="Upload notes content or drag and drop content here"
                    @uploaded="contentUrl"
                    @rejected="$q.notify({type:'negative',message:'Please upload only PDF file format and max size of 20 MB',position:'bottom'});"
                    />
                    <div class="q-my-sm">

                      <q-card class="q-my-sm q-pa-md">
                        <q-card-section>
                          <div class="text-h6">Content list</div>
                        </q-card-section>
                        <q-card-section>
                          <q-input v-model="content_list_array" outlined="" label="What student is expecting to learn?" hint="Please separate each content by comma"/>
                        </q-card-section>

                      </q-card>
                    </div>
                  </div>
                </div>
              </div>

              <q-stepper-navigation>
                <q-btn color="primary" label="Finish" @click="createNotes()" />
                <q-btn
                  flat
                  @click="step = 2"
                  color="primary"
                  label="Back"
                  class="q-ml-sm"
                />
              </q-stepper-navigation>
            </q-step>
          </q-stepper>
        </q-card-section>
      </q-card>
    </q-dialog>


    <q-dialog
      v-model="edit_dialog"
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
          <div class="text-h6">Add new notes</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-stepper v-model="step" vertical color="primary" animated flat>
            <q-step :name="1" title="Notes details" prefix="1" :done="step > 1">
              <div class="row">
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select v-model="notes.level_id" label="Select Level" outlined  emit-value="" map-options=""  :options="[{label:'Level 1',value:1}]"/>
                  </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select
                    outlined=""
                    v-model="class_id"
                    :options="options"
                    label="Select Class"
                    multiple
                    emit-value
                    map-options
                >
                  <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">

                      <q-item-section side>
                        <!-- <q-toggle  /> -->
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)"/>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>
                          {{ opt.label }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                  </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-select v-model="notes.subject_id" label="Select subject" outlined :options="[{label:'Physics',value:'1'}]" emit-value="" map-options=""/>
                  </div>
                </div>
              </div>
              <div class="row q-pr-md">
                <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
                  <q-input v-model="notes.title" label="Notes title" outlined />
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 q-my-sm q-mx-sm">
                  <q-uploader
                    auto-upload=""
                    max-files="1"
                    field-name="image_url"
                    :url="uploader_url+'api/uploader?type=thumbnail'"
                    style="width: 100%"
                    label="Upload thumbnail or drag and drop thumbanil image here"
                    accept=".jpg, image/*"
                    max-file-size="4000000"
                    @uploaded="thumbnailUrl"
                    @rejected="$q.notify({type:'negative',message:'Please upload only image and max size of 4 MB',position:'bottom'});"
                  />
                </div>
              </div>
              <q-stepper-navigation>
                <q-btn @click="step = 2" color="primary" label="Continue" />
              </q-stepper-navigation>
            </q-step>

            <q-step
              :name="2"
              title="Pricing"
              caption="Set notes price"
              prefix="2"
              :done="step > 2"
            >
              <div class="row" style="border: 1px solid blue">
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-checkbox
                      label="Allow download"
                      v-model="notes.is_downloaded"
                    />
                  </div>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <q-input
                      dense
                      outlined=""
                      label="Set price for this course"
                      v-model="notes.price"
                    ></q-input>
                  </div>
                </div>
              </div>

              <q-stepper-navigation>
                <q-btn @click="step = 3" color="primary" label="Continue" />
                <q-btn
                  flat
                  @click="step = 1"
                  color="primary"
                  label="Back"
                  class="q-ml-sm"
                />
              </q-stepper-navigation>
            </q-step>

            <q-step
              :name="3"
              title="Notes content"
              prefix="3"
              caption="Upload notes content"
            >
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="q-ma-sm">
                    <div class="q-my-sm">
                      <q-input
                        v-model="notes.desc"
                        label="Notes description"
                        outlined
                      />
                    </div>
                    <q-uploader
                    max-files="1"
                    auto-upload=""
                    accept="application/pdf"
                    max-file-size="50000000"
                    field-name="content_url"
                    :url="uploader_url+'api/uploader?type=content'"
                    style="width: 100%"
                    label="Upload notes content or drag and drop content here"
                    @uploaded="contentUrl"
                    @rejected="$q.notify({type:'negative',message:'Please upload only PDF file format and max size of 20 MB',position:'bottom'});"
                    />
                    <div class="q-my-sm">

                      <q-card class="q-my-sm q-pa-md">
                        <q-card-section>
                          <div class="text-h6">Content list</div>
                        </q-card-section>
                        <q-card-section>
                          <q-input v-model="content_list_array" outlined="" label="What student is expecting to learn?" hint="Please separate each content by comma"/>
                        </q-card-section>

                      </q-card>
                    </div>
                  </div>
                </div>
              </div>

              <q-stepper-navigation>
                <q-btn color="primary" label="Update" @click="updateNotes()" />
                <q-btn
                  flat
                  @click="step = 2"
                  color="primary"
                  label="Back"
                  class="q-ml-sm"
                />
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
import { ref } from "vue";
import { api } from "boot/axios";
import { reactive } from "vue";
import { Notify, useQuasar } from "quasar";
import { onMounted } from "vue";
import { useNotesStore } from "src/stores/notes";
const $q=useQuasar();
const notesStore=useNotesStore();
const checked_date = ref(false);
const checked_subject = ref(false);
const checked_price = ref(false);
const checked_levels = ref(false);
const dialog = ref(false);
const maximizedToggle = ref(true);
const filter = ref("");
const step = ref(1);
const uploader_url=process.env.API_URL;
// const allow_download = ref(false);
// const price = ref(0);
const edit_dialog=ref(false);
const  options=ref(
  [
        {
          label: 'Google',
          value: 1
        },
        {
          label: 'Facebook',
          value: 2
        },
        {
          label: 'Twitter',
          value: 3
        },
        {
          label: 'Apple',
          value: 4
        },
        {
          label: 'Oracle',
          value: 5
        }
      ]
      );
const columns = ref([
  {
    name: "note_preview",
    label: "Notes preview",
    align: "left",

  },
  {
    name: "notes_title",
    align: "left",
    label: "Notes title",
    field: (row) => row.title,
  },
  {
    name: "notes_subject",
    align: "left",
    label: "Notes Subject",
    field: (row) => row.name,
  },
  {
    name: "price",
    align: "left",
    label: "Price",
    field: (row) => row.price,
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

const thumbnailUrl= async (info)=>{
   notes.image_url=info.xhr.response;
}


const contentUrl= async (info)=>{
   notes.content_url=info.xhr.response;
}
// const message =()=>{

// }
/*** create notes */
const content_list_array=ref('');
const STORAGE_URL=process.env.API_URL+'storage/';
const class_id=ref([]);
let notes = reactive(
  {
    subject_id:'',
    level_id:'',
    title:'',
    image_url:'',
    desc:'',
    is_downloaded:false,
    price:'',
    content_url:'',
    user_id:1
  }
);

const createNotes= async () =>{
 notes={
    ...notes,
    content_list:JSON.stringify(content_list_array.value.split(',')),
    class_id:JSON.stringify(class_id.value)
  }


  try {
   const {data} =await api.post('api/notes',notes);
    rows.value.push(data.data);
    dialog.value=false;
    if(data.status==='exists'){
      dialog.value=false;
      Notify.create({
        'type':'info',
        'message':'You already have notes under this title',
        'position':'bottom'
      })
    }

  } catch (error) {

  }
}


const notes_id=ref();
const editRow = async (row)=>{
   edit_dialog.value=true;
    console.table(row);
    notes.title=row.title;
    notes.price=row.price;
    notes_id.value=row.notes_id;
    notes.is_downloaded=JSON.parse(row.is_downloaded);
    content_list_array.value=row.content_list.replace(/\{|\}/g, '').replace(/'/g, '').replace(/"/g, '');
    notes.desc=row.desc;

}

const updateNotes =async ()=>{
    notes={
      ...notes,
      notes_id:notes_id.value
    }

    const data = await api.post('api/update-notes',notes);
   console.log(notes);

}

const  confirmDelete =  (row)=> {
      $q.dialog({
        title: 'Confirm',
        message: 'Would you like to delete this notes',
        cancel: true,
        persistent: true
      }).onOk(async() => {
        const {data} = await api.delete(`api/delete-notes/${row.notes_id}`);
        rows.value=data.data;
        Notify.create({
          message:'Deleted succesfully',
          type:'positive',
          position:'bottom'
        })
      }).onOk(() => {
        // console.log('>>>> second OK catcher')
      }).onCancel(() => {
        // console.log('>>>> Cancel')
      }).onDismiss(() => {
        // console.log('I am triggered on both OK and Cancel')
      })
    }

    const getNotes=async ()=> {
      try {
        const { data } = await api.get('api/notes');
        if (data.length > 0) {
          rows.value=data;
        }
      } catch (error) {
        // handle error
      }
    }

onMounted(async () => {

  await Promise.all([getNotes()]);
});


</script>

<style>
.my-card {
  width: 100%;
  font-weight: normal;
  padding: 2px 10px;
}
</style>

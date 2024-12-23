<script setup>
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';
import { VForm } from 'vuetify/components/VForm';

const emit = defineEmits([
  'update:isDialogVisible',
  'LocationData',
  'isSnackbarResponse',
  'isSnackbarResponseAlertColor',
  'errorMessages',
  'errors'
])

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  typeDialog: {
    type: String,
    required: true
  },
  locationId: {
    type: Number,
    required: true
  },
  fetchTrigger: {
    type: Number,
    default: 0
  },
  errors: {
    type: Object,
    required: false
  }
})

const isLoading = ref(true)
const refVForm = ref()
const typeDialog = computed(() => props.typeDialog)
const locationId = computed(() => props.locationId)
const loadingBtn = ref([])
const locationData = reactive({
    loc_app: "",
    loc_db_codereadr: []
});
const codeReadr = ref([])

const dialogModelValueUpdate = () => {
  locationData.loc_app = "";
  locationData.loc_db_codereadr = [];
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
}

const fetchCodeReadr = async () => {
  try {
    const response = await $api(`/apps/location/list`, {
      method: 'GET',
    });
    if (response.status === 200) {

      const rows = response.data.rows || [];
      codeReadr.value = rows.map((row) => ({
        title: row.cr_db_name,
        value: row.cr_db_id,
      }));
    } else {
      console.error('Failed to fetch codereadr data');
    }
    
  } catch (error) {
    console.error('Error fetching codereadr data');
  }
}

const fetchLocationEdit = async () => {
  try {
    isLoading.value = true;
    const response = await $api(`/apps/location/edit/${locationId.value}`, {
      method: 'GET',
      onResponseError({ response }) {
        const responseData = response._data;
        const responseMessage = responseData.message;
        const responseErrors = responseData.errors;
        emit('errors', responseErrors);
        emit('errorMessages', responseMessage);
        emit('update:isDialogVisible', false)
        throw new Error("Get data failed");
      },
    });
    
    const dataResponse = JSON.parse(JSON.stringify(response));
    if (dataResponse.status == 200) {
      const dataResult = dataResponse.data;
      isLoading.value = false;
      locationData.loc_app = dataResult.loc_app;
      locationData.loc_db_codereadr = dataResult.loc_db_codereadr;
    } else {
      emit('update:isDialogVisible', false)
      emit('isSnackbarResponse',true)
      emit('isSnackbarResponseAlertColor', 'error')
      throw new Error("Get data failed");
    }
  } catch (error) {
    isLoading.value = false;
    emit('update:isDialogVisible', false)
    emit('isSnackbarResponse',true)
    emit('isSnackbarResponseAlertColor', 'error')
  }
}

watch(
  [() => locationId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchLocationEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
      fetchCodeReadr()
      loadingBtn.value[0] = false;
  },
  { immediate: true }
);

const onSubmit = async () => {
  refVForm.value?.validate().then(({ valid }) => {
    try {
      if (valid) {
        loadingBtn.value[0] = true
        const mode = props.typeDialog;
        emit("LocationData", { mode, formData: { ...locationData }, dialogUpdate: dialogModelValueUpdate });
      } else {
        loadingBtn.value[0] = false
      }
    } catch (err) {
      loadingBtn.value[0] = false
    }
  })
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate" />
    <VCard class="pa-sm-10 pa-2" v-model:loading="isLoading">
      <VCardText>
          <div>
            <div class="d-flex flex-wrap justify-start justify-sm-space-between gap-y-4 gap-x-6 mb-6">
              <div class="d-flex flex-column justify-center">
                <div class="d-flex gap-x-4 align-center">
                  <div>
                    <div class="card__actions d-flex justify-end flex-column" v-if="isLoading">
                      <Skeletor width="150" height="36" class="me-4" pill/>
                      <Skeletor width="85" height="26" class="mt-5" pill/>
                    </div>
                    <div class="text-h4 font-weight-medium" v-if="!isLoading">
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Location
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card__text mb-5" v-if="isLoading">
              <VRow>
                <VCol cols="6" v-for="i in 1" :key="i">
                  <Skeletor height="40" pill class="mt-5"/>
                </VCol>
              </VRow>
            </div>
            <VRow>
              <VCol cols="12">
                <VForm
                  ref="refVForm"
                  lazy-validation
                  @submit.prevent="onSubmit"
                >
                  <VRow>
                    <VCol cols="12" v-if="!isLoading">
                      <h5>(*) Is required</h5>
                    </VCol>
                    <VCol cols="12" v-if="!isLoading">
                      <VRow>
                        <VCol cols="12" md="6">
                          <AppTextField
                            v-model="locationData.loc_app"
                            persistent-placeholder
                            label="Location*"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.loc_app"
                            required
                          />
                        </VCol>
                        <VCol cols="12" md="6">
                        <AppAutocomplete
                          label="Trigger Codereadr*"
                          v-model="locationData.loc_db_codereadr"
                          :items="codeReadr"
                          :item-title="'title'"
                          :item-value="'value'"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.loc_db_codereadr"
                          placeholder="Select Trigger Codereadr"
                          clearable
                        />
                      </VCol>
                      </VRow>
                    </VCol>
                  </VRow>
                  <VCol cols="12" class="mt-5">
                    <VRow class="d-flex justify-end">
                      <div class="card__actions d-flex justify-end" v-if="isLoading">
                        <Skeletor width="96" height="36" class="me-4"/>
                        <Skeletor width="96" height="36" />
                      </div>
                      <div v-if="!isLoading">
                        <VBtn
                          :loading="loadingBtn[0]"
                          :disabled="loadingBtn[0]"
                          type="submit"
                          class="me-4"
                        >
                          <span>{{ typeDialog == 'Edit' ? 'Update' : 'Create' }}</span>
                        </VBtn>
                        <VBtn
                          color="error"
                          variant="tonal"
                          @click="dialogModelValueUpdate"
                        >
                          Discard
                        </VBtn>
                      </div>
                    </VRow>
                  </VCol>
                </VForm>
              </VCol>
            </VRow>
          </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

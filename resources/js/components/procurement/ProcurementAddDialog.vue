<script setup>
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';
import { VForm } from 'vuetify/components/VForm';

const emit = defineEmits([
  'update:isDialogVisible',
  'ProcurementData',
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
  procurementId: {
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
const procurementId = computed(() => props.procurementId)
const loadingBtn = ref([])
const isFileAttachment = ref('')
const procurementData = reactive({
  visitor_name: null,
  visitor_photo: null,
  purpose: null,
  non_exp: null,
  duration: null,
  acc_level: []
});
const dataAccessLevel = ref([])

const fetchAccessLevel = async () => {
  try {
    const response = await $api(`/apps/procurement/list`, {
      method: 'GET',
    });
    if (response.status === 200) {

      const rows = response.data.rows || [];
      dataAccessLevel.value = rows.map((row) => ({
        title: row.acc_desc,
        value: row.acc_level,
      }));
    } else {
      console.error('Failed to fetch Access Level data');
    }
    
  } catch (error) {
    console.error('Error fetching Access Level data');
  }
}

const dialogModelValueUpdate = () => {
  procurementData.visitor_name = null,
  procurementData.visitor_photo = null,
  procurementData.purpose = null,
  procurementData.non_exp = null,
  procurementData.duration = null,
  procurementData.acc_level = [];
  isFileAttachment.value = ''
  refVForm.value?.reset()
  refVForm.value?.resetValidation()
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
}

watch(
  [() => procurementId.value, () => typeDialog.value, () => props.fetchTrigger],
    ([newId,newType]) => {
      if (newType === "Edit" && newId) {
        fetchProcurementEdit();
      } else if (newType === "Add") {
        isLoading.value = false;
      }
      fetchAccessLevel();
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
        emit("ProcurementData", { mode, formData: { ...procurementData }, dialogUpdate: dialogModelValueUpdate });
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
                      {{ typeDialog == 'Edit' ? 'Edit' : 'Create New' }} Procurement Barcode
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
                    <VCol cols="12" md="6" v-if="!isLoading">
                      <VCol>
                          <AppTextField
                            v-model="procurementData.visitor_name"
                            persistent-placeholder
                            label="Vendor's Name*"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.st_desc"
                            required
                          />
                      </VCol>
                      <VCol>
                          <VFileInput
                            label="Vendor's Photo"
                            v-model="procurementData.visitor_photo"
                            :error-messages="props.errors?.visitor_photo"                      
                            accept="image/png, image/jpeg, image/jpg, image/gif, image/tiff, image/heic, application/pdf"
                            placeholder="Pick a file"
                          />
                            <small>Accepted: .pdf,.jpg,.jpeg,.png, MAX: 5MB</small>
                            <RouterLink v-if="isFileAttachment != null && isFileAttachment != ''"> 
                              <small class="" @click="openPathDialog(isFileAttachment)">
                              View data
                              </small>
                            </RouterLink>
                      </VCol>
                      <VCol>
                          <AppTextField
                            v-model="procurementData.purpose"
                            persistent-placeholder
                            label="Purpose"
                            placeholder="Type here..."
                            :rules="[requiredValidator]"
                            :error-messages="props.errors?.st_desc"
                            required
                          />
                      </VCol>
                    </VCol>
                    <VCol cols="12" md="6" v-if="!isLoading">
                      <VCol>
                        <AppAutocomplete
                          label="Expired Type"
                          v-model="procurementData.non_exp"
                          :items="[
                            { value: true, title: 'Non-Expired' },
                            { value: false, title: 'Expired' },
                          ]"
                          :item-title="'title'"
                          :item-value="'value'"
                          placeholder="Select Type"
                        />
                        <small v-if="procurementData.non_exp == false">Access will be expire according to Expired Date</small>
                        <small v-else >Access will not be expired</small>
                      </VCol>
                      <VCol>
                        <AppDateTimePicker v-if="procurementData.non_exp == false"
                          label="Select Start and End Date*"
                          v-model="procurementData.duration"
                          placeholder="Select range date"
                          :config="{ mode: 'range' }"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.duration"
                          clearable
                        />
                        <AppDateTimePicker v-else
                          label="Select Start Date*"
                          v-model="procurementData.duration"
                          placeholder="Select start date"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.duration"
                          clearable
                        />
                      </VCol>
                      <VCol>
                        <AppAutocomplete
                          label="Access Level* (multiple select)"
                          v-model="procurementData.acc_level"
                          :items="dataAccessLevel"
                          :item-title="'title'"
                          :item-value="'value'"
                          :rules="[requiredValidator]"
                          :error-messages="props.errors?.acc_level"
                          placeholder="Select access level"
                          chips
                          multiple
                          closable-chips
                          clearable
                        />
                      </VCol>
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

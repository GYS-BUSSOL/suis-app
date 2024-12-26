<script setup>

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isSecurityDialogVisible = ref(false)
const isSecurityDialogDeleteVisible = ref(false)
const isSecurityTypeDialog = ref('Add')
const IDSecurity = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  application_name: undefined,
})
const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const {
  data: securityData,
  execute: fetchSecurity,
} = await useApi(createUrl('/apps/security/search', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))


const security = computed(() => securityData.value.security)
const totalSecurity = computed(() => securityData.value.totalSecurity)

const openDialog = async ({ id = null, type }) => {
  isSecurityTypeDialog.value = type
  isSecurityDialogVisible.value = true
  if(type == 'Edit')
    IDSecurity.value = id
    fetchTrigger.value += 1;
}

const updateSnackbarResponse = res => {
  isSnackbarResponse.value = res;
}

const updateSnackbarResponseAlertColor = color => {
  isSnackbarResponseAlertColor.value = color;
}

const alertErrorResponse = () => {
  fetchTrigger.value += 1;
  isSnackbarResponse.value = true;
  isSnackbarResponseAlertColor.value = 'error'
}

const alertSuccessResponse = () => {
  fetchTrigger.value += 1;
  isSnackbarResponse.value = true;
  isSnackbarResponseAlertColor.value = 'success'
}

const updateErrorMessages = err => {
  errorMessages.value = err;
}

const updateErrors = err => {
  errors.value = err;
}

// add
const fetchAddData = async (securityData, clearedForm) => {
  const formData = new FormData();
      
    for (const [key, value] of Object.entries(securityData)) {
      if (value instanceof File) {
        formData.append(key, value);
      } else if (Array.isArray(value)) {
        value.forEach((item, index) => {
          formData.append(`${key}[${index}]`, item);
        });
      } else {
        formData.append(key, value);
      }
    }
  try {
      const response = await $api('/apps/security/add', {
        method: 'POST',
        body: formData,
        onResponseError({ response }) {
          alertErrorResponse()
          const responseData = response._data;
          const responseMessage = responseData.message;
          const responseErrors = responseData.errors;
          errors.value = responseErrors;
          errorMessages.value = responseMessage;
          throw new Error("Created data failed");
        },
      });

    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchSecurity()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isSecurityDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handleFormSubmit = async ({mode, formData, dialogUpdate}) => {
  if (mode === "Add") {
    fetchAddData(formData, dialogUpdate)
  }
}

// Headers
const headers = [
  {
    title: 'No',
    key: 'no'
  },
  {
    title: 'Aplication Name',
    key: 'aplication_name',
    sortable: false,
  },
  {
    title: 'Barcode',
    key: 'barcode',
    sortable: false,
  },
  {
    title: 'Download Barcode',
    key: 'download_barcode',
    sortable: false,
  },
  {
    title: 'vendor Name',
    key: 'visitor_name',
  },
  {
    title: 'Location',
    key: 'location',
  },
  {
    title: 'Tanggal Kedatangan',
    key: 'start_date',
  },
  {
    title: 'Exp Barcode',
    key: 'exp_date',
  },
  {
    title: 'Status',
    key: 'status',
  },
]
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect
            :model-value="itemsPerPage"
            :items="[
              { value: 10, title: '10' },
              { value: 25, title: '25' },
              { value: 50, title: '50' },
              { value: 100, title: '100' },
            ]"
            style="inline-size: 6.25rem;"
            @update:model-value="itemsPerPage = parseInt($event, 10)"
          />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <!-- Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField
              v-model="searchQuery"
              placeholder="Search..."
              clearable
            />
          </div>

          <!-- Export button -->
          <VBtn
            variant="tonal"
            color="secondary"
            prepend-icon="tabler-upload"
          >
            Export
          </VBtn>
          <!-- Create New PBL button -->
          <VBtn
            color="primary"
            prepend-icon="tabler-plus"
            @click="openDialog({type:'Add'})"
          >
            Create New
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer
        v-model:items-per-page="itemsPerPage"
        v-model:model-value="selectedRows"
        v-model:page="page"
        :items="security"
        item-value="id"
        :items-length="totalSecurity"
        :headers="headers"
        class="text-no-wrap"
        show-select
        @update:options="updateOptions"
      >
        <!-- No -->
        <template #item.no="{ index }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ (page - 1) * itemsPerPage + index + 1 }}
          </div>
        </template>

        <!-- Barcode -->
        <template #item.barcode="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            <template v-if="item.status == 1 && item.aplication_name == 'Security Information System'">
                <button class="btn" >
                      <img :src="`/storage/barcode/${item.qr_image}`" width="100px" class="img img-responsive mt-2" alt="">
                    <br>
                    {{ item.visitor_name }}
                  </button>
                </template>
                <template v-else-if="item.status == 0 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-warning px-3 py-2 rounded">Pending Aproval</label>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-danger px-3 py-2 rounded">Rejected</label>
                </template>
                <template v-else>
                  {{ item.aplication_name }}
                </template>
          </div>
        </template>

        <!-- Download Barcode -->
        <template #item.download_barcode="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            <template v-if="item.status == 1 && item.aplication_name == 'Security Information System'">
              <button class="btn bg-info px-3 py-2 rounded text-center">
                    <a :href="`/storage/card/${item.barcode}`" class="text-white text-decoration-none" download>
                      <VIcon icon="tabler-download" />
                      Download Barcode
                    </a>
                  </button>
                </template>
                <template v-else-if="item.status == 0 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-warning px-3 py-2 rounded">Pending Aproval</label>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-danger px-3 py-2 rounded">Rejected</label>
                </template>
                <template v-else>
                  {{ item.aplication_name }}
                </template>
          </div>
        </template>


        <!-- Status -->
        <template #item.status="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            <template v-if="item.status == 0 && item.aplication_name == 'Security Information System'">
              <label class="badge bg-warning px-3 py-2 rounded">Pending</label>
                </template>
                <template v-else-if="item.status == 1 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-success px-3 py-2 rounded">Approved</label>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-danger px-3 py-2 rounded">Rejected</label>
                </template>
                <template v-else-if="item.del == 1 && item.aplication_name == 'Security Information System'">
                  <label class="badge bg-dark px-3 py-2 rounded">Deleted</label>
                </template>
                <template v-else>
                  <label class="badge bg-info px-3 py-2 rounded">{{ item.aplication_name }}</label>
                </template>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalSecurity"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <SecurityBarcodeDialog
    v-model:isDialogVisible="isSecurityDialogVisible"
    :errors="errors"
    :typeDialog="isSecurityTypeDialog"
    :Security-id="IDSecurity"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @SecurityData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <SecurityAddDialog
    v-model:isDialogVisible="isSecurityDialogVisible"
    :errors="errors"
    :typeDialog="isSecurityTypeDialog"
    :Security-id="IDSecurity"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @SecurityData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <VSnackbar
    v-model="isSnackbarResponse"
    transition="scroll-y-reverse-transition"
    Security="top end"
    variant="flat"
    :color="isSnackbarResponseAlertColor"
  >
    {{ isSnackbarResponseAlertColor == 'error' ? errorMessages : successMessages }}
    <template #actions>
      <VBtn
        color="white"
        @click="isSnackbarResponse = false"
      >
        Close
      </VBtn>
    </template>
  </VSnackbar>
</template>

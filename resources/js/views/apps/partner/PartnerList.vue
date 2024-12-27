<script setup>
import PopupImage from '@/components/popup/PopupImage.vue'


const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isPartnerDialogVisible = ref(false)
const isPopupVisible = ref(false)
const isPartnerTypeDialog = ref('Add')
const IDPartner = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  application_name: undefined,
  visitor_name: undefined,
  visitor_photo: undefined,
  purpose: undefined,
  non_exp: undefined,
  duration: undefined,
  acc_level: undefined
})
const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const {
  data: partnerData,
  execute: fetchPartner,
} = await useApi(createUrl('/apps/partner/search', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const image = ref('')
const name = ref('')

const openPopup = async ({ imagePopup, namePopup }) => {
  isPopupVisible.value = true
  image.value = "/storage/barcode/"+imagePopup
  name.value = namePopup
}


const partner = computed(() => partnerData.value.partner)
const totalPartner = computed(() => partnerData.value.totalPartner)
console.log('partner data: ', partner)

const openDialog = async ({ id = null, type }) => {
  isPartnerTypeDialog.value = type
  isPartnerDialogVisible.value = true
  if(type == 'Edit')
    IDPartner.value = id
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
const fetchAddData = async (partnerData, clearedForm) => {
  try {
    const formData = new FormData();
      
    for (const [key, value] of Object.entries(partnerData)) {
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
      const response = await $api('/apps/partner/add', {
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
      fetchPartner()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isPartnerDialogVisible.value = false
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
    key: "no"
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
        :items="partner"
        item-value="id"
        :items-length="totalPartner"
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
                <button class="btn" @click="openPopup({imagePopup: item.qr_image, namePopup: item.visitor_name})" >
                  <img :src="`/storage/barcode/${item.qr_image}`" width="100px" class="img img-responsive mt-2" alt="">
                  <br>
                  {{ item.visitor_name }}
                </button>
                </template>
                <template v-else-if="item.status == 0 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="warning"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Pending Approval
                  </VChip>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="error"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Rejected
                  </VChip>
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
                <VChip
                    color="info"
                    size="small"
                    icon="tabler-download"
                    label
                    class="text-capitalize"
                  >
                  <a :href="`/storage/card/${item.barcode}`" class="text-white text-decoration-none" download>
                    Download Barcode
                  </a>
                  </VChip>
                </template>
                <template v-else-if="item.status == 0 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="warning"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Pending Approval
                  </VChip>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="error"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Rejected
                  </VChip>
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
              <VChip
                    color="warning"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Pending
                  </VChip>
                </template>
                <template v-else-if="item.status == 1 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="success"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Approved
                  </VChip>
                </template>
                <template v-else-if="item.status == 2 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="error"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Rejected
                  </VChip>
                </template>
                <template v-else-if="item.del == 1 && item.aplication_name == 'Security Information System'">
                  <VChip
                    color="secondary"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  Deleted
                  </VChip>
                </template>
                <template v-else>
                  <VChip
                    color="info"
                    size="small"
                    label
                    class="text-capitalize"
                  >
                  {{ item.aplication_name }}
                  </VChip>
                </template>
          </div>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalPartner"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
    <PopupImage
    v-model:isDialogVisible="isPopupVisible"
    :image="image"
    :name="name"
    />
  <PartnerAddDialog
    v-model:isDialogVisible="isPartnerDialogVisible"
    :errors="errors"
    :typeDialog="isPartnerTypeDialog"
    :Partner-id="IDPartner"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @PartnerData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <VSnackbar
    v-model="isSnackbarResponse"
    transition="scroll-y-reverse-transition"
    partner="top end"
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

<script setup>
import PartnerApprovalDialog from '@/components/partner/PartnerApprovalDialog.vue'
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
const isPartnerTypeDialog = ref('Approve')
const IDPartner = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  visitor_name: undefined,
  visitor_photo: undefined,
  purpose: undefined,
  location: undefined,
  start_date: undefined,
  exp_date: undefined

})

const image = ref('')
const name = ref('')

const openPopup = async ({ imagePopup, namePopup }) => {
  isPopupVisible.value = true
  image.value = "/storage/Photo/"+imagePopup
  name.value = namePopup
}

const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const openDialog = async ({ id, type }) => {
  isPartnerTypeDialog.value = type
  isPartnerDialogVisible.value = true
  IDPartner.value = id
}

const {
  data: partnerData,
  execute: fetchPartnerApproval,
} = await useApi(createUrl('/apps/partner/approval', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const approvalPartner = async id => {
  try {
    const idPartner = Number(id);
    let response
    const type = isPartnerTypeDialog.value
    if (type === 'Approve') {
      response = await $api(`/apps/partner/approval/approve/${idPartner}`, {
          method: 'PUT',
          onResponseError({ response }) {
          alertErrorResponse()
          const responseData = response._data;
          const responseMessage = responseData.message;
          const responseErrors = responseData.errors;
          errors.value = responseErrors;
          errorMessages.value = responseMessage;
          throw new Error("Deleted data failed");
        },
      })
    } else if (type === 'Reject') {      
      response = await $api(`/apps/partner/approval/reject/${idPartner}`, {
          method: 'PUT',
          onResponseError({ response }) {
          alertErrorResponse()
          const responseData = response._data;
          const responseMessage = responseData.message;
          const responseErrors = responseData.errors;
          errors.value = responseErrors;
          errorMessages.value = responseMessage;
          throw new Error("Deleted data failed");
        },
      })
    }
    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      fetchPartnerApproval()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isPartnerDialogVisible.value = false;
    } else {
      alertErrorResponse()
      throw new Error("Deleted data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}


const partner = computed(() => partnerData.value.partner)
const totalPartner = computed(() => partnerData.value.totalPartner)


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

// Headers
const headers = [
  {
    title: 'No',
    key: "no"
  },
  {
    title: 'Vendor Name',
    key: 'visitor_name',
    sortable: false,
  },
  {
    title: 'Purpose',
    key: 'purpose',
    sortable: false,
  },
  {
    title: 'Location',
    key: 'location',
    sortable: false,
  },
  {
    title: 'Start Date',
    key: 'start_date',
  },
  {
    title: 'Expired Date',
    key: 'exp_date',
  },
  {
    title: 'Vendor Photo',
    key: 'visitor_photo',
  },
  {
    title: 'Action',
    key: 'actions',
    sortable: false,
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

        <!-- Visitor Photo -->
        <template #item.visitor_photo="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            <template v-if="item.visitor_photo == NULL">
                <div class="text-center">
                    <label class="badge bg-warning px-3 py-2 rounded">No Picture</label>
                </div>
            </template>
            <template v-else>
                <button class="btn" @click="openPopup({imagePopup: item.visitor_photo, namePopup: item.visitor_name})" >
                    <img :src="`/storage/Photo/${item.visitor_photo}`" width="100px" class="img img-responsive mt-2" alt="">
                </button>
            </template>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.id, type: 'Approve'})">
            <VIcon icon="tabler-check" color="success" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Approve</span>
            </VTooltip>
          </IconBtn>
          <IconBtn @click="openDialog({id: item.id, type: 'Reject'})">
            <VIcon icon="tabler-x" color="error" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Reject</span>
            </VTooltip>
          </IconBtn>
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
  <PartnerApprovalDialog
    v-model:isDialogVisible="isPartnerDialogVisible"
    :typeDialog="isPartnerTypeDialog"
    :partner-id="IDPartner"
    :fetch-trigger="fetchTrigger"
    @id-approval="approvalPartner"
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

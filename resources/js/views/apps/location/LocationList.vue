<script setup>
import LocationAddDialog from '@/components/location/LocationAddDialog.vue';

const emit = defineEmits(['updateTotalNotActive','updateTotalActive'])
// Store
const searchQuery = ref('')
// Data table options
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()
const selectedRows = ref([])
const isLocationDialogVisible = ref(false)
const isLocationTypeDialog = ref('Add')
const IDLocation = ref(0)
const isSnackbarResponse = ref(false)
const isSnackbarResponseAlertColor = ref('error')
const fetchTrigger = ref(0);
const errorMessages = ref('Internal server error')
const successMessages = ref('Successfully')
const errors = ref({
  loc_app: undefined,
  loc_db_codereadr: undefined
})
const updateOptions = options => {
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

// Headers
const headers = [
  {
    title: "No",
    key: "no"
  },
  {
    title: 'Location',
    key: 'loc_app',
  },
  {
    title: 'Trigger Codereadr',
    key: 'join_first_cr_db_name',
  },
  {
    title: 'Actions',
    key: 'actions',
    sortable: false,
  },
]

const {
  data: locationData,
  execute: fetchLocation,
} = await useApi(createUrl('/apps/location/search', {
  query: {
    q: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const location = computed(() => locationData.value.location)
const totalLocation = computed(() => locationData.value.totalLocation)

const openDialog = async ({ id = null, type }) => {
  isLocationTypeDialog.value = type
  isLocationDialogVisible.value = true
  if(type == 'Edit')
    IDLocation.value = id
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

const fetchAddData = async (locationData, clearedForm) => {
  try {
      const response = await $api('/apps/location/add', {
        method: 'POST',
        body: JSON.stringify(locationData),
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
      fetchLocation()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isLocationDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Created data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const fetchLocationUpdate = async (id, formData, clearedForm) => {
  console.log("location: ", formData)
  try {
    const response = await $api(`/apps/location/update/${id}`, {
        method: 'PUT',
        body: JSON.stringify(formData),
        onResponseError({ response }) {
          alertErrorResponse()
          const responseData = response._data;
          const responseMessage = responseData.message;
          const responseErrors = responseData.errors;
          errors.value = responseErrors;
          errorMessages.value = responseMessage;
          throw new Error("Updated data failed");
        },
      });
    
    const responseStringify = JSON.stringify(response);
    const responseParse = JSON.parse(responseStringify);

    if(responseParse?.status == 200) {
      clearedForm()
      fetchLocation()
      alertSuccessResponse()
      const responseMessage = responseParse?.message;
      successMessages.value = responseMessage;
      isLocationDialogVisible.value = false
    } else {
      alertErrorResponse()
      throw new Error("Updated data failed");
    }
  } catch (error) {
    alertErrorResponse()
  }
}

const handleFormSubmit = async ({mode, formData, dialogUpdate}) => {
  console.log("location submit: ", formData)
  if (mode === "Add") {
    fetchAddData(formData, dialogUpdate)
  } else if(mode === 'Edit') {
    fetchLocationUpdate(IDLocation.value, formData, dialogUpdate)
  }
}
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
        :items="location"
        item-value="id"
        :items-length="totalLocation"
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
        <!-- Location -->
        <template #item.loc_app="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.loc_app != null && item.loc_app != '' ? item.loc_app : '-' }}
          </div>
        </template>
        <!-- Trigger Codereadr -->
        <template #item.join_first_cr_db_name="{ item }">
          <div class="text-body-1 text-high-emphasis text-capitalize">
            {{ item.join_first_cr_db_name != null && item.join_first_cr_db_name != '' ? item.join_first_cr_db_name : '-' }}
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <IconBtn @click="openDialog({id: item.id, type: 'Edit'})">
            <VIcon icon="tabler-edit" />
            <VTooltip open-delay="200" location="top" activator="parent">
              <span>Edit data</span>
            </VTooltip>
          </IconBtn>
        </template>

        <!-- pagination -->
        <template #bottom>
          <TablePagination
            v-model:page="page"
            :items-per-page="itemsPerPage"
            :total-items="totalLocation"
          />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
  </section>
  <LocationAddDialog
    v-model:isDialogVisible="isLocationDialogVisible"
    :errors="errors"
    :typeDialog="isLocationTypeDialog"
    :location-id="IDLocation"
    :fetch-trigger="fetchTrigger"
    @isSnackbarResponseAlertColor="updateSnackbarResponseAlertColor"
    @isSnackbarResponse="updateSnackbarResponse"
    @LocationData="handleFormSubmit"
    @errorMessages="updateErrorMessages"
    @errors="updateErrors"
  />
  <VSnackbar
    v-model="isSnackbarResponse"
    transition="scroll-y-reverse-transition"
    location="top end"
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

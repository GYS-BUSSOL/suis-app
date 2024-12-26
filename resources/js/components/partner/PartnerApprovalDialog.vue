<script setup>
import { ref, watch } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  typeDialog: {
    type: String,
    required: true
  },
  partnerId: {
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
const partnerId = computed(() => props.partnerId)
const loadingBtn = ref([])

const dialogModelValueUpdate = () => {
  emit('update:isDialogVisible', false)
}
const emit = defineEmits([
  'update:isDialogVisible',
  'idApproval'
]);

watch(
  [() => props.partnerId, () => typeDialog.value, () => props.fetchTrigger],
    () => {
      loadingBtn.value[0] = false;
  },
  { immediate: true }
);

const approvePartner = async id => {
  try {
      loadingBtn.value[0] = true
      emit("idApproval", Number(id));
    } catch (err) {
      loadingBtn.value[0] = false
    }
}
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 1200"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
  <VCard class="pa-2 pa-sm-10" v-model:loading="loadingBtnDelete[0]">
    <VCardItem class="text-center">
      <VCardTitle>
        <h4 class="text-h4 mb-2">
          {{ TypeApproval }} Partner Data
        </h4>
      </VCardTitle>
    </VCardItem>

    <VCardText class="pt-6">
      <VRow>
        <VCol cols="12">
          <p class="text-body-1 mb-0">
            Are you sure {{ TypeApproval }} this data ? please klik <strong>{{ TypeApproval }}</strong> button to confirm
          </p>
        </VCol>
        <VCol
          cols="12"
          class="text-center"
        >
          <VBtn
            class="me-4"
            color="error"
            type="submit"
            :loading="loadingBtnDelete[0]"
            :disabled="loadingBtnDelete[0]"
            @click="approvePartner(props.partnerId)"
          >
            Delete
          </VBtn>
          <VBtn
            color="secondary"
            variant="outlined"
            @click="$emit('update:isDialogDeleteVisible', false)"
          >
            Cancel
          </VBtn>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
  </VDialog>
</template>

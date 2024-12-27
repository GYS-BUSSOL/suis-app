<script setup>
import { ref } from 'vue';
import 'vue-skeletor/dist/vue-skeletor.css';

const emit = defineEmits([
  'update:isDialogVisible',
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
  image: {
    type: String,
    required: true
  },
  name: {
    type: String,
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
const image = computed(() => props.image)
const name = computed(() => props.name)
const loadingBtn = ref([])

const dialogModelValueUpdate = () => {
  loadingBtn.value[0] = false;
  emit('update:isDialogVisible', false)
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
    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <div class="d-flex align-items-center justify-content-center gap-y-3 gap-x-3 mb-6">
          <VCol>
            <div class="text-h2 text-center mb-3">
              {{ name }}
            </div>
            <VRow class="justify-center">
              <img
                :src="image"
                width="400px"
                class="img img-responsive mt-2"
                alt="Barcode Image"
              >
            </VRow>
          </VCol>
        </div>
      </VCardText>
    </VCard>
  </VDialog>
</template>

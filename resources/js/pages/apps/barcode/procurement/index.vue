<script setup>
import ProcurementList from '@/views/apps/procurement/ProcurementList.vue';
const userData = useCookie('userData').value.usr_access


</script>

<template>
  <div v-if="userData === 'admin' || userData === 'proc'">
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Barcode Procurement List
        </h4>
      </VCol>
      <VCol cols="12">
        <ProcurementList />
      </VCol>
    </VRow>
  </div>
</template>

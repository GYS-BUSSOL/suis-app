<script setup>
import SecurityList from '@/views/apps/security/SecurityList.vue';
const userData = useCookie('userData').value.usr_access

</script>

<template>
  <div v-if="userData === 'admin' || userData === 'ga'">
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Barcode Security List
        </h4>
      </VCol>
      <VCol cols="12">
        <SecurityList />
      </VCol>
    </VRow>
  </div>
</template>

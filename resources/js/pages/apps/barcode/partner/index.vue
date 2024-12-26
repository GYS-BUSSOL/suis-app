<script setup>
import PartnerList from '@/views/apps/partner/PartnerList.vue';
const userData = useCookie('userData').value.usr_access

</script>

<template>
  <div v-if="userData === 'partner' || userData === 'admin'">
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Partner List
        </h4>
      </VCol>
      <VCol cols="12">
        <PartnerList/>
      </VCol>
    </VRow>
  </div>
</template>

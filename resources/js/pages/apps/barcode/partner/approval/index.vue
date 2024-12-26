<script setup>
import ApprovalPartner from '@/views/apps/partner/ApprovalPartner.vue';

const userData = useCookie('userData').value.usr_access

</script>

<template>
  <div v-if="userData === 'approvalpartner'">
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Approval Partner
        </h4>
      </VCol>
      <VCol cols="12">
        <ApprovalPartner/>
      </VCol>
    </VRow>
  </div>
</template>

<script setup>
import LocationList from '@/views/apps/location/LocationList.vue';
const userData = useCookie('userData').value.usr_access

</script>

<template>
  <div v-if="userData === 'admin'">
    <VRow>
      <VCol cols="12">
        <h4 class="text-h4 mb-1">
          Data Location
        </h4>
      </VCol>
      <VCol cols="12">
        <LocationList />
      </VCol>
    </VRow>
  </div>
</template>

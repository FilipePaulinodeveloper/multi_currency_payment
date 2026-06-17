<script setup lang="ts">
import HomeTable from './HomeTable.vue';

// This page uses the default layout (sidebar) automatically

</script>
 
<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-lg font-semibold text-white">Dashboard</h1>
      <p class="text-sm text-zinc-500 mt-1">Welcome back, John.</p>
    </div>
 
    <!-- Your HomeTable component goes here -->
    <HomeTable />
  </div>
</template>
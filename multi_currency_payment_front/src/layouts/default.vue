<script setup lang="ts">
import { ref } from 'vue'
import type { NavigationMenuItem } from '@nuxt/ui'

import { useAuth } from '../composables/useAuth'
const { logout } = useAuth()
const open = ref(true)
const user = ref(
  JSON.parse(localStorage.getItem('user') || '{}')
)
function getInitials(name?: string) {
  if (!name) return ''

  return name
    .trim()
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map(word => word[0]?.toUpperCase())
    .join('')
}
const items: NavigationMenuItem[][] = [
  [
    {
      label: 'Payments',
      icon: 'i-lucide-send',      
      to: '/',
    },


  //   {
  //     label: 'Employees',
  //     icon: 'i-mdi:user',      
  //     to: '/employes',
  //   },
  //   {
  //     label: 'Financial Admin',
  //     icon: 'i-mdi:user',      
  //     to: '/financials-admin',
  //   },
    
  // ],
  // [
  //   {
  //     label: 'Settings',
  //     icon: 'i-lucide-settings',
  //     to: '/settings',
  //   },
  ],
]
</script>

<template>
  <div class="flex h-screen overflow-hidden bg-black">

    <!-- Sidebar -->
    <USidebar
      v-model:open="open"
      collapsible="icon"
      :ui="{
        container: 'h-full bg-zinc-950 border-r border-zinc-900',
      }"
    >
      <!-- Logo -->
      <template #header>
        <div class="flex items-center gap-2.5 px-1">
          <div class="w-7 h-7 min-w-[28px] rounded-lg bg-emerald-500 flex items-center justify-center">
            <UIcon name="i-lucide-circle-dollar-sign" class="w-4 h-4 text-emerald-950" />
          </div>
          <span class="text-sm font-semibold text-white">
            Coin<span class="text-emerald-500">Pay</span>
          </span>
        </div>
      </template>

      <!-- Navigation -->
      <UNavigationMenu
        :items="items"
        orientation="vertical"
        :ui="{
          link: 'p-1.5 gap-2.5 text-zinc-500 hover:text-white hover:bg-zinc-900 rounded-lg transition-colors data-[active]:bg-emerald-950 data-[active]:text-emerald-500',
          linkLeadingIcon: 'size-[18px]',
          linkLabel: 'text-[13px] font-medium',
          badge: 'bg-zinc-900 text-zinc-400 border border-zinc-800 text-[11px]',
        }"
      />

      <!-- User -->
<template #footer>
  <div class="flex items-center justify-between w-full px-1 py-1">

    <div class="flex items-center gap-2.5">
      <div
        class="w-7 h-7 min-w-[28px] rounded-full bg-emerald-950 border border-emerald-500/30 flex items-center justify-center text-[11px] font-semibold text-emerald-500"
      >
        {{ getInitials(user.name) }}
      </div>

      <div class="overflow-hidden">
        <p class="text-xs font-medium text-zinc-300 truncate">
          {{ user.name }}
        </p>
      </div>
    </div>

    <UButton
      icon="i-lucide-log-out"
      color="error"
      variant="ghost"
      size="xs"
      @click="logout"
    />

  </div>
</template>
    </USidebar>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Topbar -->
      <header class="h-14 shrink-0 flex items-center gap-3 px-4 border-b border-zinc-900 bg-black">
        <UButton
          icon="i-lucide-panel-left"
          color="neutral"
          variant="ghost"
          size="sm"
          :ui="{ base: 'text-zinc-500 hover:text-white border border-zinc-800 hover:bg-zinc-900' }"
          aria-label="Toggle sidebar"
          @click="open = !open"
        />

        <!-- Page title via useRoute or slot -->
        <span class="text-sm font-medium text-zinc-400">Payments</span>

    
      </header>

      <!-- Page content -->
      <div class="flex-1 overflow-y-auto p-5">
        <RouterView />
      </div>

    </div>
  </div>
</template>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-black px-4 py-12">

    <!-- Brand -->
    <div class="text-center mb-8">
      <div class="flex justify-center mb-4">
        <UIcon name="i-lucide-circle-dollar-sign" class="text-emerald-500 w-12 h-12" />
      </div>
      <h1 class="text-2xl font-bold text-white tracking-tight">
        Coin<span class="text-emerald-500">Pay</span>
      </h1>
      <p class="mt-1.5 text-sm text-zinc-500">
        Multi-currency payments, simplified
      </p>
    </div>

    <!-- Card -->
    <div class="w-full max-w-sm bg-zinc-950 border border-zinc-800 rounded-xl p-8">
      <h2 class="text-lg font-semibold text-white mb-1">Sign in to your account</h2>
      <p class="text-sm text-zinc-500 mb-6">Enter your credentials to continue.</p>

      <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">

        <!-- Email -->
        <UFormField label="Email address" name="email">
          <UInput
            v-model="state.email"
            type="email"
            placeholder="you@example.com"
            icon="i-lucide-mail"
            size="md"
            class="w-full"
            :ui="{
              base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 focus:ring-emerald-500/20',
            }"
          />
        </UFormField>

        <!-- Password -->
        <UFormField name="password">
          <template #label>
            <div class="flex items-center justify-between w-full">
              <span class="text-sm font-medium text-zinc-400">Password</span>
              <NuxtLink
                to="/forgot-password"
                class="text-sm font-medium text-emerald-500 hover:text-emerald-400 transition-colors"
              >
                Forgot password?
              </NuxtLink>
            </div>
          </template>
          <UInput
            v-model="state.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="••••••••"
            icon="i-lucide-lock"
            size="md"
            class="w-full"
            :ui="{
              base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 focus:ring-emerald-500/20',
            }"
          >
            <template #trailing>
              <button
                type="button"
                class="text-zinc-500 hover:text-zinc-300 transition-colors"
                @click="showPassword = !showPassword"
              >
                <UIcon
                  :name="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                  class="w-4 h-4"
                />
              </button>
            </template>
          </UInput>
        </UFormField>

        <!-- Submit -->
        <UButton
          type="submit"
          block
          size="md"
          :loading="loading"
          class="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold mt-2"
        >
          Sign in
        </UButton>

       <UAlert
            v-if="errorMessage"
            icon="i-lucide-circle-alert"
            color="error"
            variant="soft"
            :title="errorMessage"
            class="mb-4"
       />

      </UForm>

      <!-- Supported currencies -->
      <div class="flex items-center gap-3 my-5">
        <div class="flex-1 h-px bg-zinc-800" />
        <span class="text-xs text-zinc-600">supported currencies</span>
        <div class="flex-1 h-px bg-zinc-800" />
      </div>

      <div class="flex justify-center gap-2">
        <span
          v-for="c in currencies"
          :key="c.label"
          class="flex items-center gap-1.5 bg-zinc-900 border border-zinc-800 rounded-md px-2.5 py-1 text-xs text-zinc-400"
        >
          <UIcon :name="c.icon" class="text-emerald-500 w-3.5 h-3.5" />
          {{ c.label }}
        </span>
      </div>
   

      
      
    </div>

  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue' 
import { z } from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'
import { useAuth } from '../composables/useAuth'
import router from '../router'
import axios from 'axios'
const { login, isAuthenticated } = useAuth()
const errorMessage = ref()
const schema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
})

type Schema = z.output<typeof schema>

const state = reactive({
  email: '',
  password: '',
})

const showPassword = ref(false)
const loading = ref(false)

const currencies = [
  { label: 'USD', icon: 'i-lucide-dollar-sign' },
  { label: 'EUR', icon: 'i-lucide-euro' },
  { label: 'GBP', icon: 'i-lucide-pound-sterling' },
  { label: 'BRL', icon: 'i-lucide-badge-dollar-sign' }  
]

async function onSubmit(event: FormSubmitEvent<Schema>) {
  loading.value = true
  try {
    try {
        await login(
            event.data.email,
            event.data.password
        )

        router.push('/')
    } catch (error: any) {
        if (axios.isAxiosError(error)) {           
            errorMessage.value =
            error.response?.data?.message ??
            'Error when logging in'
        }
    }
    
  } finally {
    loading.value = false
  }
}
</script>
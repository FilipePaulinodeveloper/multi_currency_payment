<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import axios from 'axios'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import api from '../services/api'
import { CURRENCIES } from '../Enums/Currencies'

onMounted(async() => {
  await fetchEmployees()
})
// ── Types ──────────────────────────────────────────────────────────────────
type Role = 'finance_admin' | 'employee'

const currencies =ref(CURRENCIES)

type Currency = typeof CURRENCIES[number]

interface Employee {
  id: number
  name: string
  email: string
  password:string
  role: Role
  currency: Currency
}

interface Meta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

// ── State ──────────────────────────────────────────────────────────────────
const employees  = ref<Employee[]>([])
const meta       = ref<Meta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const loading    = ref(false)
const search     = ref('')
const toast      = useToast()

// Modal
const modalOpen  = ref(false)
const isEditing  = ref(false)
const saving     = ref(false)
const deleting   = ref<number | null>(null)

const emptyForm = (): Omit<Employee, 'id'> => ({
  name: '', email: '', role: 'employee', currency: '', password:''
})
const form = ref(emptyForm())
const editId = ref<number | null>(null)

// ── API base (adjust to your Laravel URL / env var) ───────────────────────


// ── Fetch ──────────────────────────────────────────────────────────────────
async function fetchEmployees(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/user', {      
      params: { page, per_page: meta.value.per_page, search: search.value || undefined },
    })
    employees.value = data.data.data
    console.log(employees.value)
    meta.value = {
      current_page: data.current_page,
      last_page:    data.last_page,
      per_page:     data.per_page,
      total:        data.total,
    }
  } catch {
    toast.add({ title: 'Error loading employees', color: 'error' })
  } finally {
    loading.value = false
  }
}

// ── Create / Edit ──────────────────────────────────────────────────────────
function openCreate() {
  isEditing.value = false
  editId.value    = null
  form.value      = emptyForm()
  modalOpen.value = true
}

function openEdit(emp: Employee) {
  isEditing.value = true
  editId.value    = emp.id
  form.value      = { name: emp.name, email: emp.email, role: emp.role, currency: emp.currency, password: emp.password }
  modalOpen.value = true
}
const errors = ref()
function validateForm(): boolean {
  errors.value = {} 
  let isValid = true

  // Name validation (required, max:255)
  if (!form.value.name) {
    errors.value.name = 'Name is required.'
    isValid = false
  } else if (form.value.name.length > 255) {
    errors.value.name = 'Name cannot exceed 255 characters.'
    isValid = false
  }

  // Email validation (required, email, max:255)
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!form.value.email) {
    errors.value.email = 'Email is required.'
    isValid = false
  } else if (!emailRegex.test(form.value.email)) {
    errors.value.email = 'Invalid email format.'
    isValid = false
  } else if (form.value.email.length > 255) {
    errors.value.email = 'Email cannot exceed 255 characters.'
    isValid = false
  }

  // Password validation (required if creating, min:8)
  if (!isEditing.value && !form.value.password) {
    errors.value.password = 'Password is required for new employees.'
    isValid = false
  } else if (form.value.password && form.value.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters long.'
    isValid = false
  }

  // Currency validation (required)
  if (!form.value.currency) {
    errors.value.currency = 'Currency is required.'
    isValid = false
  }

  return isValid
}
async function saveEmployee() {
  // 1. Prevent submission if frontend validation fails
  if (!validateForm()) {
    toast.add({ title: 'Please fix the errors in the form', color: 'error' }) 
    return
  }

  saving.value = true
  try {
    if (isEditing.value && editId.value) {
      // Prevent sending an empty password string on edit
      const payload = { ...form.value }
      if (!payload.password) delete payload.password

      await api.put(`/user/${editId.value}`, payload)
      toast.add({ title: 'Employee updated', color: 'success' })
    } else {
      await api.post('/user', form.value)
      toast.add({ title: 'Employee created', color: 'success' })
    }
    
    modalOpen.value = false
    fetchEmployees(meta.value.current_page)
  } catch (e: any) {
    // 2. Catch Laravel 422 validation errors (like unique email)
    if (e.response?.status === 422) {
      const backendErrors = e.response.data.errors
      for (const field in backendErrors) {
        errors.value[field] = backendErrors[field][0] // Get the first message of the array
      }
      toast.add({ title: 'Validation error', color: 'error' })
    } else {
      const msg = e?.response?.data?.message ?? 'Something went wrong'
      toast.add({ title: msg, color: 'error' })
    }
  } finally {
    saving.value = false
  }
}

// ── Delete ─────────────────────────────────────────────────────────────────
async function deleteEmployee(id: number) {
  deleting.value = id
  try {
    await api.delete(`/user/${id}`)
    toast.add({ title: 'Employee removed', color: 'success' })
    fetchEmployees(meta.value.current_page)
  } catch {
    toast.add({ title: 'Error deleting employee', color: 'error' })
  } finally {
    deleting.value = null
  }
}

// ── Search debounce ────────────────────────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchEmployees(1), 400)
})

// ── Init ───────────────────────────────────────────────────────────────────

</script>

<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-semibold text-white">Employees</h1>      
      </div>
      <UButton
        icon="i-lucide-plus"
        class="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold"
        @click="openCreate"
      >
        New Employee
      </UButton>
    </div>

    <!-- Search -->
    <UInput
      v-model="search"
      icon="i-lucide-search"
      placeholder="Search by name or email..."
      class="max-w-xs"
      :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500' }"
    />

    <!-- Table -->
    <div class="bg-zinc-950 border border-zinc-800 rounded-xl overflow-hidden">

      <!-- Head -->
      <div class="grid grid-cols-[2fr_2fr_1fr_1fr_auto] px-4 py-3 border-b border-zinc-800">
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">Name</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">Email</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">Role</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">Currency</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-right">Actions</span>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-16">
        <UIcon name="i-lucide-loader-circle" class="w-6 h-6 text-emerald-500 animate-spin" />
      </div>

      <!-- Empty -->
      <div
        v-else-if="!employees.length"
        class="flex flex-col items-center justify-center py-16 text-zinc-600"
      >
        <UIcon name="i-lucide-users" class="w-8 h-8 mb-2" />
        <span class="text-sm">No employees found</span>
      </div>

      <!-- Rows -->
      <template v-else>
        <div
          v-for="emp in employees"
          :key="emp.id"
          class="grid grid-cols-[2fr_2fr_1fr_1fr_auto] items-center px-4 py-3 border-b border-zinc-800 last:border-0 hover:bg-zinc-900/50 transition-colors"
        >
          <!-- Name -->
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-emerald-950 border border-emerald-500/30 flex items-center justify-center text-[11px] font-semibold text-emerald-500 shrink-0">
              {{ emp.name.slice(0, 2).toUpperCase() }}
            </div>
            <span class="text-sm font-medium text-zinc-200 truncate">{{ emp.name }}</span>
          </div>

          <!-- Email -->
          <span class="text-sm text-zinc-400 truncate">{{ emp.email }}</span>

          <!-- Role -->
          <span
            class="inline-flex w-fit items-center text-[11px] font-medium px-2.5 py-0.5 rounded-full border"
            :class="emp.role === 'finance_admin'
              ? 'bg-violet-950 text-violet-400 border-violet-500/30'
              : 'bg-zinc-800 text-zinc-400 border-zinc-700'"
          >
            {{ emp.role === 'finance_admin' ? 'Finance Admin' : 'Employee' }}
          </span>

          <!-- Currency -->
          <span class="inline-flex w-fit items-center text-[11px] font-medium px-2 py-0.5 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
            {{ emp.currency }}
          </span>

          <!-- Actions -->
          <div class="flex items-center gap-2 justify-end">
            <UButton
              icon="i-lucide-pencil"
              color="neutral"
              variant="ghost"
              size="xs"
              :ui="{ base: 'text-zinc-500 hover:text-white hover:bg-zinc-800' }"
              aria-label="Edit"
              @click="openEdit(emp)"
            />
            <UButton
              icon="i-lucide-trash-2"
              color="neutral"
              variant="ghost"
              size="xs"
              :loading="deleting === emp.id"
              :ui="{ base: 'text-zinc-500 hover:text-red-400 hover:bg-zinc-800' }"
              aria-label="Delete"
              @click="deleteEmployee(emp.id)"
            />
          </div>
        </div>
      </template>
    </div>

    <!-- Pagination -->
    <div
      v-if="employees.length"
      class="flex items-center justify-between"
    >
      <span class="text-xs text-zinc-600">
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </span>
      <UPagination
        :model-value="meta.current_page"
        :page-count="meta.per_page"
        :total="meta.total"
        :ui="{
          base: 'gap-1',
          button: {
            base: 'text-zinc-400 border border-zinc-800 hover:bg-zinc-800 hover:text-white',
            active: 'bg-emerald-500 text-zinc-950 border-emerald-500 font-semibold',
          },
        }"
        @update:model-value="fetchEmployees"
      />
    </div>

  </div>

  <!-- Create / Edit Modal -->
<UModal v-model:open="modalOpen" :ui="{ content: 'bg-zinc-950 border border-zinc-800' }">
<template #content>
  <div class="p-6 space-y-5">

    <div class="flex items-center justify-between">
      <h2 class="text-base font-semibold text-white">
        {{ isEditing ? 'Edit Employee' : 'New Employee' }}
      </h2>
      <UButton
        icon="i-lucide-x"
        color="neutral"
        variant="ghost"
        size="xs"
        :ui="{ base: 'text-zinc-500 hover:text-white' }"
        @click="modalOpen = false"
      />
    </div>

    <div class="space-y-4">

      <div class="grid grid-cols-2 gap-4">
        
        <div class="flex flex-col space-y-1.5">
          <label class="block text-xs font-medium text-zinc-400">Name</label>
          <UInput
            v-model="form.name"
            placeholder="Full name"
            icon="i-lucide-user"
            :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 w-full' }"
            :class="{ 'ring-1 ring-red-500 rounded-md': errors?.name }"
          />
          <span v-if="errors?.name" class="text-xs text-red-500">{{ errors?.name }}</span>
        </div>

        <div class="flex flex-col space-y-1.5">
          <label class="block text-xs font-medium text-zinc-400">Currency</label>
          <USelectMenu
            v-model="form.currency"
            :items="currencies"         
            value-key="label"
            searchable
            placeholder="Select currency..."
            :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white focus:border-emerald-500 w-full' }"
            :class="{ 'ring-1 ring-red-500 rounded-md': errors?.currency }"
          />
          <span v-if="errors?.currency" class="text-xs text-red-500">{{ errors?.currency }}</span>
        </div>

      </div>

      <div class="flex flex-col space-y-1.5">
        <label class="block text-xs font-medium text-zinc-400">Email</label>
        <UInput
          v-model="form.email"
          type="email"
          placeholder="email@example.com"
          icon="i-lucide-mail"
          :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 w-full' }"
          :class="{ 'ring-1 ring-red-500 rounded-md': errors?.email }"
        />
        <span v-if="errors?.email" class="text-xs text-red-500">{{ errors?.email }}</span>
      </div>

      <div class="flex flex-col space-y-1.5">
        <label class="block text-xs font-medium text-zinc-400">
          Password <span v-if="isEditing" class="text-zinc-500 font-normal ml-1">(Leave blank to keep current)</span>
        </label>
        <UInput
          v-model="form.password"
          type="password"
          placeholder="••••••••"
          icon="i-lucide-lock"
          :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 w-full' }"
          :class="{ 'ring-1 ring-red-500 rounded-md': errors?.password }"
        />
        <span v-if="errors?.password" class="text-xs text-red-500">{{ errors?.password }}</span>
      </div>

    </div>

    <div class="flex justify-end gap-2 pt-1">
      <UButton
        color="neutral"
        variant="ghost"
        :ui="{ base: 'text-zinc-400 hover:text-white border border-zinc-800 hover:bg-zinc-800' }"
        @click="modalOpen = false"
      >
        Cancel
      </UButton>
      <UButton
        :loading="saving"
        class="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold"
        @click="saveEmployee"
      >
        {{ isEditing ? 'Save Changes' : 'Create Employee' }}
      </UButton>
    </div>

  </div>
</template>
</UModal>
</template>
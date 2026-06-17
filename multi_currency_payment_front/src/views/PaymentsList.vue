<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useToast } from '@nuxt/ui/runtime/composables/useToast.js'
import api from '../services/api'
import { CURRENCIES } from '../Enums/Currencies'

const user = ref(
  JSON.parse(localStorage.getItem('user') || '{}')
)




const isFinancialAdmin = computed(() =>
//   console.log(user.value)
  user.value?.role?.includes('finance_admin')
)

// ── Types ──────────────────────────────────────────────────────────────────
type PaymentStatus = 'pending' | 'approved' | 'rejected' | 'expired'
type Currency = typeof CURRENCIES[number]

interface Payment {
  id: number
  description: string
  amount_local: number
  currency: Currency
  amount_eur: number
  exchange_rate: number
  status: PaymentStatus
  created_at: string
}

interface Meta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}



// ── Constants ──────────────────────────────────────────────────────────────
const STATUS_OPTIONS = [
  { label: 'All',      value: ''         },
  { label: 'Pending',  value: 'pending'  },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'Expired',  value: 'expired'  },
]

const STATUS_STYLE: Record<PaymentStatus, string> = {
  pending:  'bg-amber-950 text-amber-400 border-amber-500/30',
  approved: 'bg-emerald-950 text-emerald-400 border-emerald-500/30',
  rejected: 'bg-red-950 text-red-400 border-red-500/30',
  expired:  'bg-zinc-800 text-zinc-400 border-zinc-700',
}

const currencies = ref(CURRENCIES)

// ── State ──────────────────────────────────────────────────────────────────
const payments   = ref<Payment[]>([])
const meta       = ref<Meta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
const loading    = ref(false)
const search     = ref('')
const statusFilter = ref('')
const toast      = useToast()


watch(
  () => meta.value.current_page,
  (page) => {    

    fetchPayments(page)
  }
)
// Modal
const modalOpen  = ref(false)
const saving     = ref(false)

const emptyForm = () => ({ description: '', amount_local: '' as number | '', currency: '' as Currency | '' })
const form      = ref(emptyForm())
const errors    = ref<Record<string, string>>({})

async function updateStatus(id: number, status: PaymentStatus) {
  try {
    await api.put(`/payment-requests/${id}/status`, {
      status
    })

    toast.add({
      title: `Payment ${status}`,
      color: 'success'
    })

    fetchPayments(meta.value.current_page)

  } catch {
    toast.add({
      title: 'Unable to update status',
      color: 'error'
    })
  }
}

async function deletePayment(id: number) {
  try {

    await api.delete(`/payment-requests/${id}`)

    toast.add({
      title: 'Payment deleted',
      color: 'success'
    })

    fetchPayments(meta.value.current_page)

  } catch {

    toast.add({
      title: 'Unable to delete payment',
      color: 'error'
    })

  }
}

// ── Fetch ──────────────────────────────────────────────────────────────────
async function fetchPayments(page = 1) {
  console.log(page)
  loading.value = true
  try {
    const { data } = await api.get('/payment-requests', {
      params: {
        page,
        per_page:  meta.value.per_page,
        search:    search.value    || undefined,
        status:    statusFilter.value || undefined,
      },
    })
    payments.value = data.data.data ?? data.data
    meta.value = {
      current_page: data.data.current_page ?? data.current_page,
      last_page:    data.data.last_page    ?? data.last_page,
      per_page:     data.data.per_page     ?? data.per_page,
      total:        data.data.total        ?? data.total,
    }
  } catch {
    toast.add({ title: 'Error loading payments', color: 'error' })
  } finally {
    loading.value = false
  }
}

// ── Create ─────────────────────────────────────────────────────────────────
function openCreate() {
  form.value   = emptyForm()
  errors.value = {}
  modalOpen.value = true
}

function validateForm(): boolean {
  errors.value = {}
  let valid = true

  if (!form.value.description.trim()) {
    errors.value.description = 'Description is required.'
    valid = false
  }
  if (!form.value.amount_local || Number(form.value.amount_local) <= 0) {
    errors.value.amount_local = 'Amount must be greater than 0.'
    valid = false
  }
  if (!form.value.currency) {
    errors.value.currency = 'Currency is required.'
    valid = false
  }

  return valid
}

async function savePayment() {
  if (!validateForm()) {
    toast.add({ title: 'Please fix the errors in the form', color: 'error' })
    return
  }
  saving.value = true
  try {    
    
    await api.post('/payment-requests', {
      description:  form.value.description,
      amount_local: Number(form.value.amount_local),
      currency:     form.value.currency,
    })
    toast.add({ title: 'Payment request submitted', color: 'success' })
    modalOpen.value = false
    fetchPayments(1)
  } catch (e: any) {
    console.log(e)
    if (e.response?.status === 422) {
      const be = e.response.data.errors
      for (const f in be) errors.value[f] = be[f][0]
      toast.add({ title: 'Validation error', color: 'error' })
    } else {
      toast.add({ title: e?.response?.data?.message ?? 'Something went wrong', color: 'error' })
    }
  } finally {
    saving.value = false
  }
}

// ── Helpers ────────────────────────────────────────────────────────────────
function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
function formatNumber(n: number, decimals = 2) {
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
}

// ── Watchers ───────────────────────────────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchPayments(1), 400)
})
watch(statusFilter, () => fetchPayments(1))

// ── Init ───────────────────────────────────────────────────────────────────
onMounted(fetchPayments)
</script>

<template>
  <div class="space-y-5">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-lg font-semibold text-white">Payments</h1>
        <p class="text-sm text-zinc-500 mt-0.5">{{ meta.total }} total requests</p>
      </div>
      <UButton
        icon="i-lucide-plus"
        class="bg-emerald-500 hover:bg-emerald-600 text-zinc-950 font-semibold"
        @click="openCreate"
      >
        New Payment
      </UButton>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-3 flex-wrap">     
      <!-- Status filter tabs -->
      <div class="flex items-center gap-1 bg-zinc-900 border border-zinc-800 rounded-lg p-1">
        <button
          v-for="opt in STATUS_OPTIONS"
          :key="opt.value"
          class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
          :class="statusFilter === opt.value
            ? 'bg-emerald-500 text-zinc-950'
            : 'text-zinc-400 hover:text-white hover:bg-zinc-800'"
          @click="statusFilter = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-zinc-950 border border-zinc-800 rounded-xl overflow-hidden">

      <!-- Head -->
      <div class="grid grid-cols-[2fr_1fr_1fr_1fr_1fr_1fr_1fr_180px] px-4 py-3 border-b border-zinc-800">
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider">Description</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-right">Local Amount</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-center">Currency</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-right">Amount (EUR)</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-right">Rate</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-center">Status</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-right">Date</span>
        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-wider text-center">Actions</span>
        
        
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-16">
        <UIcon name="i-lucide-loader-circle" class="w-6 h-6 text-emerald-500 animate-spin" />
      </div>

      <!-- Empty -->
      <div
        v-else-if="!payments.length"
        class="flex flex-col items-center justify-center py-16 text-zinc-600"
      >
        <UIcon name="i-lucide-receipt" class="w-8 h-8 mb-2" />
        <span class="text-sm">No payments found</span>
      </div>

      <!-- Rows -->
      <template v-else>
        <div
          v-for="p in payments"
          :key="p.id"
          class="grid grid-cols-[2fr_1fr_1fr_1fr_1fr_1fr_1fr_180px] items-center px-4 py-3 border-b border-zinc-800 last:border-0 hover:bg-zinc-900/50 transition-colors"
        >
          <!-- Description -->
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center shrink-0">
              <UIcon name="i-lucide-file-text" class="w-3.5 h-3.5 text-zinc-400" />
            </div>
            <span class="text-sm text-zinc-200 truncate">{{ p.description }}</span>
          </div>

          <!-- Local Amount -->
          <span class="text-sm text-zinc-200 font-medium text-right tabular-nums">
            {{ formatNumber(p.amount_local) }}
          </span>

          <!-- Currency -->
          <div class="flex justify-center">
            <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-zinc-800 text-zinc-300 border border-zinc-700">
              {{ p.currency }}
            </span>
          </div>

          <!-- Amount EUR -->
          <span class="text-sm text-emerald-400 font-medium text-right tabular-nums">
            €{{ formatNumber(p.amount_eur) }}
          </span>

          <!-- Exchange rate -->
          <span class="text-sm text-zinc-500 text-right tabular-nums">
            {{ formatNumber(p.exchange_rate, 4) }}
          </span>

          <!-- Status -->
          <div class="flex justify-center">
            <span
              class="inline-flex items-center text-[11px] font-medium px-2.5 py-0.5 rounded-full border capitalize"
              :class="STATUS_STYLE[p.status]"
            >
              {{ p.status }}
            </span>
          </div>

          <!-- Date -->
          <span class="text-xs text-zinc-500 text-right whitespace-nowrap">
            {{ formatDate(p.created_at) }}
          </span>

          <div class="flex items-center justify-center gap-2">

            <!-- Financial Admin -->

            <template v-if="isFinancialAdmin && p.status === 'pending'">

                <UButton
                size="xs"
                icon="i-lucide-check"
                class="bg-emerald-500 hover:bg-emerald-600 text-zinc-950"
                @click="updateStatus(p.id, 'approved')"
                />

                <UButton
                size="xs"
                icon="i-lucide-x"
                class="bg-red-500 hover:bg-red-600 text-white"
                @click="updateStatus(p.id, 'rejected')"
                />

            </template>

            <!-- Todos -->

            <UButton
                size="xs"
                icon="i-lucide-trash-2"
                color="error"
                variant="soft"
                @click="deletePayment(p.id)"
            />

            </div>
          
        </div>
      </template>
    </div>

    <!-- Pagination -->
    <div v-if="meta.last_page > 1" class="flex items-center justify-between">
      <span class="text-xs text-zinc-600">
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </span>
      {{meta.current_page}}
      <UPagination
        v-model:page="meta.current_page"
        :page-count="meta.per_page"
        :total="meta.total"
        :ui="{
          base: 'gap-1',
          button: {
            base: 'text-zinc-400 border border-zinc-800 hover:bg-zinc-800 hover:text-white',
            active: 'bg-emerald-500 text-zinc-950 border-emerald-500 font-semibold',
          },
        }"
        @update:page="fetchPayments"
      />

  
    </div>
  </div>

  <!-- New Payment Modal -->
  <UModal v-model:open="modalOpen" :ui="{ content: 'bg-zinc-950 border border-zinc-800' }">
    <template #content>
      <div class="p-6 space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-base font-semibold text-white">New Payment</h2>
            <p class="text-xs text-zinc-500 mt-0.5">Submit a payment request for approval.</p>
          </div>
          <UButton
            icon="i-lucide-x"
            color="neutral"
            variant="ghost"
            size="xs"
            :ui="{ base: 'text-zinc-500 hover:text-white hover:bg-zinc-800' }"
            @click="modalOpen = false"
          />
        </div>

        <!-- Fields -->
        <div class="space-y-4">

          <!-- Description -->
          <div class="flex flex-col space-y-1.5">
            <label class="text-xs font-medium text-zinc-400">Description</label>
            <UInput
              v-model="form.description"
              placeholder="e.g. Supplier invoice #1234"
              icon="i-lucide-file-text"
              :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 w-full' }"
              :class="{ 'ring-1 ring-red-500 rounded-md': errors.description }"
            />
            <span v-if="errors.description" class="text-xs text-red-500">{{ errors.description }}</span>
          </div>

          <!-- Amount + Currency side by side -->
          <div class="grid grid-cols-2 gap-4">

            <div class="flex flex-col space-y-1.5">
              <label class="text-xs font-medium text-zinc-400">Amount</label>
              <UInput
                v-model="form.amount_local"
                type="number"
                min="0"
                max="999999999.99"
                step="0.01"
                placeholder="0.00"
                icon="i-lucide-banknote"
                :ui="{ base: 'bg-zinc-900 border-zinc-800 text-white placeholder-zinc-600 focus:border-emerald-500 w-full' }"
                :class="{ 'ring-1 ring-red-500 rounded-md': errors.amount_local }"
              />
           
              <span v-if="errors.amount_local" class="text-xs text-red-500">{{ errors.amount_local }}</span>
            </div>

            <div class="flex flex-col space-y-1.5">
              <label class="text-xs font-medium text-zinc-400">Currency</label>
              <USelectMenu
                v-model="form.currency"
                :items="currencies"
                 value-key="label"
                searchable
                placeholder="Select currency..."
                :ui="{
                  trigger: 'w-full bg-zinc-900 border border-zinc-800 text-white rounded-lg hover:border-zinc-700 data-[state=open]:border-emerald-500',
                  content: 'bg-zinc-900 border border-zinc-800 rounded-lg z-50',
                  item: 'text-zinc-300 hover:bg-zinc-800 hover:text-white data-[highlighted]:bg-zinc-800',
                  input: 'bg-zinc-800 border-zinc-700 text-white placeholder-zinc-500',
                }"
                :class="{ 'ring-1 ring-red-500 rounded-md': errors.currency }"
              />
              <span v-if="errors.currency" class="text-xs text-red-500">{{ errors.currency }}</span>
            </div>

          </div>

          <!-- Info box -->
          <div class="flex items-start gap-2.5 bg-zinc-900 border border-zinc-800 rounded-lg px-3.5 py-3">
            <UIcon name="i-lucide-info" class="w-4 h-4 text-zinc-500 mt-0.5 shrink-0" />
            <p class="text-xs text-zinc-500 leading-relaxed">
              The EUR equivalent and exchange rate will be calculated automatically upon submission.
              Your request will be reviewed and approved by a Finance Admin.
            </p>
          </div>

        </div>

        <!-- Actions -->
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
            @click="savePayment"
          >
            Submit Request
          </UButton>
        </div>

      </div>
    </template>
  </UModal>
</template>
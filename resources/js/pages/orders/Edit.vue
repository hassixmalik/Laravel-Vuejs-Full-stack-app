<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, X } from 'lucide-vue-next';
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import SearchableSelect from '@/components/SearchableSelect.vue'

import type { AppPageProps as AppPageProps } from '@/types'

type Option = { label: string; value: number }
type ProductOption = { label: string; value: number; price: number; stock: number; available: number; }
type StatusOption = { label: string; value: 'draft' | 'placed' | 'cancelled' | 'fulfilled' }
const isFulfilled = computed(() => page.props.order.status === 'fulfilled') // to disable edits if fulfilled

type OrderPayload = {
  id: number
  customer_id: number
  placed_by: number
  status: 'draft' | 'placed' | 'cancelled' | 'fulfilled'
  total: number
  items: { product_id: number | null; qty: number; unit_total: number | null }[]
}

type EditPageProps = AppPageProps & {
  order: OrderPayload
  customerOptions: Option[]
  productOptions: ProductOption[]
  statusOptions: StatusOption[]
  placedBy: number
  placedByName: string
  oldQtyMap: Record<number, number>
  flash?: { message?: string }
}

const page = usePage<EditPageProps>()

// init form from page.props.order
const form = useForm<OrderPayload>({
  id: page.props.order.id,
  customer_id: page.props.order.customer_id,
  placed_by: page.props.order.placed_by, // keep id; show name in UI
  status: page.props.order.status,
  total: page.props.order.total,
  items: page.props.order.items.length
    ? page.props.order.items.map(it => ({
      product_id: it.product_id,
      qty: it.qty,
      unit_total: it.unit_total,
    }))
    : [{ product_id: null, qty: 1, unit_total: 0.000 }],
})

function addItem() {
  form.items.push({ product_id: null, qty: 1, unit_total: 0.000 })
}
function removeItem(idx: number) {
  form.items.splice(idx, 1)
}
function handleSubmit() {
  // PUT to /orders/{id}
  form.put(`/orders/${form.id}`)
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Orders', href: '/orders' },
  { title: `Edit #${form.id}`, href: `/orders/${form.id}/edit` },
];
function productAvailable(productId: number | null): number {
  if (productId == null) return 0
  const opt = page.props.productOptions.find(o => o.value === productId)
  return opt ? Number(opt.available) : 0
}
function oldQtyFor(productId: number | null): number {
  if (productId == null) return 0
  return page.props.oldQtyMap?.[productId] ?? 0
}
function allowedQty(productId: number | null): number {
  return productAvailable(productId) + oldQtyFor(productId)
}
function lineExceedsStock(idx: number): boolean {
  const it = form.items[idx]
  if (!it.product_id) return false
  return (Number(it.qty) || 0) > allowedQty(it.product_id)
}

// pricing & stock helpers (same behavior as Create)
function productPrice(productId: number | null): number {
  if (productId == null) return 0
  const opt = page.props.productOptions.find(o => o.value === productId)
  return opt ? Number(opt.price) : 0
}
// function productStock(productId: number | null): number {
//   if (productId == null) return 0
//   const opt = page.props.productOptions.find(o => o.value === productId)
//   return opt ? Number(opt.stock) : 0
// }
function updateLine(idx: number) {
  const it = form.items[idx]
  const total = Number(((productPrice(it.product_id) || 0) * (it.qty || 0)).toFixed(3))
  it.unit_total = total
}
const computedTotal = computed(() =>
  Number(form.items.reduce((acc, it) => acc + Number(it.unit_total ?? 0), 0).toFixed(3))
)

const hasAnyStockError = computed(() =>
  form.items.some((_, idx) => lineExceedsStock(idx))
)
const isFormInvalid = computed(() => {
  const missingCustomer = !form.customer_id
  const missingAnyProduct = form.items.some(it => !it.product_id)
  const anyBadQty = form.items.some(it => !it.qty || it.qty < 1)
  return missingCustomer || missingAnyProduct || anyBadQty || hasAnyStockError.value
})
</script>

<template>

  <Head title="Edit Order" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-3">
      <Link href="/orders/">
      <Button>
        <ArrowLeft />
      </Button>
      </Link>

      <div v-if="page.props.flash?.message" class="rounded-md bg-blue-50 p-2 text-sm my-2">
        {{ page.props.flash.message }}
      </div>
      <!-- after the back button / flash -->
      <div v-if="isFulfilled" class="my-3 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
        This order is <span class="font-semibold">fulfilled</span> and can’t be edited.
        Create new order? <Link href="/orders/createOrder" class="underline">Click here</Link>.
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold">Items</h3>
            <Button type="button" @click="addItem">Add Item</Button>
          </div>

          <div class="grid grid-cols-12 gap-3" v-for="(it, idx) in form.items" :key="idx">
            <div class="col-span-6">
              <Label class="mb-1">Product</Label>
              <SearchableSelect v-model="it.product_id" :options="page.props.productOptions"
                placeholder="Select product" @change="() => updateLine(idx)" />
              <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.product_id`]">
                {{ form.errors[`items.${idx}.product_id`] }}
              </div>
              <p class="text-xs text-gray-500 mt-1" v-if="it.product_id">
                In Stock: {{ productAvailable(it.product_id) }} 
              </p>
            </div>

            <div class="col-span-2">
              <Label class="mb-1">Qty</Label>
              <Input type="number" min="1" v-model.number="it.qty" @input="() => updateLine(idx)" />
              <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.qty`]">
                {{ form.errors[`items.${idx}.qty`] }}
              </div>
              <p class="text-xs mt-1" :class="lineExceedsStock(idx) ? 'text-red-600' : 'text-gray-500'">
                <template v-if="it.product_id">
                  <template v-if="lineExceedsStock(idx)">
                    Request exceeds available qty ({{ allowedQty(it.product_id) }}).
                  </template>
                  <template v-else>
                    Max Allowed: {{ allowedQty(it.product_id) }}
                  </template>
                </template>
              </p>
            </div>

            <div class="col-span-3">
              <Label class="mb-1">Price</Label>
              <Input type="number" min="0" step="0.001" :value="Number(it.unit_total ?? 0).toFixed(3)"
                placeholder="0.000" disabled />
              <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.unit_total`]">
                {{ form.errors[`items.${idx}.unit_total`] }}
              </div>
            </div>

            <div class="col-span-1 flex items-center">
              <Button type="button" class="bg-red-400" @click="removeItem(idx)">
                <X />
              </Button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-6 border-t pt-4">
          <div class="text-sm text-gray-600">Total so far</div>
          <div class="text-lg font-semibold tabular-nums">
            {{ computedTotal.toFixed(3) }}
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label class="mb-2">Customer</Label>
            <SearchableSelect v-model="form.customer_id" :options="page.props.customerOptions"
              placeholder="Select customer" />
            <div class="text-sm text-red-500" v-if="form.errors.customer_id">{{ form.errors.customer_id }}</div>
          </div>

          <div>
            <Label class="mb-2">Status</Label>
            <SearchableSelect v-model="form.status" :options="page.props.statusOptions" placeholder="Select status" />
            <div class="text-sm text-red-500" v-if="form.errors.status">{{ form.errors.status }}</div>
          </div>

          <div>
            <Label class="mb-2">Placed By</Label>
            <Input type="text" :value="page.props.placedByName" :placeholder="page.props.placedByName" disabled />
            <input type="hidden" v-model="form.placed_by" />
          </div>
        </div>

        <Button type="submit" :disabled="form.processing || isFormInvalid">Update Order</Button>
      </form>
    </div>
  </AppLayout>
</template>

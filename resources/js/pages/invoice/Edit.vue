<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { computed } from 'vue';


type BoolOption = { label: string; value: boolean };
type StatusOption = { label: string; value: 'draft'|'issued'|'void' };
type ItemRow = { id:number; description:string; quantity:number; price:number; total:number };

type CreatePageProps = AppPageProps & {
  invoice: {
    id: number
    order_id: number
    order_label: string
    status: 'draft'|'issued'|'void'
    vat_enabled: boolean
    discount: number
    subtotal: number
    total: number
    vat_rate: number
    customer: { name:string; email:string; phone:string; address:string }
    placed_by_name: string
    items: ItemRow[]
  },
  statusOptions: StatusOption[],
  vatOptions: BoolOption[],
}

const page = usePage<CreatePageProps>();

const form = useForm({
  discount: page.props.invoice.discount,
  vat_enabled: page.props.invoice.vat_enabled,
  status: page.props.invoice.status as 'draft'|'issued'|'void',
});

const net = computed(() => Math.max(page.props.invoice.subtotal - form.discount, 0));
const vatAmount = computed(() => form.vat_enabled ? Number((net.value * page.props.invoice.vat_rate).toFixed(3)) : 0);
const total = computed(() => Number((net.value + vatAmount.value).toFixed(3)));

function handleSubmit() {
  form.put(`/invoice/${page.props.invoice.id}`)
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Invoices', href: '/invoice' },
  { title: `#${page.props.invoice.id}`, href: `/invoice/${page.props.invoice.id}` },
  { title: 'Edit', href: `/invoice/${page.props.invoice.id}/edit` },
];
</script>

<template>
  <Head :title="`Edit Invoice #${page.props.invoice.id}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-6">
      <div class="mb-3">
        <Link :href="`/invoice/${page.props.invoice.id}`"><Button><ArrowLeft /></Button></Link>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-3 gap-4">
          <div class="col-span-3 md:col-span-1">
            <Label class="mb-2">Order</Label>
            <Input type="text" :placeholder="page.props.invoice.order_label" disabled />
          </div>

          <div>
            <Label class="mb-2">Status</Label>
            <SearchableSelect
              v-model="form.status"
              :options="page.props.statusOptions"
              placeholder="Select status"
            />
            <div class="text-sm text-red-500" v-if="form.errors.status">{{ form.errors.status }}</div>
          </div>

          <div>
            <Label class="mb-2">VAT</Label>
            <SearchableSelect
              v-model="form.vat_enabled"
              :options="page.props.vatOptions"
              placeholder="VAT"
            />
            <div class="text-sm text-red-500" v-if="form.errors.vat_enabled">{{ form.errors.vat_enabled }}</div>
          </div>

          <div>
            <Label class="mb-2">Discount</Label>
            <Input type="number" step="0.001" v-model.number="form.discount" placeholder="0.000" />
            <div class="text-sm text-red-500" v-if="form.errors.discount">{{ form.errors.discount }}</div>
          </div>
        </div>

        <!-- Summary -->
        <div class="rounded-lg border p-4 bg-white space-y-1">
          <div class="text-sm"><span class="font-semibold">Customer:</span> {{ page.props.invoice.customer.name }}</div>
          <div class="text-sm"><span class="font-semibold">Created By:</span> {{ page.props.invoice.placed_by_name }}</div>
          <div class="text-sm"><span class="font-semibold">Subtotal:</span> {{ page.props.invoice.subtotal.toFixed(3) }} BD</div>
          <div class="text-sm"><span class="font-semibold">Discount:</span> {{ form.discount.toFixed(3) }} BD</div>
          <div class="text-sm"><span class="font-semibold">VAT:</span> {{ vatAmount.toFixed(3) }} BD</div>
          <div class="text-sm font-semibold"><span>Total:</span> {{ total.toFixed(3) }} BD</div>
          <p class="text-xs text-gray-500 mt-2">.</p>
        </div>

        <Button type="submit" :disabled="form.processing">Update Invoice</Button>
      </form>

      <!-- Optional: show current items (read-only) -->
      <div class="rounded-lg border p-4 bg-white">
        <h3 class="font-semibold mb-2">Items</h3>
        <table class="w-full border-collapse border text-left">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2">Item</th>
              <th class="border p-2">Qty</th>
              <th class="border p-2">Price</th>
              <th class="border p-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="it in page.props.invoice.items" :key="it.id">
              <td class="border p-2">{{ it.description }}</td>
              <td class="border p-2">{{ it.quantity }}</td>
              <td class="border p-2">{{ it.price.toFixed(3) }}</td>
              <td class="border p-2">{{ it.total.toFixed(3) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

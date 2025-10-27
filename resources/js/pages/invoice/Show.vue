<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer, SquarePen } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type InvoiceItem = {
  id: number
  description: string
  quantity: number
  price: number   // unit price (3 dp)
  total: number   // line total (3 dp)
}

type InvoicePayload = {
  id: number
  date: string | null
  due_date: string | null
  status: string
  customer: { name: string; email: string; phone: string; address: string }
  items: InvoiceItem[]
  subtotal: number
  discount: number
  total: number
  vat_enabled: boolean
  vat_rate: number   // fraction (e.g., 0.15)
  created_by: string
}

const props = defineProps<{ invoice: InvoicePayload }>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Invoices', href: '/invoice' },
  { title: `#${props.invoice.id}`, href: `/invoice/${props.invoice.id}` },
];

// Derived amounts (keep 3 dp)
const subtotal = computed(() => props.invoice.subtotal);
const discount = computed(() => props.invoice.discount);
const net = computed(() => Math.max(subtotal.value - discount.value, 0));
const vatAmount = computed(() => props.invoice.total - net.value);
const total = computed(() => props.invoice.total);

// Print ONLY the invoice card
const printTarget = ref<HTMLElement | null>(null);
function printInvoice() {
  const html = printTarget.value?.innerHTML ?? '';
  const w = window.open('', '', 'width=900,height=1200');
  if (!w) return;
  w.document.write(`
    <html>
      <head>
        <title>Invoice #${props.invoice.id}</title>
        <style>
          body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; padding: 24px; }
          h1,h2 { margin: 0 0 8px; }
          .mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}
          table{width:100%; border-collapse: collapse;}
          th,td{border:1px solid #e5e7eb; padding:8px; text-align:left}
          thead{background:#f3f4f6}
          .right{text-align:right}
        </style>
      </head>
      <body>${html}</body>
    </html>
  `);
  w.document.close();
  w.focus();
  w.print();
  w.close();
}
</script>

<template>
  <Head :title="`Invoice #${invoice.id}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex justify-between items-center p-4">
      <div>
        <Link href="/invoice">
          <Button><ArrowLeft /></Button>
        </Link>
      </div>

      <div class="space-x-2">
        <Button @click="printInvoice"><Printer /></Button>
        <Link :href="`/invoice/${invoice.id}/edit`">
          <Button><SquarePen /></Button>
        </Link>
      </div>
    </div>

    <div class="flex justify-center">
      <!-- Print target -->
      <div class="p-8 max-w-3xl w-full bg-white rounded shadow" ref="printTarget">
        <h1 class="text-3xl font-bold mb-4">Invoice #{{ invoice.id }}</h1>

        <div class="mb-6">
          <p><strong>Date:</strong> {{ invoice.date ?? '—' }}</p>
          <p><strong>Due Date:</strong> {{ invoice.due_date ?? '—' }}</p>
          <p>
            <strong>Status:</strong>
            <span :class="invoice.status === 'issued' ? 'text-green-600' : invoice.status === 'draft' ? 'text-yellow-600' : 'text-red-600'">
               {{ invoice.status }}
            </span>
          </p>
          <p><strong>Created By:</strong> {{ invoice.created_by }}</p>
        </div>

        <div class="mb-6">
          <h2 class="text-xl font-semibold">Customer</h2>
          <p>{{ invoice.customer.name }}</p>
          <p>{{ invoice.customer.email }}</p>
          <p>{{ invoice.customer.phone }}</p>
          <p>{{ invoice.customer.address }}</p>
        </div>

        <table class="w-full border-collapse border text-left mb-6">
          <thead class="bg-gray-100">
            <tr>
              <th class="border p-2">Item</th>
              <th class="border p-2">Qty</th>
              <th class="border p-2">Price</th>
              <th class="border p-2">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in invoice.items" :key="item.id">
              <td class="border p-2">{{ item.description }}</td>
              <td class="border p-2">{{ item.quantity }}</td>
              <td class="border p-2">{{ item.price.toFixed(3) }}</td>
              <td class="border p-2">{{ item.total.toFixed(3) }}</td>
            </tr>
          </tbody>
        </table>

        <div class="text-right">
          <p>Subtotal: {{ subtotal.toFixed(3) }} BD</p>
          <p>Discount: {{ discount.toFixed(3) }} BD</p>
          <p>
            VAT
            <span v-if="invoice.vat_enabled">
              ({{ (invoice.vat_rate * 100).toFixed(0) }}%)
            </span>
            :
            {{ vatAmount.toFixed(3) }} BD
          </p>
          <p class="font-bold text-lg">Total: {{ total.toFixed(3) }} BD</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

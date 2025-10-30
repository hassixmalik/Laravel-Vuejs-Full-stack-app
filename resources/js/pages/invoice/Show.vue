<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Printer, SquarePen, Send, Rocket, Trash } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3'
import Alert from '@/components/ui/alert/Alert.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogTrigger from '@/components/ui/dialog/DialogTrigger.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';


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

const emailForm = useForm({})

function sendByEmail(id: number) {
  emailForm.post(`/invoice/${id}/email`)
}
const handleDelete = (id: number) => {
  router.delete(`/invoice/${id}`);
}
</script>

<template>

  <Head :title="`Invoice #${invoice.id}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex justify-between items-center p-4">
      <div>
        <Link href="/invoice">
        <Button>
          <ArrowLeft />
        </Button>
        </Link>
      </div>

      <div class="space-x-2">
        <Button @click="printInvoice">
          <Printer />
        </Button>

        <Link :href="`/invoice/${invoice.id}/edit`"><Button class="bg-slate-600">
          <SquarePen />
        </Button></Link>

        <Dialog>
          <DialogTrigger as-child>
            <Button class="bg-red-400" :disabled="invoice.status === 'issued'">
              <Trash />
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
              <DialogTitle>Delete Invoice</DialogTitle>
            </DialogHeader>
            <div>Do you want to delete Invoice#{{ invoice.id }}?</div>
            <DialogFooter>
              <Button class="bg-red-500" @click="handleDelete(invoice.id)">
                Delete
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

      </div>
      <form @submit.prevent="sendByEmail(invoice.id)">
        <Button type="submit" :disabled="emailForm.processing" class="bg-blue-600 hover:bg-blue-700">
          <span v-if="emailForm.processing">Sending...</span>
          <span v-else>Email</span><span>
            <Send class="w-4" />
          </span>
        </Button>
      </form>
    </div>
    <div v-if="$page.props.errors?.email" class="p-2 text-sm text-red-600">
      {{ $page.props.errors.email }}
    </div>
    <div v-if="$page.props.flash?.message" class="p-2 text-sm text-green-600">
      <Alert class="bg-blue-200 dark:bg-neutral-700 dark:text-neutral-100">
        <Rocket class="h-4 w-4" />
        <AlertTitle>Notification!</AlertTitle>
        <AlertDescription>
          {{ $page.props.flash.message }}
        </AlertDescription>
      </Alert>
    </div>

    <div class="flex justify-center">
      <!-- Print target -->
      <div class="dark:bg-neutral-700 dark:text-neutral-100 p-8 max-w-3xl w-full bg-white rounded shadow" ref="printTarget">
        <h1 class="text-3xl font-bold mb-4">Invoice #{{ invoice.id }}</h1>

        <div class="mb-6">
          <p><strong>Date:</strong> {{ invoice.date ?? '—' }}</p>
          <p><strong>Due Date:</strong> {{ invoice.due_date ?? '—' }}</p>
          <p>
            <strong>Status</strong>
            <span
              :class="invoice.status === 'issued' ? 'text-green-600' : invoice.status === 'draft' ? 'text-yellow-600' : 'text-red-600'">
              : {{ invoice.status }}
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
          <thead class="dark:bg-neutral-700 dark:text-neutral-100 bg-gray-100">
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

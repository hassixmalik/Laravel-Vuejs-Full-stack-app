<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
// import SearchableSelect from '@/components/SearchableSelect.vue'

// ✅ ADD: props typing for invoices list
type InvoiceStatus = 'draft' | 'issued' | 'void'

type InvoiceRow = {
  id: number
  invoice_no: string
  order_no: number
  customer: string
  created_by: string
  status: InvoiceStatus
  subtotal: string
  discount: string
  total: string
  created_at: string
}

type LinkItem = { url: string | null; label: string; active: boolean }

type Paginated<T> = {
  data: T[]
  links: LinkItem[]
}

interface Props {
  invoices: Paginated<InvoiceRow>
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Invoices',
    href: '/invoice',
  },
];
</script>

<template>

  <Head title="Invoices" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <Link href="/invoice/create"><Button>Add Invoice</Button></Link>
    </div>

    <Table>
      <TableCaption>A list of your recent invoices.</TableCaption>

      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">Invoice #</TableHead>
          <TableHead>Order #</TableHead>
          <TableHead>Customer</TableHead>
          <TableHead>Created By</TableHead>
          <TableHead>Status</TableHead>
          <TableHead class="text-right">Subtotal</TableHead>
          <TableHead class="text-right">Discount</TableHead>
          <TableHead class="text-right">Total</TableHead>
          <TableHead>Created At</TableHead>
          <TableHead class="text-center">Actions</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow v-for="row in props.invoices.data" :key="row.id">
          <TableCell class="font-medium">{{ row.invoice_no }}</TableCell>
          <TableCell>#{{ row.order_no }}</TableCell>
          <TableCell>{{ row.customer }}</TableCell>
          <TableCell>{{ row.created_by }}</TableCell>
          <TableCell
            :class="row.status === 'issued' ? 'text-green-600' : row.status === 'draft' ? 'text-yellow-600' : 'text-red-600'">
            {{ row.status }}
          </TableCell>
          <TableCell class="text-right">{{ row.subtotal }}</TableCell>
          <TableCell class="text-right">{{ row.discount }}</TableCell>
          <TableCell class="text-right">{{ row.total }}</TableCell>
          <TableCell>{{ row.created_at }}</TableCell>
          <TableCell class="text-center">
            <Link :href="`/invoice/${row.id}`"><Button>View</Button></Link>
          </TableCell>
        </TableRow>
      </TableBody>

    </Table>

  </AppLayout>
</template>

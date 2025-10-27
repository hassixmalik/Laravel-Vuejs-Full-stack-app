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
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { AppPageProps as AppPageProps } from '@/types'
import { Eye, SquarePen} from 'lucide-vue-next';


type OrderRow = {
  id: number
  customer: string | null
  placed_by: string | null
  status: 'draft' | 'placed' | 'cancelled' | 'fulfilled'
  total: string
  created_at: string
  items_count: number
}

const page = usePage<AppPageProps & { orders: OrderRow[] }>();

// tiny helper for status color
function statusClass(s: OrderRow['status']) {
  return {
    draft: 'text-yellow-600',
    placed: 'text-blue-600',
    cancelled: 'text-red-600',
    fulfilled: 'text-green-600',
  }[s] ?? '';
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Orders',
    href: '/orders',
  },
];


</script>

<template>

  <Head title="Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <Link href="/orders/createOrder"><Button>Create Order</Button></Link>
    </div>
    <Table>
      <TableCaption>Orders List.</TableCaption>

      <TableHeader>
        <TableRow>
          <TableHead class="w-[80px]">ID</TableHead>
          <TableHead>Customer</TableHead>
          <TableHead>Placed By</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Total</TableHead>
          <TableHead>Created At</TableHead>
          <TableHead class="text-center">Actions</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow v-for="o in page.props.orders" :key="o.id">
          <TableCell class="font-medium">{{ o.id }}</TableCell>
          <TableCell>{{ o.customer ?? '—' }}</TableCell>
          <TableCell>{{ o.placed_by ?? '—' }}</TableCell>
          <TableCell :class="statusClass(o.status)">{{ o.status }}</TableCell>
          <TableCell class="tabular-nums">BD {{ o.total }}</TableCell>
          <TableCell>{{ o.created_at }}</TableCell>
          <TableCell class="text-center">
            <Button class="bg-blue-400 mx-1"><Eye /></Button>
            <Link :href="`/orders/${o.id}/edit`"><Button class="bg-slate-400 mx-1"><SquarePen /></Button></Link>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

  </AppLayout>
</template>

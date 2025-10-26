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
        <TableRow>
          <TableCell class="font-medium">1</TableCell>
          <TableCell>Ali Khan</TableCell>
          <TableCell>Admin User</TableCell>
          <TableCell class="text-yellow-500">Draft</TableCell>
          <TableCell>245.250</TableCell>
          <TableCell>2025-10-26 14:30</TableCell>
          <TableCell class="text-center">
            <Button class="bg-slate-600">View</Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

  </AppLayout>
</template>

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
      <TableCaption>A list of your recent Orders.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">
            Order#
          </TableHead>
          <TableHead>Status</TableHead>
          <TableHead>To</TableHead>
          <TableHead class="text-right">
            Amount
          </TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow>
          <TableCell class="font-medium">
            OR734
          </TableCell>
          <TableCell>Paid</TableCell>
          <TableCell>Mr. Abdullah</TableCell>
          <TableCell class="text-right">
            $250.00
          </TableCell>
          <TableCell><Button>view</Button></TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>

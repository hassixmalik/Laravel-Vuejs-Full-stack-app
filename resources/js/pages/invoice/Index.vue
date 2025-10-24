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
          <TableHead class="w-[100px]">
            Invoice
          </TableHead>
          <TableHead>Status</TableHead>
          <TableHead>To</TableHead>
          <TableHead>Method</TableHead>
          <TableHead class="text-right">
            Amount
          </TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow>
          <TableCell class="font-medium">
            INV001
          </TableCell>
          <TableCell>Paid</TableCell>
          <TableCell>Mr. Abdullah</TableCell>
          <TableCell>Credit Card</TableCell>
          <TableCell class="text-right">
            BD 250.00
          </TableCell>
          <TableCell><Link href="/invoice/detail"><Button>view</Button></Link></TableCell>
        </TableRow>
        <TableRow>
          <TableCell class="font-medium">
            INV002
          </TableCell>
          <TableCell>Unpaid</TableCell>
          <TableCell>ABC</TableCell>
          <TableCell>Credit Card</TableCell>
          <TableCell class="text-right">
            BD 250.00
          </TableCell>
          <TableCell><Link href="/invoice/detail"><Button>view</Button></Link></TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>

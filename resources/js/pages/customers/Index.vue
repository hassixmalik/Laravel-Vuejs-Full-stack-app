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
    title: 'Customers',
    href: '/customers',
  },
];


</script>

<template>

  <Head title="Customers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <Link href="/inventory/create"><Button>Add Customer</Button></Link>
    </div>
    <Table>
      <TableCaption>Customers List.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">
            Product ID
          </TableHead>
          <TableHead>Type</TableHead>
          <TableHead>QTY</TableHead>
          <TableHead>Last Update</TableHead>
          <TableHead class="text-right">
            Amount
          </TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow>
          <TableCell class="font-medium">
            P002
          </TableCell>
          <TableCell>Home Appliance</TableCell>
          <TableCell>30 pc</TableCell>
          <TableCell>10/03/2025</TableCell>
          <TableCell class="text-right">
            BD 250.00
          </TableCell>
          <TableCell><Button>edit</Button></TableCell>
        </TableRow>
        <TableRow>
          <TableCell class="font-medium">
            P003
          </TableCell>
          <TableCell>Electrical</TableCell>
          <TableCell>56 pc</TableCell>
          <TableCell>19/10/2025</TableCell>
          <TableCell class="text-right">
            BD 250.00
          </TableCell>
          <TableCell><Button>edit</Button></TableCell>
        </TableRow>
        <TableRow>
          <TableCell class="font-medium">
            P002
          </TableCell>
          <TableCell>Home Appliance</TableCell>
          <TableCell>30 pc</TableCell>
          <TableCell>10/03/2025</TableCell>
          <TableCell class="text-right">
            BD 250.00
          </TableCell>
          <TableCell><Button>edit</Button></TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>

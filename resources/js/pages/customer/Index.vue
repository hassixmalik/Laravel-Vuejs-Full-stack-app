<script setup lang="ts">
import Alert from '@/components/ui/alert/Alert.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
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
import { Rocket, SquarePen, Dot } from 'lucide-vue-next';
const page = usePage()

interface Customer {
  id: number,
  name: string,
  email: string,
  phone: string,
  address: string,
  city: string,
  is_active: boolean,
}

interface Props {
  customers: Customer[]
}

//get props from inertia
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Customers',
    href: '/customer',
  },
];


</script>

<template>

  <Head title="Customers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <Link href="/customer/create"><Button>Add Customer</Button></Link>
    </div>
    <div v-if="page.props.flash?.message" class="alert p-2">
      <Alert class="bg-blue-200">
        <Rocket class="h-4 w-4" />
        <AlertTitle>Notification!</AlertTitle>
        <AlertDescription>
          {{ page.props.flash.message }}
        </AlertDescription>
      </Alert>
    </div>
    <Table>
      <TableCaption>Customers List.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[80px]">ID</TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Email</TableHead>
          <TableHead>Phone</TableHead>
          <!-- <TableHead>Address</TableHead> -->
          <TableHead>City</TableHead>
          <TableHead>Status</TableHead>
          <TableHead class="text-center">Actions</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow v-for="customer in props.customers" :key="customer.id">
          <TableCell class="font-medium">{{ customer.id }}</TableCell>
          <TableCell>{{ customer.name }}</TableCell>
          <TableCell>{{ customer.email }}</TableCell>
          <TableCell>{{ customer.phone }}</TableCell>
          <!-- <TableCell>{{ customer.address }}</TableCell> -->
          <TableCell>{{ customer.city ? customer.city : 'Not mentioned' }}</TableCell>
          <TableCell  :class="customer.is_active ? 'text-green-500' : 'text-red-500'"> {{ customer.is_active ? 'Active' : 'Disabled' }}</TableCell>
          <TableCell><Link :href="`/customer/${customer.id}/edit`"><Button class="bg-slate-600"><SquarePen /></Button></Link></TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>

<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket, SquarePen } from 'lucide-vue-next';

const page = usePage()

interface Product {
  id: number,
  name: string,
  price: number,
  qty: number,
  description: string,
}

interface Props {
  products: Product[]
}

//get props from Inertia
const props = defineProps<Props>();


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Inventory',
    href: '/inventory',
  },
];


</script>

<template>

  <Head title="Inventory" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <Link href="/inventory/create"><Button>Add Product</Button></Link>
    </div>
    <div v-if="page.props.flash?.message" class="alert p-2">
      <Alert class="dark:bg-neutral-700 dark:text-neutral-100 bg-blue-200">
        <Rocket class="h-4 w-4" />
        <AlertTitle>Notification!</AlertTitle>
        <AlertDescription>
          {{ page.props.flash.message }}
        </AlertDescription>
      </Alert>
    </div>
    <Table>
      <TableCaption>Products List.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">
            Product ID
          </TableHead>
          <TableHead>Name</TableHead>
          <TableHead>QTY</TableHead>
          <TableHead>
            Amount
          </TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="product in props.products" :key="product.id">
          <TableCell class="font-medium">
            {{ product.id }}
          </TableCell>
          <TableCell>{{ product.name }}</TableCell>
          <TableCell>{{ product.qty }}</TableCell>
          <TableCell>
            BD {{ product.price }}
          </TableCell>
          <TableCell>
            <Link :href="`/inventory/${product.id}/edit`"><Button><SquarePen /></Button></Link>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>

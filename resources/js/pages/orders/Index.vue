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
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { AppPageProps as AppPageProps } from '@/types';
import { SquarePen, FileInput, Rocket } from 'lucide-vue-next';
import Alert from '@/components/ui/alert/Alert.vue';
import AlertTitle from '@/components/ui/alert/AlertTitle.vue';
import AlertDescription from '@/components/ui/alert/AlertDescription.vue';

const convertForm = useForm({});

function convertOrder(orderId: number) {
  convertForm.post(`/orders/${orderId}/convert-to-invoice`)
}

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
    <div v-if="page.props.flash?.message" class="alert p-2">
      <Alert class="bg-blue-200 dark:bg-neutral-700 dark:text-neutral-100">
        <Rocket class="h-4 w-4" />
        <AlertTitle>Notification!</AlertTitle>
        <AlertDescription>
          {{ page.props.flash.message }}
        </AlertDescription>
      </Alert>
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
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger>
                  <Link :href="`/orders/${o.id}/edit`"><Button class="bg-slate-400 mx-1">
                    <SquarePen />
                  </Button></Link>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Edit Order details</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger>
                  <Dialog>
                    <DialogTrigger as-child>
                      <Button variant="outline">
                        <FileInput />
                      </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[425px]">
                      <DialogHeader>
                        <DialogTitle>Convert to Invoice</DialogTitle>
                        <DialogDescription>
                          By clicking 'convert', order will be converted into an invoice that can be sent to Customer
                          via
                          Email
                        </DialogDescription>
                      </DialogHeader>
                      <div>Do you want to convert order#{{ o.id }} into Invoice?</div>
                      <form @submit.prevent="convertOrder(o.id)">
                        <Button type="submit" :disabled="convertForm.processing">
                          <span v-if="convertForm.processing">Converting...</span>
                          <span v-else>Convert</span>
                        </Button>
                      </form>
                    </DialogContent>
                  </Dialog>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Convert to Invoice</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>


          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

  </AppLayout>
</template>

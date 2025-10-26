<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

import { useForm, usePage } from '@inertiajs/vue3'
import SearchableSelect from '@/components/SearchableSelect.vue'

// ✅ ADD: extend your app's PageProps (Option 1)
import type { AppPageProps as AppPageProps } from '@/types'

type CreatePageProps = AppPageProps & {
  customerOptions: Array<{ label: string, value: number }>
  productOptions: Array<{ label: string, value: number }>
  statusOptions: Array<{ label: string, value: string }>
  placedBy: number
  placedByName: string
  flash?: { message?: string }
}

const page = usePage<CreatePageProps>()

// ✅ ADD: type the form payload so TS knows placed_by is a number
const form = useForm<{
  customer_id: number | null
  status: 'draft' | 'placed' | 'cancelled' | 'fulfilled'
  placed_by: number
}>({
  customer_id: null,
  status: 'draft',
  placed_by: page.props.placedBy,   // from server
})

// purely UI demo for now (not submitted yet)
const ui = {
  product_id_preview: null as number | null,
}

function handleSubmit() {
  form.post('/orders')
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create',
    href: '/create',
  },
];
</script>

<template>

  <Head title="Create" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <Link href="/orders/">
      <Button>
        <ArrowLeft />
      </Button>
    </Link>
    <div class="space-y-4">
      <div v-if="page.props.flash?.message" class="rounded-md bg-blue-50 p-2 text-sm">
        {{ page.props.flash.message }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label class="mb-2">Customer</Label>
            <SearchableSelect v-model="form.customer_id" :options="page.props.customerOptions"
              placeholder="Select customer" />
            <div class="text-sm text-red-500" v-if="form.errors.customer_id">{{ form.errors.customer_id }}
            </div>
          </div>

          <div>
            <Label class="mb-2">Status</Label>
            <SearchableSelect v-model="form.status" :options="page.props.statusOptions"
              placeholder="Select status" />
            <div class="text-sm text-red-500" v-if="form.errors.status">{{ form.errors.status }}</div>
          </div>

          <div>
            <Label class="mb-2">Placed By</Label>
            <Input type="text" :value="page.props.placedByName" :placeholder="page.props.placedByName" disabled />
            <input type="hidden" v-model="form.placed_by" />
          </div>
        </div>

        <!-- Show product select now (preview only; not posted yet) -->
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label class="mb-2">Product (preview)</Label>
            <SearchableSelect v-model="ui.product_id_preview" :options="page.props.productOptions"
              placeholder="Select product" />
            <p class="text-xs text-gray-500 mt-1">
              We’ll add multi-item rows next. This is just to verify the searchable select works with real
              data.
            </p>
          </div>
        </div>

        <Button type="submit" :disabled="form.processing">Create Order</Button>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import SearchableSelect from '@/components/SearchableSelect.vue';

// ---- Types (Option 1: extend your app types) ----
import type { AppPageProps as AppPageProps } from '@/types';
import { computed } from 'vue';

type BoolOption = { label: string; value: boolean };
type StatusOption = { label: string; value: 'draft' | 'issued' | 'void' };
type OrderOption = { label: string; value: number };
type OrderSummary = {
    order_id: number;
    customer_name: string;
    placed_by_name: string;
    total: string;       // "123.000"
    items_count: number;
};

type CreatePageProps = AppPageProps & {
    orderOptions: OrderOption[];
    orderSummaries: Record<number, OrderSummary>;
    statusOptions: StatusOption[];
    vatOptions: BoolOption[];
    createdById: number;
    createdByName: string;
}

const page = usePage<CreatePageProps>();

const form = useForm<{
    order_id: number | null;
    discount: number | null;        // DECIMAL(10,3) — entered by user
    vat_enabled: boolean;           // yes/no toggle
    status: 'draft' | 'issued' | 'void';
    // server will derive: created_for_cust from the order, created_by_employe from auth
}>({
    order_id: null,
    discount: 0.000,
    vat_enabled: false,
    status: 'draft',
});

const selectedSummary = computed<OrderSummary | null>(() => {
    if (!form.order_id) return null;
    return page.props.orderSummaries[form.order_id] ?? null;
});

function handleSubmit() {
  form.post('/invoice')
}


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Invoices', href: '/invoice' },
    { title: 'Create', href: '/invoice/create' },
];
</script>

<template>

    <Head title="Create Invoice" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-6">
            <div class="mb-3">
                <Link href="/invoice"><Button>
                    <ArrowLeft />
                </Button></Link>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <Label class="mb-2">Order</Label>
                        <SearchableSelect v-model="form.order_id" :options="page.props.orderOptions"
                            placeholder="Select order" />
                        <div class="text-sm text-red-500" v-if="form.errors.order_id">{{ form.errors.order_id }}</div>
                    </div>

                    <div>
                        <Label class="mb-2">Discount</Label>
                        <Input type="number" step="0.001" v-model.number="form.discount" placeholder="0.000" />
                        <div class="text-sm text-red-500" v-if="form.errors.discount">{{ form.errors.discount }}</div>
                    </div>

                    <div>
                        <Label class="mb-2">VAT</Label>
                        <SearchableSelect v-model="form.vat_enabled" :options="page.props.vatOptions"
                            placeholder="VAT" />
                        <div class="text-sm text-red-500" v-if="form.errors.vat_enabled">{{ form.errors.vat_enabled }}
                        </div>
                    </div>

                    <div>
                        <Label class="mb-2">Status</Label>
                        <SearchableSelect v-model="form.status" :options="page.props.statusOptions"
                            placeholder="Select status" />
                        <div class="text-sm text-red-500" v-if="form.errors.status">{{ form.errors.status }}</div>
                    </div>

                    <div>
                        <Label class="mb-2">Created By</Label>
                        <Input type="text" :placeholder="page.props.createdByName" disabled />
                    </div>
                </div>

                <!-- Summary (shown only when order selected) -->
                <div v-if="selectedSummary" class="rounded-lg border p-4 bg-white space-y-1">
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Customer:</span>
                        {{ selectedSummary.customer_name }}
                    </div>
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Created By:</span>
                        {{ selectedSummary.placed_by_name }}
                    </div>
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Order Total:</span>
                        {{ selectedSummary.total }}
                    </div>
                    <div class="text-sm text-gray-700">
                        <span class="font-semibold">Total Items:</span>
                        {{ selectedSummary.items_count }}
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        (This is a preview from the selected order. Actual invoice totals are computed on save.)
                    </p>
                </div>

                <Button type="submit" :disabled="form.processing">Create Invoice</Button>
            </form>
        </div>
    </AppLayout>
</template>

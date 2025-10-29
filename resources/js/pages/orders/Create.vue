<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, X } from 'lucide-vue-next';
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import SearchableSelect from '@/components/SearchableSelect.vue'

import type { AppPageProps as AppPageProps } from '@/types'

type CreatePageProps = AppPageProps & {
    customerOptions: Array<{ label: string, value: number }>
    productOptions: Array<{ label: string, value: number, price: number, available: number }>
    statusOptions: Array<{ label: string, value: string }>
    placedBy: number
    placedByName: string
    flash?: { message?: string }
}

const page = usePage<CreatePageProps>()

// add to your existing <script setup> with the types you already defined
const form = useForm<{
    customer_id: number | null
    status: 'draft' | 'placed' | 'cancelled' | 'fulfilled'
    placed_by: number
    items: { product_id: number | null; qty: number; unit_total: number | null }[]
}>({
    customer_id: null,
    status: 'draft',
    placed_by: page.props.placedBy,
    items: [
        { product_id: null, qty: 1, unit_total: 0.000 },
    ],
})

function addItem() {
    form.items.push({ product_id: null, qty: 1, unit_total: 0.000 })
}

function removeItem(idx: number) {
    form.items.splice(idx, 1)
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

// get price for a selected product_id
function productPrice(productId: number | null): number {
    if (productId == null) return 0
    const opt = page.props.productOptions.find(o => o.value === productId)
    return opt ? Number(opt.price) : 0
}

// update one line's unit_total = price * qty (3 dp)
function updateLine(idx: number) {
    const it = form.items[idx]
    const price = productPrice(it.product_id)
    const total = Number(((price || 0) * (it.qty || 0)).toFixed(3))
    it.unit_total = total
}

// show “Total so far” on page
const computedTotal = computed(() =>
    Number(
        form.items.reduce((acc, it) => acc + Number(it.unit_total ?? 0), 0).toFixed(3)
    )
)

function productStock(productId: number | null): number {
    if (productId == null) return 0
    const opt = page.props.productOptions.find(o => o.value === productId)
    return opt ? Number(opt.available) : 0
}

// true if the line's requested qty exceeds stock
function lineExceedsStock(idx: number): boolean {
    const it = form.items[idx]
    if (!it.product_id) return false // no product selected yet
    return (Number(it.qty) || 0) > productStock(it.product_id)
}

// if ANY line is invalid, we’ll disable submit
const hasAnyStockError = computed(() =>
    form.items.some((_, idx) => lineExceedsStock(idx))
)

// also ensure you still meet basic requirements before enabling submit
const isFormInvalid = computed(() => {
    const missingCustomer = !form.customer_id
    const missingAnyProduct = form.items.some(it => !it.product_id)
    const anyBadQty = form.items.some(it => !it.qty || it.qty < 1)
    return missingCustomer || missingAnyProduct || anyBadQty || hasAnyStockError.value
})

</script>

<template>

    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-3">
            <Link href="/orders/"><Button>
                <ArrowLeft />
            </Button></Link>
            <div v-if="page.props.flash?.message" class="rounded-md bg-blue-50 p-2 text-sm my-2">
                {{ page.props.flash.message }}
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold">Items</h3>
                        <Button type="button" @click="addItem">Add Item</Button>
                    </div>

                    <div class="grid grid-cols-12 gap-3" v-for="(it, idx) in form.items" :key="idx">
                        <div class="col-span-6">
                            <Label class="mb-1">Product</Label>
                            <SearchableSelect v-model="it.product_id" :options="page.props.productOptions"
                                placeholder="Select product" @change="() => updateLine(idx)" />
                            <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.product_id`]">
                                {{ form.errors[`items.${idx}.product_id`] }}
                            </div>
                            <p class="text-xs text-gray-500 mt-1" v-if="it.product_id">
                                In stock: {{ productStock(it.product_id) }}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <Label class="mb-1">Qty</Label>
                            <Input type="number" min="1" v-model.number="it.qty" @input="() => updateLine(idx)" />
                            <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.qty`]">
                                {{ form.errors[`items.${idx}.qty`] }}
                            </div>
                            <p class="text-xs mt-1" :class="lineExceedsStock(idx) ? 'text-red-600' : 'text-gray-500'">
                                <template v-if="it.product_id">
                                    <template v-if="lineExceedsStock(idx)">
                                        Requested qty exceeds stock ({{ productStock(it.product_id) }}). Reduce
                                        quantity.
                                    </template>
                                    <template v-else>
                                        available
                                    </template>
                                </template>
                            </p>
                        </div>

                        <div class="col-span-3">
                            <Label class="mb-1">Price</Label>
                            <Input type="number" min="0" step="0.001" :value="Number(it.unit_total ?? 0).toFixed(3)"
                                placeholder="0.000" disabled />

                            <div class="text-xs text-red-500" v-if="form.errors[`items.${idx}.unit_total`]">
                                {{ form.errors[`items.${idx}.unit_total`] }}
                            </div>
                        </div>

                        <div class="col-span-1 flex items-center">
                            <Button type="button" class="bg-red-400" @click="removeItem(idx)">
                                <X />
                            </Button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-6 border-t pt-4">
                    <div class="text-sm text-gray-600">Total so far</div>
                    <div class="text-lg font-semibold tabular-nums">
                        {{ computedTotal.toFixed(3) }}
                    </div>
                </div>


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
                        <Input type="text" :value="page.props.placedByName" :placeholder="page.props.placedByName"
                            disabled />
                        <input type="hidden" v-model="form.placed_by" />
                    </div>
                </div>
                <Button type="submit" :disabled="form.processing || isFormInvalid" >Create Order</Button>
            </form>
        </div>
    </AppLayout>
</template>

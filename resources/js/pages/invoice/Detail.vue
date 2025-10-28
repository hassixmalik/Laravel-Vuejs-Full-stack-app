<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Printer, SquarePen } from 'lucide-vue-next';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: '/inventory',
    },
];

const invoice = {
    id: 1001,
    customer: {
        name: 'Ali Bin Saleh',
        email: 'ali.saleh@example.com',
        phone: '+966 55 123 4567',
        address: 'Riyadh, Saudi Arabia'
    },
    date: '2025-10-23',
    due_date: '2025-11-02',
    status: 'Paid',
    items: [
        { id: 1, description: 'Wireless Router', quantity: 2, price: 350 },
        { id: 2, description: 'Network Switch (8-Port)', quantity: 1, price: 500 },
        { id: 3, description: 'Ethernet Cable (Cat6 10m)', quantity: 3, price: 60 },
    ],
    tax_rate: 0.15
}

const subtotal = invoice.items.reduce((sum, item) => sum + item.price * item.quantity, 0)
const tax = subtotal * invoice.tax_rate
const total = subtotal + tax

</script>

<template>

    <Head title="Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-between items-center p-4">
            <div>
                <Link href="/invoice/">
                <Button>
                    <ArrowLeft />
                </Button>
                </Link>
            </div>

            <div class="space-x-2">
                <Link>
                <Button>
                    <Printer />
                </Button>
                </Link>
                <Link>
                <Button>
                    <SquarePen />
                </Button>
                </Link>
            </div>
        </div>

        <div class="flex justify-center">
            <div class="p-8 max-w-3xl w-full">
                <h1 class="text-3xl font-bold mb-4">Invoice #{{ invoice.id }}</h1>

                <div class="mb-6">
                    <p><strong>Date:</strong> {{ invoice.date }}</p>
                    <p><strong>Due Date:</strong> {{ invoice.due_date }}</p>
                    <p><strong>Status:</strong> {{ invoice.status }}</p>
                </div>

                <div class="mb-6">
                    <h2 class="text-xl font-semibold">Customer</h2>
                    <p>{{ invoice.customer.name }}</p>
                    <p>{{ invoice.customer.email }}</p>
                    <p>{{ invoice.customer.phone }}</p>
                    <p>{{ invoice.customer.address }}</p>
                </div>

                <table class="w-full border-collapse border text-left mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border p-2">Item</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Price</th>
                            <th class="border p-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="border p-2">{{ item.description }}</td>
                            <td class="border p-2">{{ item.quantity }}</td>
                            <td class="border p-2">{{ item.price.toFixed(2) }}</td>
                            <td class="border p-2">{{ (item.price * item.quantity).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-right">
                    <p>Subtotal: {{ subtotal.toFixed(2) }} BD</p>
                    <p>VAT ({{ (invoice.tax_rate * 100).toFixed(0) }}%): {{ tax.toFixed(2) }} BD</p>
                    <p class="font-bold text-lg">Total: {{ total.toFixed(2) }} BD</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

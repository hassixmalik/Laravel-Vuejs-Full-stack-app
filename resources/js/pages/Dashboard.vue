<script setup lang="ts">
import LineChart from '@/components/ui/chart-line/LineChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Warehouse, UserRoundSearch, Package, } from 'lucide-vue-next';

const data = [
    {
        month: "2025-01",
        "Orders Total": 12500.350,
        "Invoices Total": 11750.240,
    },
    {
        month: "2025-02",
        "Orders Total": 13800.420,
        "Invoices Total": 13100.390,
    },
    {
        month: "2025-03",
        "Orders Total": 14650.890,
        "Invoices Total": 14000.100,
    },
    {
        month: "2025-04",
        "Orders Total": 15000.000,
        "Invoices Total": 14950.550,
    },
    {
        month: "2025-05",
        "Orders Total": 13200.750,
        "Invoices Total": 12680.320,
    },
    {
        month: "2025-06",
        "Orders Total": 15800.120,
        "Invoices Total": 15500.870,
    },
    {
        month: "2025-07",
        "Orders Total": 16250.640,
        "Invoices Total": 16020.310,
    },
    {
        month: "2025-08",
        "Orders Total": 17100.900,
        "Invoices Total": 16990.720,
    },
    {
        month: "2025-09",
        "Orders Total": 16500.200,
        "Invoices Total": 16300.650,
    },
    {
        month: "2025-10",
        "Orders Total": 17800.560,
        "Invoices Total": 17590.880,
    },
]
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1>Find Useful Links</h1>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <a href="/inventory" class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <Warehouse class="mb-2" />
                        <div class="text-lg">
                            Manage Inventory
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Manage and add your products in inventory.
                        </div>
                    </div>
                </a>
                <a href="/customer" 
                    class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <UserRoundSearch class="mb-2" />
                        <div class="text-lg">
                            Customers
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Add customers, Disabled old customers or create new.
                        </div>
                    </div>
                </a>
                <a href="/orders" 
                    class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <Package class="mb-2" />
                        <div class="text-lg">
                            Orders
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Create orders for customers and Manage them.
                        </div>
                    </div>
                </a>
            </div>
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow">
                <LineChart :data="data" index="month" :categories="['Orders Total', 'Invoices Total']"
                    :colors="['#2563EB', '#10B981']"
                    :y-formatter="(tick) => {
                    return typeof tick === 'number'
                    ? `BD ${new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 3,
                    maximumFractionDigits: 3
                    }).format(tick)}`
                    : ''
                    }"
                    />
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
/* Ensure text contrast and visibility */
:deep(.recharts-cartesian-axis-tick-value) {
  fill: #111827 !important; /* dark text */
}
:deep(.recharts-legend-item-text) {
  fill: #111827 !important;
}
.dark :deep(.recharts-cartesian-axis-tick-value),
.dark :deep(.recharts-legend-item-text) {
  fill: #f9fafb !important; /* light text in dark mode */
}
</style>

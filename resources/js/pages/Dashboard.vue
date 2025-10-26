<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { BookOpen, LayoutGrid, Warehouse, Newspaper, UserRoundSearch, Package, PackageOpen, Link} from 'lucide-vue-next';



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
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <h1>Find Useful Links</h1>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <a class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <Warehouse class="mb-2"/>
                        <div class="text-lg">
                            Manage Inventory
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Manage and add your products in inventory.
                        </div>
                    </div>
                </a>
                <div class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <UserRoundSearch class="mb-2"/>
                        <div class="text-lg">
                            Customers
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Add customers, Disabled old customers or create new.
                        </div>
                    </div>
                </div>
                <div class="hover:bg-neutral-100 cursor-pointer relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div class="p-2">
                        <Package class="mb-2"/>
                        <div class="text-lg">
                            Orders
                        </div>
                        <div class="text-[#706f6c] text-sm">
                            Create orders for customers and Manage them.
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

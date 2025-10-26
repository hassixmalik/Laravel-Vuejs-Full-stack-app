<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    is_active: '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create',
        href: '/create',
    },
];

const handleSubmit = () => {
    form.post('/customer');
}
</script>

<template>

    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-3">
                <Link href="/customer/"><Button>
                    <ArrowLeft></ArrowLeft>
                </Button></Link>
            </div>
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="mb-2" for="name">Name</Label>
                        <Input type="text" v-model="form.name" placeholder="Customer name" />
                        <div class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <Label class="mb-2" for="email">Email</Label>
                        <Input type="email" v-model="form.email" placeholder="Email address" />
                        <div class="text-sm text-red-500" v-if="form.errors.email">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <Label class="mb-2" for="phone">Phone</Label>
                        <Input type="text" v-model="form.phone" placeholder="Phone number" />
                        <div class="text-sm text-red-500" v-if="form.errors.phone">{{ form.errors.phone }}</div>
                    </div>

                    <div>
                        <Label class="mb-2" for="city">City</Label>
                        <Input type="text" v-model="form.city" placeholder="City (optional)" />
                        <div class="text-sm text-red-500" v-if="form.errors.city">{{ form.errors.city }}</div>
                    </div>

                    <div class="col-span-2">
                        <Label class="mb-2" for="address">Address</Label>
                        <Textarea v-model="form.address" placeholder="Customer address (optional)" />
                        <div class="text-sm text-red-500" v-if="form.errors.address">{{ form.errors.address }}</div>
                    </div>

                    <div>
                        <Label class="mb-2" for="is_active">Status</Label>
                        <Select v-model="form.is_active">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Status</SelectLabel>
                                    <SelectItem :value="true">Active</SelectItem>
                                    <SelectItem :value="false">Inactive</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <div class="text-sm text-red-500" v-if="form.errors.is_active">
                            {{ form.errors.is_active }}
                        </div>

                        <div class="text-sm text-red-500" v-if="form.errors.is_active">{{ form.errors.is_active }}</div>
                    </div>
                </div>
                <Button type="submit" :disabled="form.processing" class="mt-4">Add Customer</Button>
            </form>

        </div>
    </AppLayout>
</template>

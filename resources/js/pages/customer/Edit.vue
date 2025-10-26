<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3'
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';


interface Customer {
    id: number,
    name: string,
    email: string,
    phone: string,
    address: string,
    city: string,
    is_active: string,
}

const props = defineProps<{ customer: Customer }>();

const form = useForm({
    name: props.customer.name,
    email: props.customer.email,
    phone: props.customer.phone,
    address: props.customer.address,
    city: props.customer.city,
    is_active: props.customer.is_active,
})

const handleSubmit = () => {
    form.put(`/customer/${props.customer.id}`);
}

const handleDelete = (id: number) => {
    router.delete(`/customer/${id}`);
}

</script>

<template>

    <Head title="Create" />

    <AppLayout :breadcrumbs="[{ title: 'Edit Customer', href: `customer/${props.customer.id}/edit`, },]">
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
                    </div>
                </div>
                <Button type="submit" :disabled="form.processing" class="mt-4">Edit Customer</Button>
            </form>
            <div
                class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p class="font-medium">Warning</p>
                    <p class="text-sm">
                        Please proceed with caution, this cannot be undone.
                    </p>
                </div>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive">Disable Customer</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to disable this Customer?</DialogTitle>
                            <DialogDescription>
                                Once your customer is disabled, you will not be able to create orders or invoices for
                                this customer.
                            </DialogDescription>
                            <Button @click="handleDelete(props.customer.id)" class="bg-red-400">Disable</Button>
                        </DialogHeader>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>

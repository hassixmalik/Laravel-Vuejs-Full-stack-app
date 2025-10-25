<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

interface Product {
    id: number, name: string, price: number, qty: number, description: string,
}

const props = defineProps<{ product: Product }>();

const form = useForm({
    name: props.product.name,
    price: props.product.price,
    qty: props.product.qty,
    description: props.product.description,
})

const handleSubmit = () => {
    form.put(`/inventory/${props.product.id}`);
}
</script>

<template>

    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="[{ title: 'Edit the Product Details ', href: `inventory/${props.product.id}/edit`, },]">
        <div class="p-4">
            <div class="mb-3">
                <Link href="/inventory/"><Button>
                    <ArrowLeft></ArrowLeft>
                </Button></Link>
            </div>
            <form @submit.prevent="handleSubmit">
                <div>
                    <div class="mb-3">
                        <Label class="mb-2" for="Product name">Product Name</Label>
                        <Input type="text" v-model="form.name" placeholder="Name"></Input>
                        <div class="text-sm text-red-500" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>
                    <div class="mb-3">
                        <Label class="mb-2" for="Product price">Price</Label>
                        <Input type="number" step=".01" v-model="form.price" placeholder="Price"></Input>
                        <div class="text-sm text-red-500" v-if="form.errors.price">{{ form.errors.price }}</div>
                    </div>
                    <div class="mb-3">
                        <Label class="mb-2" for="Product qty">QTY</Label>
                        <Input type="number" v-model="form.qty" placeholder="Quantity"></Input>
                        <div class="text-sm text-red-500" v-if="form.errors.qty">{{ form.errors.qty }}</div>
                    </div>
                    <div class="mb-3">
                        <Label class="mb-2" for="Product description">Description</Label>
                        <Textarea v-model="form.description" placeholder="Description of the Product"></Textarea>
                    </div>
                    <Button type="submit" :disabled="form.processing">Edit Product</Button>
                </div>
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
                        <Button variant="destructive">Delete account</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to delete this product</DialogTitle>
                            <DialogDescription>
                                Once your product is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your password to confirm
                                you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </DialogHeader>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </AppLayout>
</template>

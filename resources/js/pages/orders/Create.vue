<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next';


const form = useForm({
  name: '',
  price: '',
  description: '',
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create',
        href: '/create',
    },
];

const handleSubmit = () => {
    form.post('/orders');
}
</script>

<template>
    <Head title="Create" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-3">
                <Link href="/orders/"><Button><ArrowLeft></ArrowLeft></Button></Link>
            </div>
            <div class="mb-2">
                <h2>Create an order</h2>
            </div>
            <form @submit.prevent="handleSubmit">
                <div>
                    <div class="mb-3">
                        <Label  class="mb-2" for="Product name">Product Name</Label>
                        <Input type="text" v-model="form.name" placeholder="Name"></Input>
                        <div class="text-sm text-red" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>
                    <div class="mb-3">
                        <Label class="mb-2" for="Product price">Price</Label>
                        <Input type="number" step=".01" v-model="form.price" placeholder="Price"></Input>
                        <div class="text-sm text-red" v-if="form.errors.price">{{ form.errors.price }}</div>
                    </div>
                    <div class="mb-3">
                        <Label  class="mb-2" for="Product description">Description</Label>
                        <Textarea v-model="form.description" placeholder="Description of the Product"></Textarea>
                    </div>
                    <Button type="submit">Add Product</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

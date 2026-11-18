<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({ layout: { title: 'Verify your email', description: 'Enter the six-digit code we sent to your inbox.' } });
defineProps<{ email?: string }>();
const page = usePage();
</script>

<template>
    <Head title="Verify email" />
    <div v-if="page.props.flash?.status" class="mb-4 rounded-lg bg-emerald-500/10 p-3 text-sm text-emerald-700 dark:text-emerald-300">{{ page.props.flash.status }}</div>
    <p class="mb-5 text-sm text-muted-foreground">Your school workspace is ready. Verify {{ email || 'your email address' }} to activate the seven-day trial and open your dashboard.</p>
    <Form action="/register/verify" method="post" v-slot="{ errors, processing }" class="space-y-4">
        <div class="grid gap-2"><Label for="code">Email verification code</Label><Input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" autocomplete="one-time-code" required autofocus placeholder="000000" /><InputError :message="errors.code" /></div>
        <Button type="submit" class="w-full" :disabled="processing"><Spinner v-if="processing" />Verify and open dashboard</Button>
    </Form>
    <Form action="/register/resend" method="post" class="mt-4 text-center"><button class="text-sm text-primary underline-offset-4 hover:underline">Resend code</button></Form>
</template>

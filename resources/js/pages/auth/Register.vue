<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({ layout: { title: 'Start your school trial', description: 'Create your school workspace and start a seven-day trial.' } });
</script>

<template>
    <Head title="Create school account" />
    <Form action="/register" method="post" v-slot="{ errors, processing }" class="flex flex-col gap-5">
        <div class="grid gap-2"><Label for="school_name">School name</Label><Input id="school_name" name="school_name" required maxlength="191" autocomplete="organization" placeholder="Your school or academy" /><InputError :message="errors.school_name" /></div>
        <div class="grid gap-2"><Label for="name">Your name</Label><Input id="name" name="name" required maxlength="191" autocomplete="name" placeholder="School owner or administrator" /><InputError :message="errors.name" /></div>
        <div class="grid gap-2"><Label for="email">Work email</Label><Input id="email" name="email" type="email" required autocomplete="email" placeholder="you@school.edu" /><InputError :message="errors.email" /></div>
        <div class="grid gap-2"><Label for="password">Password</Label><PasswordInput id="password" name="password" required minlength="12" autocomplete="new-password" placeholder="At least 12 characters" /><InputError :message="errors.password" /></div>
        <div class="grid gap-2"><Label for="password_confirmation">Confirm password</Label><PasswordInput id="password_confirmation" name="password_confirmation" required autocomplete="new-password" placeholder="Enter it again" /></div>
        <label class="flex items-start gap-3 text-sm text-muted-foreground"><input type="checkbox" name="terms" value="1" required class="mt-1"><span>I agree to the terms and privacy policy.</span></label><InputError :message="errors.terms" />
        <Button type="submit" class="w-full" :disabled="processing"><Spinner v-if="processing" />Create workspace and start 7-day trial</Button>
        <p class="text-center text-sm text-muted-foreground">Already registered? <TextLink href="/login">Sign in</TextLink></p>
    </Form>
</template>

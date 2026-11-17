<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Verify your email',
        description: 'Open the secure verification link sent to your school email address.',
    },
});

defineProps<{
    status?: string;
    email?: string;
}>();
</script>

<template>
    <Head title="Verify your email" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 rounded-md border border-green-600/20 bg-green-600/10 p-3 text-center text-sm font-medium text-green-700 dark:text-green-400"
        role="status"
    >
        A new verification link has been sent to your email address.
    </div>

    <div class="space-y-4 text-center text-sm text-muted-foreground">
        <p>
            Verify your email address<span v-if="email"> ({{ email }})</span>
            to continue. For an offline school installation, contact your school
            administrator for account verification.
        </p>

        <Form v-bind="send.form()" v-slot="{ processing }">
            <Button class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Resend verification email
            </Button>
        </Form>

        <TextLink :href="logout()" as="button" class="mx-auto block">
            Log out
        </TextLink>
    </div>
</template>

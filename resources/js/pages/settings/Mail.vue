<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'School email settings',
        description: 'Configure email delivery for this school workspace.',
    },
});

defineProps<{
    settings: {
        host: string;
        port: number;
        scheme: 'smtp' | 'smtps';
        username: string;
        from_email: string;
        from_name: string;
    };
    passwordConfigured: boolean;
    lastTestedAt?: string | null;
    lastTestStatus?: 'success' | 'failed' | null;
}>();

const page = usePage();
</script>

<template>
    <Head title="School email settings" />

    <div class="space-y-6">
        <Heading
            title="School email"
            description="Each school's email configuration is isolated from every other workspace."
        />

        <div v-if="page.props.flash?.status" class="rounded-md border border-green-600/20 bg-green-600/10 p-3 text-sm text-green-700 dark:text-green-400" role="status">
            {{ page.props.flash.status }}
        </div>

        <div class="rounded-lg border bg-card p-4 text-sm text-muted-foreground">
            These settings are used for school invitations, email verification,
            password reset links, and school notifications. Credentials are
            encrypted and never shown again after saving. The password field is
            optional when updating; leave it blank to keep the saved password.
        </div>

        <Form action="/settings/mail" method="put" v-slot="{ errors, processing }" class="space-y-5">
            <div class="grid gap-2">
                <Label for="host">SMTP host</Label>
                <Input id="host" name="host" :model-value="settings.host" required autocomplete="off" placeholder="smtp.example.com" />
                <InputError :message="errors.host" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="port">Port</Label>
                    <Input id="port" name="port" type="number" :model-value="settings.port" required min="1" max="65535" />
                    <InputError :message="errors.port" />
                </div>
                <div class="grid gap-2">
                    <Label for="scheme">Connection security</Label>
                    <select id="scheme" name="scheme" class="h-10 rounded-md border border-input bg-background px-3 text-sm" required>
                        <option value="smtp" :selected="settings.scheme === 'smtp'">SMTP with STARTTLS</option>
                        <option value="smtps" :selected="settings.scheme === 'smtps'">Implicit TLS (SMTPS)</option>
                    </select>
                    <InputError :message="errors.scheme" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="username">SMTP username</Label>
                <Input id="username" name="username" :model-value="settings.username" autocomplete="off" placeholder="Optional" />
                <InputError :message="errors.username" />
            </div>

            <div class="grid gap-2">
                <Label for="password">SMTP password</Label>
                <Input id="password" name="password" type="password" autocomplete="new-password" :placeholder="passwordConfigured ? 'Saved securely — leave blank to keep it' : 'Enter SMTP password or app password'" />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="from_email">From email</Label>
                    <Input id="from_email" name="from_email" type="email" :model-value="settings.from_email" required autocomplete="email" />
                    <InputError :message="errors.from_email" />
                </div>
                <div class="grid gap-2">
                    <Label for="from_name">From name</Label>
                    <Input id="from_name" name="from_name" :model-value="settings.from_name" required autocomplete="organization" />
                    <InputError :message="errors.from_name" />
                </div>
            </div>

            <InputError :message="errors.smtp" />

            <div class="flex flex-wrap items-center gap-3">
                <Button type="submit" :disabled="processing">
                    <Spinner v-if="processing" />
                    Save email settings
                </Button>
                <span v-if="lastTestStatus === 'success'" class="text-sm text-green-700 dark:text-green-400">Last test succeeded</span>
                <span v-else-if="lastTestStatus === 'failed'" class="text-sm text-destructive">Last test failed</span>
            </div>
        </Form>

        <Form action="/settings/mail/test" method="post" v-slot="{ processing }" class="border-t pt-5">
            <p v-if="lastTestedAt" class="mb-3 text-xs text-muted-foreground">Last tested {{ new Date(lastTestedAt).toLocaleString() }}</p>
            <Button type="submit" variant="outline" :disabled="processing">
                <Spinner v-if="processing" />
                Send test email to my account
            </Button>
        </Form>
    </div>
</template>

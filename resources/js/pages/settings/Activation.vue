<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'School activation',
        description: 'View this school workspace’s activation status.',
    },
});

defineProps<{
    school: { name: string; code: string };
    deploymentMode: 'hosted' | 'local';
    license: {
        plan: string;
        features: string[];
        expiresAt: string | null;
        active: boolean;
    } | null;
    latestRequest: {
        requestId: string;
        status: string;
        createdAt: string | null;
    } | null;
    activationConfigured: boolean;
    smtpConfigured: boolean;
}>();

const page = usePage();
</script>

<template>
    <Head title="School activation" />

    <div class="space-y-6">
        <Heading
            title="School activation"
            description="Activation is specific to this school installation."
        />

        <div v-if="page.props.flash?.status" class="rounded-md border border-green-600/20 bg-green-600/10 p-3 text-sm text-green-700 dark:text-green-400" role="status">
            {{ page.props.flash.status }}
        </div>

        <section class="space-y-3 rounded-lg border p-5">
            <h2 class="font-medium">{{ school.name }}</h2>
            <p class="text-sm text-muted-foreground">Workspace code: <span class="font-mono">{{ school.code }}</span></p>
            <p class="text-sm text-muted-foreground">Deployment: {{ deploymentMode === 'local' ? 'Local installation' : 'Hosted SaaS' }}</p>

            <template v-if="deploymentMode === 'hosted'">
                <p class="text-sm">Hosted school access and plan entitlements are managed by the SaaS platform. Manual offline license keys are not used in hosted mode.</p>
            </template>
            <template v-else>
                <div v-if="license?.active" class="space-y-1 rounded-md border border-green-600/20 bg-green-600/10 p-3 text-sm">
                    <p class="font-medium text-green-700 dark:text-green-400">Installation activated</p>
                    <p>Plan: {{ license.plan }}</p>
                    <p v-if="license.expiresAt">Expires: {{ new Date(license.expiresAt).toLocaleDateString() }}</p>
                    <p v-else>License: no expiry</p>
                </div>
                <p v-else class="text-sm text-muted-foreground">This installation can continue working offline after activation. The signed key is tied to this installation ID and can be used once.</p>
            </template>
        </section>

        <div v-if="latestRequest" class="rounded-lg border p-4 text-sm">
            <p class="font-medium">Latest activation request: {{ latestRequest.status }}</p>
            <p class="mt-1 break-all font-mono text-xs text-muted-foreground">{{ latestRequest.requestId }}</p>
        </div>

        <template v-if="deploymentMode === 'local'">
            <section v-if="!license?.active" class="space-y-4 rounded-lg border p-5">
                <h2 class="font-medium">Request a license key</h2>
                <p class="text-sm text-muted-foreground">
                    The app emails your school name, workspace code, installation ID, request ID, and administrator email to the software provider. It does not send student data or passwords.
                </p>
                <p v-if="!smtpConfigured" class="text-sm text-amber-700 dark:text-amber-400">Configure and test this school’s SMTP settings first.</p>
                <p v-else-if="!activationConfigured" class="text-sm text-amber-700 dark:text-amber-400">The software provider’s activation email is not configured for this installation.</p>
                <Form action="/settings/activation/request" method="post" v-slot="{ errors, processing }" class="space-y-3">
                    <InputError :message="errors.activation" />
                    <Button type="submit" :disabled="processing || !smtpConfigured || !activationConfigured">
                        <Spinner v-if="processing" />
                        Request activation key
                    </Button>
                </Form>
            </section>

            <section v-if="!license?.active" class="space-y-4 rounded-lg border p-5">
                <h2 class="font-medium">Activate this installation</h2>
                <p class="text-sm text-muted-foreground">Paste the signed license key provided by your software provider. Keep the key private and do not post it publicly.</p>
                <Form action="/settings/activation" method="post" v-slot="{ errors, processing }" class="space-y-3">
                    <label for="license_key" class="text-sm font-medium">License key</label>
                    <textarea id="license_key" name="license_key" required rows="5" autocomplete="off" spellcheck="false" class="w-full rounded-md border border-input bg-background px-3 py-2 font-mono text-xs" placeholder="Paste the signed key here"></textarea>
                    <InputError :message="errors.license_key" />
                    <Button type="submit" :disabled="processing">
                        <Spinner v-if="processing" />
                        Activate installation
                    </Button>
                </Form>
            </section>
        </template>
    </div>
</template>

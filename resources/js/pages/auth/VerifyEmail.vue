<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Email verification',
        description:
            'Dev mode dummy email system is active. Enter the OTP shown below or use 1-click verification.',
    },
});

const props = defineProps<{
    status?: string;
    otp?: string;
    verificationUrl?: string;
}>();

const code = ref(props.otp ?? '');

const otpForm = useForm({
    otp: props.otp ?? '',
});

const handleOtpSubmit = () => {
    otpForm.otp = code.value;
    otpForm.post('/email/verify-otp');
};

const autofillAndVerify = () => {
    if (props.otp) {
        code.value = props.otp;
        otpForm.otp = props.otp;
        otpForm.post('/email/verify-otp');
    }
};
</script>

<template>
    <Head title="Email verification" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400"
    >
        A new dummy verification OTP has been generated.
    </div>

    <!-- Dev Mode OTP Display Banner -->
    <div
        v-if="otp"
        class="mb-6 rounded-lg border border-dashed border-amber-500/40 bg-amber-500/10 p-4 text-center dark:bg-amber-950/20"
    >
        <span class="inline-block rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-semibold tracking-wider uppercase text-amber-700 dark:text-amber-300">
            Dev Mode Dummy OTP
        </span>
        <div class="my-2 select-all font-mono text-3xl font-extrabold tracking-[0.25em] text-foreground">
            {{ otp }}
        </div>
        <p class="text-xs text-muted-foreground">
            Use this OTP code below or click the auto-verify button.
        </p>
    </div>

    <!-- OTP Input Form -->
    <form @submit.prevent="handleOtpSubmit" class="space-y-4">
        <div class="flex flex-col items-center justify-center space-y-3 text-center">
            <InputOTP
                id="otp"
                v-model="code"
                :maxlength="6"
                :disabled="otpForm.processing"
                autofocus
            >
                <InputOTPGroup>
                    <InputOTPSlot
                        v-for="index in 6"
                        :key="index"
                        :index="index - 1"
                    />
                </InputOTPGroup>
            </InputOTP>
            <InputError :message="otpForm.errors.otp" />
        </div>

        <Button
            type="submit"
            class="w-full"
            :disabled="otpForm.processing || code.length !== 6"
        >
            <Spinner v-if="otpForm.processing" />
            Verify with OTP
        </Button>
    </form>

    <div class="my-6 grid grid-cols-2 gap-3 border-t pt-4">
        <Button
            v-if="otp"
            type="button"
            variant="outline"
            class="w-full text-xs"
            :disabled="otpForm.processing"
            @click="autofillAndVerify"
        >
            Auto-fill & Verify
        </Button>

        <a
            v-if="verificationUrl"
            :href="verificationUrl"
            class="w-full inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-2 text-xs font-medium shadow-sm hover:bg-accent hover:text-accent-foreground"
        >
            1-Click Link Verify
        </a>
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-4 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="ghost" size="sm" class="w-full text-muted-foreground hover:text-foreground">
            <Spinner v-if="processing" />
            Generate new OTP
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            Log out
        </TextLink>
    </Form>
</template>

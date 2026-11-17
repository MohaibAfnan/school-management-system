<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound, ShieldCheck } from '@lucide/vue';
import InputError from '@/components/InputError.vue';

defineOptions({ layout: null });
</script>

<template>
    <Head title="Developer sign in" />
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-5 py-12 text-white">
        <div class="absolute -left-32 top-[-8rem] size-[30rem] rounded-full bg-cyan-500/10 blur-3xl" />
        <div class="absolute -right-32 bottom-[-10rem] size-[32rem] rounded-full bg-blue-500/10 blur-3xl" />
        <section class="relative w-full max-w-md rounded-3xl border border-white/10 bg-slate-900/80 p-7 shadow-2xl shadow-black/40 backdrop-blur sm:p-9">
            <a href="/" class="inline-flex items-center gap-3">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-cyan-300 text-slate-950"><ShieldCheck class="size-6" /></span>
                <span><span class="block text-xs uppercase tracking-[0.16em] text-slate-400">School platform</span><span class="mt-0.5 block font-semibold">Developer console</span></span>
            </a>
            <div class="mt-9">
                <h1 class="text-2xl font-semibold tracking-tight">Sign in to manage schools</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">This portal is for authorized platform administrators.</p>
            </div>
            <Form action="/developer/login" method="post" v-slot="{ errors, processing }" class="mt-7 space-y-5">
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-200">Administrator email</label>
                    <input id="email" name="email" type="email" required autocomplete="username" autofocus class="h-11 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3.5 text-sm outline-none transition placeholder:text-slate-600 focus:border-cyan-300/70 focus:ring-2 focus:ring-cyan-300/10" placeholder="you@example.com">
                    <InputError :message="errors.email" />
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-200">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="h-11 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3.5 text-sm outline-none transition placeholder:text-slate-600 focus:border-cyan-300/70 focus:ring-2 focus:ring-cyan-300/10" placeholder="Enter your password">
                    <InputError :message="errors.password" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-400"><input type="checkbox" name="remember" value="1" class="size-4 rounded border-white/20 bg-slate-950 text-cyan-300"> Keep me signed in</label>
                <button type="submit" :disabled="processing" class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-cyan-300 px-4 text-sm font-bold text-slate-950 transition hover:bg-cyan-200 disabled:opacity-60"><KeyRound class="size-4" /> {{ processing ? 'Signing in…' : 'Sign in securely' }}</button>
            </Form>
            <p class="mt-6 border-t border-white/10 pt-5 text-xs leading-5 text-slate-500">Platform administrator accounts are created from the server command line with <code class="text-slate-300">php artisan platform:admin:create</code>. There is no public registration.</p>
        </section>
    </main>
</template>

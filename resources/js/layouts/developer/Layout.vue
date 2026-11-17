<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Blocks, Building2, LayoutDashboard, LogOut, ShieldCheck } from '@lucide/vue';

const page = usePage();
const navigation = [
    { label: 'Overview', href: '/developer', icon: LayoutDashboard },
    { label: 'Schools', href: '/developer/schools', icon: Building2 },
    { label: 'Packages', href: '/developer/plans', icon: Blocks },
];
</script>

<template>
    <Head title="Developer portal" />
    <div class="min-h-screen bg-slate-950 text-slate-100 lg:grid lg:grid-cols-[250px_1fr]">
        <aside class="border-b border-white/10 bg-slate-900/70 px-5 py-5 lg:flex lg:min-h-screen lg:flex-col lg:border-r lg:border-b-0 lg:px-4">
            <a href="/developer" class="flex items-center gap-3 px-2">
                <span class="flex size-10 items-center justify-center rounded-xl bg-cyan-300 text-slate-950"><ShieldCheck class="size-5" /></span>
                <span><span class="block text-sm font-semibold">School Platform</span><span class="block text-xs text-slate-400">Developer console</span></span>
            </a>
            <nav class="mt-8 flex gap-2 overflow-x-auto lg:flex-col">
                <Link v-for="item in navigation" :key="item.href" :href="item.href" class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition" :class="page.url === item.href || (item.href !== '/developer' && page.url.startsWith(item.href)) ? 'bg-cyan-300/10 text-cyan-200' : 'text-slate-400 hover:bg-white/5 hover:text-white'">
                    <component :is="item.icon" class="size-4" /> {{ item.label }}
                </Link>
            </nav>
            <div class="mt-auto hidden rounded-2xl border border-white/10 bg-white/[0.03] p-4 text-xs leading-5 text-slate-400 lg:block">
                Platform actions are recorded in the audit history. School records remain isolated by workspace.
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex items-center justify-between border-b border-white/10 px-5 py-4 sm:px-8">
                <div><p class="text-xs text-slate-500">Platform administration</p><p class="mt-0.5 text-sm font-medium">Developer workspace</p></div>
                <Form action="/developer/logout" method="post">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-white/10 px-3 py-2 text-sm text-slate-300 transition hover:bg-white/5 hover:text-white"><LogOut class="size-4" /> Sign out</button>
                </Form>
            </header>
            <main class="mx-auto max-w-7xl px-5 py-7 sm:px-8 sm:py-9"><slot /></main>
        </div>
    </div>
</template>

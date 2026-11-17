<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Blocks, Building2, ShieldCheck, Users } from '@lucide/vue';

defineOptions({ layout: { title: 'Developer overview' } });

defineProps<{
    stats: {
        schools: number;
        activeSchools: number;
        disabledSchools: number;
        students: number;
        plans: number;
        subscriptions: number;
    };
    recentActivity: Array<{
        id: number;
        platform_admin_email: string;
        school_id: number | null;
        school?: { name: string; code: string } | null;
        action: string;
        target_type: string | null;
        target_id: number | null;
        created_at: string;
    }>;
}>();

const cards = (stats: { schools: number; activeSchools: number; disabledSchools: number; students: number; plans: number; subscriptions: number }) => [
    { label: 'Schools', value: stats.schools, note: `${stats.activeSchools} active · ${stats.disabledSchools} disabled`, icon: Building2, color: 'text-cyan-200 bg-cyan-300/10' },
    { label: 'Students', value: stats.students, note: 'Across all workspaces', icon: Users, color: 'text-violet-200 bg-violet-300/10' },
    { label: 'Packages', value: stats.plans, note: `${stats.subscriptions} active or trialing`, icon: Blocks, color: 'text-amber-200 bg-amber-300/10' },
];
</script>

<template>
    <Head title="Developer overview" />
    <section>
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-medium text-cyan-200">Platform control center</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Good to see you.</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">Manage school workspaces, client access, subscription packages, and account recovery from one place.</p>
            </div>
            <Link href="/developer/schools" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-300 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-200">Manage schools <ArrowRight class="size-4" /></Link>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <article v-for="card in cards(stats)" :key="card.label" class="rounded-2xl border border-white/10 bg-slate-900/65 p-5 shadow-lg shadow-black/10">
                <div class="flex items-start justify-between">
                    <div><p class="text-sm text-slate-400">{{ card.label }}</p><p class="mt-3 text-3xl font-semibold tracking-tight text-white">{{ card.value.toLocaleString() }}</p></div>
                    <span class="flex size-10 items-center justify-center rounded-xl" :class="card.color"><component :is="card.icon" class="size-5" /></span>
                </div>
                <p class="mt-3 text-xs text-slate-500">{{ card.note }}</p>
            </article>
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1.4fr_0.8fr]">
            <section class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/65">
                <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                    <div><h2 class="font-semibold text-white">Recent platform activity</h2><p class="mt-1 text-xs text-slate-500">Administrative changes with time and actor</p></div>
                    <ShieldCheck class="size-5 text-cyan-200" />
                </div>
                <div v-if="recentActivity.length" class="divide-y divide-white/5">
                    <div v-for="event in recentActivity" :key="event.id" class="flex flex-col justify-between gap-2 px-5 py-4 sm:flex-row sm:items-center">
                        <div><p class="text-sm font-medium text-slate-200">{{ event.action.replaceAll('.', ' ') }}</p><p class="mt-1 text-xs text-slate-500">{{ event.school?.name ?? 'Platform' }} · {{ event.platform_admin_email }}</p></div>
                        <time class="text-xs text-slate-500">{{ new Date(event.created_at).toLocaleString() }}</time>
                    </div>
                </div>
                <div v-else class="px-5 py-12 text-center"><p class="text-sm font-medium text-slate-300">No activity yet</p><p class="mt-1 text-xs text-slate-500">Changes made in this portal will appear here.</p></div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-gradient-to-br from-cyan-300/10 to-slate-900/80 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-200">Quick actions</p>
                <h2 class="mt-3 text-xl font-semibold text-white">Keep client operations moving.</h2>
                <p class="mt-2 text-sm leading-6 text-slate-400">Create a workspace, update package access, or help a school administrator recover account access.</p>
                <div class="mt-6 space-y-2">
                    <Link href="/developer/schools" class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-200 transition hover:bg-slate-950/70">Create or update a school <ArrowRight class="size-4 text-cyan-200" /></Link>
                    <Link href="/developer/plans" class="flex items-center justify-between rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm text-slate-200 transition hover:bg-slate-950/70">Manage packages <ArrowRight class="size-4 text-cyan-200" /></Link>
                </div>
            </section>
        </div>
    </section>
</template>

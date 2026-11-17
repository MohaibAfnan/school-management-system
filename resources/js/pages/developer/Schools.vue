<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { Archive, Building2, KeyRound, Plus, RotateCcw, Save, ShieldCheck, UserRoundCog } from '@lucide/vue';

defineOptions({ layout: { title: 'School workspaces' } });

type SchoolRecord = {
    id: number;
    name: string;
    slug: string;
    code: string;
    status: string;
    deploymentMode: string;
    createdAt: string;
    archived: boolean;
    admin: { id: number; name: string; email: string } | null;
    subscription: { status: string; planId: number | null; planName: string | null; trialEndsAt: string | null; endsAt: string | null } | null;
};

const props = defineProps<{
    schools: { data: SchoolRecord[]; links: Array<{ url: string | null; label: string; active: boolean }>; current_page: number; last_page: number; total: number };
    plans: Array<{ id: number; name: string; price: string; currency: string }>;
    filters: { q: string };
}>();
const page = usePage();

const inputClass = 'h-10 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 text-sm text-slate-100 outline-none placeholder:text-slate-600 focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-300/10';
const selectClass = 'h-10 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 text-sm text-slate-100 outline-none focus:border-cyan-300/60';
</script>

<template>
    <Head title="School workspaces" />
    <section>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><p class="text-sm font-medium text-cyan-200">Client management</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">School workspaces</h1><p class="mt-2 text-sm text-slate-400">Create client workspaces, manage their primary administrator, access state, and package.</p></div>
            <div class="text-sm text-slate-400">{{ schools.total.toLocaleString() }} total workspace{{ schools.total === 1 ? '' : 's' }}</div>
        </div>

        <div v-if="page.props.flash?.status" class="mt-6 rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-200" role="status">{{ page.props.flash.status }}</div>
        <div v-if="page.props.errors?.account" class="mt-6 rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-200" role="alert">{{ page.props.errors.account }}</div>

        <Form action="/developer/schools" method="get" class="mt-6 flex max-w-xl gap-2">
            <input name="q" type="search" :value="filters.q" :class="inputClass" placeholder="Search schools, workspace codes, or administrator emails">
            <button class="shrink-0 rounded-xl border border-white/10 px-4 text-sm font-medium text-slate-200 hover:bg-white/5">Search</button>
            <Link v-if="filters.q" href="/developer/schools" class="flex items-center rounded-xl px-3 text-sm text-slate-400 hover:text-white">Clear</Link>
        </Form>

        <section class="mt-7 rounded-2xl border border-white/10 bg-slate-900/65 p-5 sm:p-6">
            <div class="flex items-start gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cyan-300/10 text-cyan-200"><Plus class="size-5" /></span><div><h2 class="font-semibold text-white">Add a school</h2><p class="mt-1 text-xs leading-5 text-slate-500">An administrator account is created with a random password. The setup link is sent by email; passwords are never shown here.</p></div></div>
            <Form action="/developer/schools" method="post" v-slot="{ errors, processing }" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300" for="new-school-name">School name</label><input id="new-school-name" name="name" required maxlength="191" :class="inputClass" placeholder="Northstar Academy"><p v-if="errors.name" class="text-xs text-rose-300">{{ errors.name }}</p></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300" for="new-school-code">Workspace code</label><input id="new-school-code" name="code" required minlength="3" maxlength="32" pattern="[A-Za-z0-9-]+" :class="inputClass" placeholder="NORTHSTAR"><p v-if="errors.code" class="text-xs text-rose-300">{{ errors.code }}</p></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300" for="new-admin-name">Primary administrator</label><input id="new-admin-name" name="admin_name" required maxlength="191" :class="inputClass" placeholder="Admin name"><p v-if="errors.admin_name" class="text-xs text-rose-300">{{ errors.admin_name }}</p></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300" for="new-admin-email">Administrator email</label><input id="new-admin-email" name="admin_email" type="email" required :class="inputClass" placeholder="admin@school.com"><p v-if="errors.admin_email" class="text-xs text-rose-300">{{ errors.admin_email }}</p></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300" for="new-school-plan">Initial package</label><select id="new-school-plan" name="school_plan_id" :class="selectClass"><option value="">Unassigned</option><option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }} · {{ plan.currency }} {{ plan.price }}</option></select><p v-if="errors.school_plan_id" class="text-xs text-rose-300">{{ errors.school_plan_id }}</p></div>
                <div class="md:col-span-2 xl:col-span-5"><button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-xl bg-cyan-300 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-200 disabled:opacity-60"><Building2 class="size-4" /> {{ processing ? 'Creating workspace…' : 'Create school workspace' }}</button></div>
            </Form>
        </section>

        <section class="mt-7 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/65">
            <div class="flex items-center justify-between border-b border-white/10 px-5 py-4"><div><h2 class="font-semibold text-white">Client accounts</h2><p class="mt-1 text-xs text-slate-500">School records and their primary administrator</p></div><UserRoundCog class="size-5 text-slate-400" /></div>
            <div v-if="schools.data.length" class="divide-y divide-white/5">
                <article v-for="school in schools.data" :key="school.id" class="px-5 py-5 sm:px-6">
                    <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-center">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl" :class="school.archived ? 'bg-slate-700/60 text-slate-400' : school.status === 'active' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-amber-300/10 text-amber-200'"><Building2 class="size-5" /></span>
                            <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold text-white">{{ school.name }}</h3><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide" :class="school.archived ? 'bg-slate-700 text-slate-300' : school.status === 'active' ? 'bg-emerald-300/10 text-emerald-200' : 'bg-amber-300/10 text-amber-200'">{{ school.archived ? 'Archived' : school.status }}</span></div><p class="mt-1 text-xs text-slate-500">Code <span class="font-mono text-slate-300">{{ school.code }}</span> · {{ school.deploymentMode }} · joined {{ school.createdAt }}</p><p class="mt-1 text-xs text-slate-400">{{ school.admin?.name ?? 'No administrator' }}<span v-if="school.admin"> · {{ school.admin.email }}</span></p></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                            <span class="mr-1 text-xs text-slate-400">{{ school.subscription?.planName ?? 'No package' }} <span v-if="school.subscription">· {{ school.subscription.status }}</span></span>
                            <Form v-if="!school.archived && school.admin" :action="`/developer/schools/${school.id}/reset-access`" method="post"><button class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"><KeyRound class="size-3.5" /> Reset access</button></Form>
                            <Form v-if="!school.archived" :action="`/developer/schools/${school.id}/toggle`" method="post"><button class="rounded-lg border border-white/10 px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5 hover:text-white">{{ school.status === 'active' ? 'Disable' : 'Enable' }}</button></Form>
                            <Form v-if="school.archived" :action="`/developer/schools/${school.id}/restore`" method="post"><button class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-300/20 px-3 py-2 text-xs font-medium text-cyan-200 transition hover:bg-cyan-300/10"><RotateCcw class="size-3.5" /> Restore</button></Form>
                            <details v-if="!school.archived" class="relative">
                                <summary class="cursor-pointer list-none rounded-lg border border-white/10 px-3 py-2 text-xs font-medium text-slate-300 transition hover:bg-white/5">Manage</summary>
                                <div class="absolute right-0 z-10 mt-2 w-[min(90vw,34rem)] rounded-2xl border border-white/10 bg-slate-900 p-4 shadow-2xl">
                                    <p class="mb-3 text-sm font-semibold text-white">Update school and administrator</p>
                                    <Form :action="`/developer/schools/${school.id}`" method="patch" v-slot="{ errors, processing }" class="grid gap-3 sm:grid-cols-2">
                                        <input name="name" required :value="school.name" :class="inputClass" aria-label="School name">
                                        <input name="code" required minlength="3" maxlength="32" :value="school.code" :class="inputClass" aria-label="Workspace code">
                                        <input name="admin_name" required :value="school.admin?.name" :class="inputClass" aria-label="Administrator name">
                                        <input name="admin_email" type="email" required :value="school.admin?.email" :class="inputClass" aria-label="Administrator email">
                                        <p v-if="Object.keys(errors).length" class="text-xs text-rose-300 sm:col-span-2">Review the school and administrator details.</p>
                                        <button type="submit" :disabled="processing" class="inline-flex items-center justify-center gap-2 rounded-lg bg-cyan-300 px-3 py-2 text-xs font-bold text-slate-950 sm:col-span-2"><Save class="size-3.5" /> Save client account</button>
                                    </Form>
                                    <div class="my-4 border-t border-white/10" />
                                    <p class="mb-3 text-sm font-semibold text-white">Package and subscription</p>
                                    <Form :action="`/developer/schools/${school.id}/subscription`" method="put" class="grid gap-3 sm:grid-cols-2">
                                        <select name="school_plan_id" :class="selectClass"><option value="">No package</option><option v-for="plan in plans" :key="plan.id" :value="plan.id" :selected="school.subscription?.planId === plan.id">{{ plan.name }}</option></select>
                                        <select name="status" :class="selectClass"><option v-for="status in ['trialing', 'active', 'past_due', 'cancelled']" :key="status" :value="status" :selected="(school.subscription?.status ?? 'trialing') === status">{{ status }}</option></select>
                                        <input name="trial_ends_at" type="date" :value="school.subscription?.trialEndsAt" class="h-10 rounded-xl border border-white/10 bg-slate-950/70 px-3 text-xs text-slate-300">
                                        <input name="ends_at" type="date" :value="school.subscription?.endsAt" class="h-10 rounded-xl border border-white/10 bg-slate-950/70 px-3 text-xs text-slate-300">
                                        <button type="submit" class="rounded-lg border border-cyan-300/20 px-3 py-2 text-xs font-semibold text-cyan-200 hover:bg-cyan-300/10 sm:col-span-2">Save package access</button>
                                    </Form>
                                    <Form :action="`/developer/schools/${school.id}/archive`" method="post" class="mt-4 border-t border-white/10 pt-4" onsubmit="return window.confirm('Archive this school? Its data will be retained and can be restored.')"><button class="inline-flex items-center gap-2 rounded-lg border border-rose-300/20 px-3 py-2 text-xs font-medium text-rose-200 hover:bg-rose-300/10"><Archive class="size-3.5" /> Archive school</button></Form>
                                </div>
                            </details>
                        </div>
                    </div>
                </article>
            </div>
            <div v-else class="px-5 py-14 text-center"><p class="text-sm font-medium text-slate-300">No school workspaces yet</p><p class="mt-1 text-xs text-slate-500">Create the first client workspace above.</p></div>
            <div v-if="schools.last_page > 1" class="flex items-center justify-between border-t border-white/10 px-5 py-4 text-xs text-slate-400"><span>Page {{ schools.current_page }} of {{ schools.last_page }}</span><div class="flex gap-2"><Link v-for="link in schools.links" :key="link.label" :href="link.url ?? '#'" preserve-scroll class="rounded-md px-2.5 py-1.5" :class="link.active ? 'bg-cyan-300 text-slate-950' : link.url ? 'text-slate-300 hover:bg-white/5' : 'pointer-events-none text-slate-600'">{{ link.label.replace('&laquo; Previous', 'Previous').replace('Next &raquo;', 'Next') }}</Link></div></div>
        </section>
    </section>
</template>

<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Archive, Blocks, Check, Plus, Save } from '@lucide/vue';

defineOptions({ layout: { title: 'Packages and plans' } });

type Plan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price: string;
    currency: string;
    billing_interval: string;
    student_limit: number | null;
    staff_limit: number | null;
    features: string[] | null;
    is_active: boolean;
    sort_order: number;
    subscriptions_count: number;
};

const props = defineProps<{ plans: Plan[] }>();
const page = usePage();
const inputClass = 'h-10 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 text-sm text-slate-100 outline-none placeholder:text-slate-600 focus:border-cyan-300/60 focus:ring-2 focus:ring-cyan-300/10';
const selectClass = 'h-10 w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 text-sm text-slate-100 outline-none focus:border-cyan-300/60';
</script>

<template>
    <Head title="Packages and plans" />
    <section>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-sm font-medium text-cyan-200">Subscriptions</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-white">Packages and plans</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">Define client packages, billing periods, feature lists, and student or staff limits. Disabling a package preserves existing school subscriptions.</p></div><div class="text-sm text-slate-400">{{ plans.length }} package{{ plans.length === 1 ? '' : 's' }}</div></div>

        <div v-if="page.props.flash?.status" class="mt-6 rounded-xl border border-emerald-300/20 bg-emerald-300/10 px-4 py-3 text-sm text-emerald-200" role="status">{{ page.props.flash.status }}</div>
        <div v-if="page.props.errors?.plan" class="mt-6 rounded-xl border border-rose-300/20 bg-rose-300/10 px-4 py-3 text-sm text-rose-200" role="alert">{{ page.props.errors.plan }}</div>

        <section class="mt-7 rounded-2xl border border-white/10 bg-slate-900/65 p-5 sm:p-6">
            <div class="flex items-start gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-300/10 text-violet-200"><Plus class="size-5" /></span><div><h2 class="font-semibold text-white">Create package</h2><p class="mt-1 text-xs text-slate-500">Set the offer details and enable it when it is ready for new clients.</p></div></div>
            <Form action="/developer/plans" method="post" v-slot="{ errors, processing }" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-1.5"><label for="plan-name" class="text-xs font-medium text-slate-300">Package name</label><input id="plan-name" name="name" required :class="inputClass" placeholder="Growth"><p v-if="errors.name" class="text-xs text-rose-300">{{ errors.name }}</p></div>
                <div class="space-y-1.5"><label for="plan-slug" class="text-xs font-medium text-slate-300">Slug (optional)</label><input id="plan-slug" name="slug" :class="inputClass" placeholder="growth"><p v-if="errors.slug" class="text-xs text-rose-300">{{ errors.slug }}</p></div>
                <div class="space-y-1.5"><label for="plan-price" class="text-xs font-medium text-slate-300">Price</label><input id="plan-price" name="price" type="number" step="0.01" min="0" required :class="inputClass" placeholder="0.00"><p v-if="errors.price" class="text-xs text-rose-300">{{ errors.price }}</p></div>
                <div class="space-y-1.5"><label for="plan-currency" class="text-xs font-medium text-slate-300">Currency</label><input id="plan-currency" name="currency" maxlength="3" value="USD" required :class="inputClass"></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300">Billing period</label><select name="billing_interval" required :class="selectClass"><option value="monthly">Monthly</option><option value="yearly">Yearly</option><option value="lifetime">Lifetime</option></select></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300">Student limit</label><input name="student_limit" type="number" min="1" :class="inputClass" placeholder="No limit"></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300">Staff limit</label><input name="staff_limit" type="number" min="1" :class="inputClass" placeholder="No limit"></div>
                <div class="space-y-1.5"><label class="text-xs font-medium text-slate-300">Display order</label><input name="sort_order" type="number" min="0" value="0" :class="inputClass"></div>
                <div class="space-y-1.5 md:col-span-2 xl:col-span-2"><label class="text-xs font-medium text-slate-300">Description</label><input name="description" maxlength="2000" :class="inputClass" placeholder="Who this package is for"></div>
                <div class="space-y-1.5 md:col-span-2 xl:col-span-2"><label class="text-xs font-medium text-slate-300">Included features (one per line)</label><textarea name="features_text" rows="2" class="w-full rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 outline-none placeholder:text-slate-600 focus:border-cyan-300/60" placeholder="Student portal&#10;Attendance reports"></textarea></div>
                <div class="flex items-center gap-2"><input id="plan-active" name="is_active" type="checkbox" value="1" checked class="size-4 rounded border-white/20 bg-slate-950 text-cyan-300"><label for="plan-active" class="text-sm text-slate-300">Available for assignment</label></div>
                <div><button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-xl bg-cyan-300 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-cyan-200 disabled:opacity-60"><Blocks class="size-4" /> {{ processing ? 'Creating…' : 'Create package' }}</button></div>
            </Form>
        </section>

        <section class="mt-7 space-y-4">
            <article v-for="plan in plans" :key="plan.id" class="rounded-2xl border border-white/10 bg-slate-900/65 p-5 sm:p-6">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                    <div class="flex items-start gap-3"><span class="flex size-10 shrink-0 items-center justify-center rounded-xl" :class="plan.is_active ? 'bg-cyan-300/10 text-cyan-200' : 'bg-slate-700/60 text-slate-400'"><Blocks class="size-5" /></span><div><div class="flex flex-wrap items-center gap-2"><h2 class="font-semibold text-white">{{ plan.name }}</h2><span class="rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="plan.is_active ? 'bg-emerald-300/10 text-emerald-200' : 'bg-slate-700 text-slate-300'">{{ plan.is_active ? 'Enabled' : 'Disabled' }}</span></div><p class="mt-1 text-xs text-slate-500">{{ plan.slug }} · {{ plan.subscriptions_count }} subscription{{ plan.subscriptions_count === 1 ? '' : 's' }}</p><p v-if="plan.description" class="mt-2 text-sm text-slate-400">{{ plan.description }}</p></div></div>
                    <div class="text-left lg:text-right"><p class="text-xl font-semibold text-white">{{ plan.currency }} {{ Number(plan.price).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</p><p class="mt-1 text-xs capitalize text-slate-500">{{ plan.billing_interval }}</p></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-400"><span v-if="plan.student_limit" class="rounded-full border border-white/10 px-3 py-1">{{ plan.student_limit.toLocaleString() }} students</span><span v-else class="rounded-full border border-white/10 px-3 py-1">No student limit</span><span v-if="plan.staff_limit" class="rounded-full border border-white/10 px-3 py-1">{{ plan.staff_limit.toLocaleString() }} staff</span><span v-else class="rounded-full border border-white/10 px-3 py-1">No staff limit</span><span v-for="feature in plan.features ?? []" :key="feature" class="inline-flex items-center gap-1 rounded-full border border-white/10 px-3 py-1"><Check class="size-3 text-cyan-200" />{{ feature }}</span></div>
                <details class="mt-5 border-t border-white/10 pt-4">
                    <summary class="cursor-pointer list-none text-xs font-semibold text-cyan-200">Edit package settings</summary>
                    <Form :action="`/developer/plans/${plan.id}`" method="patch" v-slot="{ errors, processing }" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <input name="name" required :value="plan.name" :class="inputClass" aria-label="Package name">
                        <input name="slug" :value="plan.slug" :class="inputClass" aria-label="Package slug">
                        <input name="price" type="number" min="0" step="0.01" required :value="plan.price" :class="inputClass" aria-label="Price">
                        <input name="currency" maxlength="3" required :value="plan.currency" :class="inputClass" aria-label="Currency">
                        <select name="billing_interval" :class="selectClass"><option v-for="interval in ['monthly', 'yearly', 'lifetime']" :key="interval" :value="interval" :selected="plan.billing_interval === interval">{{ interval }}</option></select>
                        <input name="student_limit" type="number" min="1" :value="plan.student_limit ?? ''" :class="inputClass" aria-label="Student limit">
                        <input name="staff_limit" type="number" min="1" :value="plan.staff_limit ?? ''" :class="inputClass" aria-label="Staff limit">
                        <input name="sort_order" type="number" min="0" :value="plan.sort_order" :class="inputClass" aria-label="Display order">
                        <input name="description" :value="plan.description ?? ''" :class="inputClass" aria-label="Description">
                        <textarea name="features_text" rows="2" class="rounded-xl border border-white/10 bg-slate-950/70 px-3 py-2 text-sm text-slate-100 outline-none focus:border-cyan-300/60" aria-label="Features, one per line">{{ (plan.features ?? []).join('\n') }}</textarea>
                        <label class="flex items-center gap-2 text-sm text-slate-300"><input name="is_active" type="checkbox" value="1" :checked="plan.is_active" class="size-4 rounded border-white/20 bg-slate-950 text-cyan-300"> Available for assignment</label>
                        <div class="flex items-center gap-2"><button type="submit" :disabled="processing" class="inline-flex items-center gap-2 rounded-lg bg-cyan-300 px-3 py-2 text-xs font-bold text-slate-950"><Save class="size-3.5" /> Save changes</button></div>
                        <p v-if="Object.keys(errors).length" class="text-xs text-rose-300 md:col-span-2 xl:col-span-4">Review the package values and try again.</p>
                    </Form>
                    <div class="mt-3 flex gap-2">
                        <Form :action="`/developer/plans/${plan.id}/toggle`" method="post"><button class="rounded-lg border border-white/10 px-3 py-2 text-xs text-slate-300 hover:bg-white/5">{{ plan.is_active ? 'Disable package' : 'Enable package' }}</button></Form>
                        <Form v-if="plan.subscriptions_count === 0" :action="`/developer/plans/${plan.id}`" method="delete" onsubmit="return window.confirm('Archive this package?')"><button class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300/20 px-3 py-2 text-xs text-rose-200 hover:bg-rose-300/10"><Archive class="size-3.5" /> Archive</button></Form>
                    </div>
                </details>
            </article>
            <div v-if="!plans.length" class="rounded-2xl border border-dashed border-white/15 p-10 text-center"><p class="text-sm font-medium text-slate-300">No packages configured</p><p class="mt-1 text-xs text-slate-500">Create your first package above, then assign it to a school workspace.</p></div>
        </section>
    </section>
</template>

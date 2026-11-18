<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowRight, BookOpenCheck, GraduationCap, ShieldCheck } from '@lucide/vue';
</script>

<template>
    <Head title="School Management System" />

    <main class="min-h-screen bg-slate-950 text-white">
        <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-48 right-[-8rem] size-[34rem] rounded-full bg-cyan-500/10 blur-3xl" />
            <div class="absolute bottom-[-14rem] left-[-10rem] size-[36rem] rounded-full bg-blue-600/10 blur-3xl" />
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-7 sm:px-10 lg:px-12">
            <header class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-3" aria-label="School Management System home">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-950/40">
                        <GraduationCap class="size-6" />
                    </span>
                    <span class="text-sm font-semibold tracking-wide sm:text-base">School Management System</span>
                </a>
                <a href="/login" class="rounded-xl border border-white/15 px-4 py-2.5 text-sm font-semibold text-white transition hover:border-cyan-300/70 hover:bg-white/5">
                    School sign in
                </a>
            </header>

            <section class="grid flex-1 items-center gap-14 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:py-24">
                <div class="max-w-2xl">
                    <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/5 px-3.5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-200">
                        <span class="size-1.5 rounded-full bg-cyan-300" />
                        One workspace for your school
                    </p>
                    <h1 class="text-4xl font-semibold leading-tight tracking-tight sm:text-6xl lg:text-7xl">
                        Give your school day <span class="text-cyan-300">room to run.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">
                        Bring administration, teaching, attendance, learning, and family communication together in one secure school workspace.
                    </p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="/login" class="inline-flex items-center gap-2 rounded-xl bg-cyan-300 px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-cyan-200">
                            Sign in to your school <ArrowRight class="size-4" />
                        </a>
                        <a href="/register" class="rounded-xl border border-cyan-300/30 px-5 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-300/10">Start a free 7-day trial</a>
                    </div>
                    <div class="mt-12 grid max-w-xl gap-5 border-t border-white/10 pt-7 sm:grid-cols-3">
                        <div class="flex gap-3 text-sm text-slate-300"><BookOpenCheck class="mt-0.5 size-4 shrink-0 text-cyan-300" /> Academics and learning</div>
                        <div class="flex gap-3 text-sm text-slate-300"><ShieldCheck class="mt-0.5 size-4 shrink-0 text-cyan-300" /> School data stays private</div>
                        <div class="flex gap-3 text-sm text-slate-300"><GraduationCap class="mt-0.5 size-4 shrink-0 text-cyan-300" /> Built for every role</div>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-lg">
                    <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-cyan-400/20 via-blue-500/10 to-transparent blur-2xl" />
                    <div class="relative overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-900/80 p-5 shadow-2xl shadow-black/40 backdrop-blur sm:p-7">
                        <div class="flex items-center justify-between border-b border-white/10 pb-5">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.16em] text-slate-400">School overview</p>
                                <h2 class="mt-1 text-lg font-semibold">Everything in its place</h2>
                            </div>
                            <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">Workspace ready</span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/8 bg-white/[0.035] p-4">
                                <p class="text-xs text-slate-400">Attendance</p>
                                <p class="mt-3 text-2xl font-semibold">Daily</p>
                                <p class="mt-1 text-xs text-slate-500">Clear class records</p>
                            </div>
                            <div class="rounded-2xl border border-white/8 bg-white/[0.035] p-4">
                                <p class="text-xs text-slate-400">Learning</p>
                                <p class="mt-3 text-2xl font-semibold">Connected</p>
                                <p class="mt-1 text-xs text-slate-500">Lessons and results</p>
                            </div>
                            <div class="col-span-2 rounded-2xl border border-white/8 bg-white/[0.035] p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium">A calmer school workflow</p>
                                        <p class="mt-1 text-xs text-slate-400">A role-based view for staff and students</p>
                                    </div>
                                    <span class="flex size-10 items-center justify-center rounded-xl bg-cyan-300/10 text-cyan-200"><BookOpenCheck class="size-5" /></span>
                                </div>
                                <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-800"><div class="h-full w-3/4 rounded-full bg-gradient-to-r from-cyan-300 to-blue-400" /></div>
                                <div class="mt-2 flex justify-between text-[11px] text-slate-500"><span>One shared source of truth</span><span>Secure by role</span></div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-3 rounded-2xl bg-cyan-300/[0.07] px-4 py-3 text-sm text-cyan-100">
                            <ShieldCheck class="size-4 shrink-0" /> Each school works in its own protected workspace.
                        </div>
                    </div>
                </div>
            </section>

            <footer class="flex flex-col gap-2 border-t border-white/10 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ new Date().getFullYear() }} School Management System</span>
                <span>Secure access for school communities</span>
            </footer>
        </div>
    </main>
</template>

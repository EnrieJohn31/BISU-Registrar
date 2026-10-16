<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BadgeCheck, FileText, Receipt, Ticket } from '@lucide/vue';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';
</script>

<template>
    <Head title="Welcome" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <div
                    class="flex flex-1 flex-col justify-center rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h1 class="mb-1 font-medium">Welcome to BISU Registrar</h1>
                    <p class="mb-6 text-[#706f6c] dark:text-[#A1A09A]">
                        Document requests, queuing and transactions for the
                        Registrar and Cashier offices of Bohol Island State
                        University – Balilihan Campus.
                    </p>
                    <div class="flex gap-3 text-sm leading-normal">
                        <Link
                            :href="
                                $page.props.auth.user ? dashboard() : login()
                            "
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            {{ $page.props.auth.user ? 'Dashboard' : 'Log in' }}
                        </Link>
                    </div>
                </div>
                <div
                    class="relative -mb-px flex aspect-[335/364] w-full shrink-0 flex-col justify-between overflow-hidden rounded-t-lg bg-blue-600 p-8 text-white lg:mb-0 lg:-ml-px lg:aspect-auto lg:min-h-[420px] lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-blue-950"
                    aria-hidden="true"
                >
                    <div
                        class="pointer-events-none absolute -top-16 -right-16 size-56 rounded-full bg-white/10"
                    />
                    <div
                        class="pointer-events-none absolute -bottom-24 -left-16 size-64 rounded-full bg-white/5"
                    />

                    <div
                        class="relative translate-y-0 opacity-100 transition-all duration-750 starting:opacity-0 motion-safe:starting:translate-y-6"
                    >
                        <p
                            class="text-xs font-medium tracking-[0.2em] text-white/70 uppercase"
                        >
                            Balilihan Campus
                        </p>
                        <p class="text-5xl font-extrabold tracking-tight">
                            BISU
                        </p>
                        <p class="text-2xl font-semibold text-white/90">
                            Registrar
                        </p>
                    </div>

                    <div class="relative mx-auto h-36 w-full max-w-[280px]">
                        <div
                            class="absolute inset-x-8 top-0 h-full rounded-xl bg-white/20 opacity-100 transition-all delay-300 duration-750 starting:opacity-0 motion-safe:starting:translate-y-6"
                        />
                        <div
                            class="absolute inset-x-4 top-3 h-full rounded-xl bg-white/40 opacity-100 transition-all delay-200 duration-750 starting:opacity-0 motion-safe:starting:translate-y-6"
                        />
                        <div
                            class="absolute inset-x-0 top-6 rounded-xl bg-white p-4 text-zinc-900 opacity-100 shadow-xl transition-all delay-100 duration-750 dark:bg-zinc-900 dark:text-zinc-100 starting:opacity-0 motion-safe:starting:translate-y-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                                >
                                    <FileText class="size-5" />
                                </div>
                                <div class="grid flex-1 gap-1.5">
                                    <div
                                        class="h-2 w-3/4 rounded-full bg-zinc-200 dark:bg-zinc-700"
                                    />
                                    <div
                                        class="h-2 w-1/2 rounded-full bg-zinc-100 dark:bg-zinc-800"
                                    />
                                </div>
                                <BadgeCheck class="size-6 text-emerald-500" />
                            </div>
                            <div
                                class="mt-4 flex gap-3 border-t border-zinc-100 pt-3 dark:border-zinc-800"
                            >
                                <div
                                    class="flex flex-1 items-center gap-2 text-blue-600 dark:text-blue-400"
                                >
                                    <Ticket class="size-4" />
                                    <div
                                        class="h-2 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800"
                                    />
                                </div>
                                <div
                                    class="flex flex-1 items-center gap-2 text-blue-600 dark:text-blue-400"
                                >
                                    <Receipt class="size-4" />
                                    <div
                                        class="h-2 flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    ></div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>

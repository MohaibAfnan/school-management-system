<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarCheck,
    ClipboardList,
    FileText,
    GraduationCap,
    LayoutGrid,
    Users,
    Settings,
    School,
    UserCheck,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const role = computed(() => user.value?.role?.slug);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (role.value === 'admin') {
        items.push(
            { title: 'Students', href: '#', icon: GraduationCap },
            { title: 'Teachers', href: '#', icon: Users },
            { title: 'Classes', href: '#', icon: School },
            { title: 'Subjects', href: '#', icon: BookOpen },
            { title: 'Attendance', href: '#', icon: CalendarCheck },
            { title: 'Reports', href: '#', icon: FileText },
        );
    } else if (role.value === 'teacher') {
        items.push(
            { title: 'My Classes', href: '#', icon: School },
            { title: 'Students', href: '#', icon: GraduationCap },
            { title: 'Attendance', href: '#', icon: CalendarCheck },
            { title: 'Timetable', href: '#', icon: ClipboardList },
        );
    } else if (role.value === 'student') {
        items.push(
            { title: 'My Class', href: '#', icon: School },
            { title: 'Timetable', href: '#', icon: ClipboardList },
            { title: 'Attendance', href: '#', icon: CalendarCheck },
            { title: 'Results', href: '#', icon: FileText },
            { title: 'Profile', href: '#', icon: UserCheck },
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

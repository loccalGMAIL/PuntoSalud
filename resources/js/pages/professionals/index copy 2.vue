<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div>
                <h1>Lista de Profesionales</h1>
                <p>Total: {{ professionals.length }}</p>

                <div v-for="professional in professionals" :key="professional.id">
                    <p>{{ professional.first_name }} {{ professional.last_name }}</p>
                </div>

                <div class="py-12">
                    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <!-- Header con botón crear -->
                                <div class="mb-6 flex items-center justify-between">
                                    <h3 class="text-lg font-medium">Lista de Profesionales</h3>
                                    <Link
                                        :href="route('professionals.create')"
                                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                                    >
                                        Nuevo Profesional
                                    </Link>
                                </div>

                                <!-- Tabla de profesionales -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border border-gray-300 px-4 py-2 text-left">Nombre</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Especialidad</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Matrícula</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Comisión</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Estado</th>
                                                <th class="border border-gray-300 px-4 py-2 text-left">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="professional in professionals" :key="professional.id">
                                                <td class="border border-gray-300 px-4 py-2">
                                                    {{ professional.full_name }}
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    {{ professional.email }}
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    {{ professional.specialty.name }}
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    {{ professional.license_number }}
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">{{ professional.commission_percentage }}%</td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    <span
                                                        :class="professional.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                                    >
                                                        {{ professional.is_active ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </td>
                                                <td class="border border-gray-300 px-4 py-2">
                                                    <div class="flex space-x-2">
                                                        <Link
                                                            :href="route('professionals.show', professional.id)"
                                                            class="text-blue-600 hover:text-blue-800"
                                                        >
                                                            Ver
                                                        </Link>
                                                        <Link
                                                            :href="route('professionals.edit', professional.id)"
                                                            class="text-yellow-600 hover:text-yellow-800"
                                                        >
                                                            Editar
                                                        </Link>
                                                        <button
                                                            @click="deactivateProfessional(professional)"
                                                            v-if="professional.is_active"
                                                            class="text-red-600 hover:text-red-800"
                                                        >
                                                            Desactivar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mensaje si no hay profesionales -->
                                <div v-if="professionals.length === 0" class="py-8 text-center">
                                    <p class="text-gray-500">No hay profesionales registrados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Profesionales',
        href: '',
    },
];
defineProps({
    professionals: Array,
});
</script>

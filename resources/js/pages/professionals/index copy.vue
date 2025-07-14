<template>
    <Head title="Profesionales" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profesionales
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <!-- Header con botón crear -->
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Lista de Profesionales</h3>
                            <Link 
                                :href="route('professionals.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
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
                                        <td class="border border-gray-300 px-4 py-2">
                                            {{ professional.commission_percentage }}%
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span 
                                                :class="professional.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="px-2 py-1 rounded-full text-xs font-medium"
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
                        <div v-if="professionals.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No hay profesionales registrados.</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    professionals: Array
});

const deactivateProfessional = (professional) => {
    if (confirm('¿Estás seguro de que quieres desactivar este profesional?')) {
        router.delete(route('professionals.destroy', professional.id));
    }
};
</script>
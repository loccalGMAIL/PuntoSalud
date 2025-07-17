<template>
    <Head title="Profesionales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Header con estadísticas -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Profesionales
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Gestiona los profesionales del centro médico
                        </p>
                    </div>
                    <PSButton 
                        variant="primary"
                        :icon-left="Plus"
                        @click="openCreateModal"
                    >
                        Nuevo Profesional
                    </PSButton>
                </div>

                <!-- Cards de estadísticas usando PSStatsCard -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <PSStatsCard
                        title="Total"
                        :value="stats.total"
                        subtitle="profesionales registrados"
                        :icon="Users"
                        height="sm"
                    />
                    
                    <PSStatsCard
                        title="Activos"
                        :value="stats.active"
                        value-color="success"
                        subtitle="profesionales activos"
                        :icon="UserCheck"
                        height="sm"
                    />
                    
                    <PSStatsCard
                        title="Inactivos"
                        :value="stats.inactive"
                        value-color="error"
                        subtitle="profesionales inactivos"
                        :icon="UserX"
                        height="sm"
                    />
                    
                    <PSStatsCard
                        title="Especialidades"
                        :value="stats.specialtiesCount"
                        subtitle="especialidades médicas"
                        :icon="Heart"
                        value-color="info"
                        height="sm"
                    />
                </div>
            </div>

            <!-- Filtros usando PSFilterCard -->
            <PSFilterCard
                title="Filtros y Búsqueda"
                :icon="Filter"
                layout="grid"
                columns="5"
                :has-active-filters="hasActiveFilters"
                @clear-filters="clearFilters"
            >
                <!-- Búsqueda -->
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300"> Buscar </label>
                    <Input v-model="searchTerm" placeholder="Buscar por nombre, DNI o email..." :class="puntoSaludTheme.input.search" />
                </div>

                <!-- Filtro por especialidad -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Especialidad
                    </label>
                    <Select v-model="selectedSpecialty">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Todas las especialidades" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las especialidades</SelectItem>
                            <SelectItem 
                                v-for="specialty in specialtyOptions" 
                                :key="specialty.id" 
                                :value="specialty.id.toString()"
                            >
                                {{ specialty.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Filtro por estado -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Estado
                    </label>
                    <Select v-model="selectedStatus">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Todos los estados" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="active">Activos</SelectItem>
                            <SelectItem value="inactive">Inactivos</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Contador de resultados -->
                <div class="flex items-end">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>{{ filteredProfessionals.length }}</strong> de 
                        <strong>{{ stats.total }}</strong> profesionales
                    </div>
                </div>
            </PSFilterCard>

            <!-- Tabla de profesionales usando PSCard -->
            <PSCard
                title="Lista de Profesionales"
                :icon="Users"
                variant="default"
            >
                <div class="rounded-md border border-green-200/50 dark:border-green-800/30">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-green-200/50 dark:border-green-800/30">
                                <TableHead class="text-gray-700 dark:text-gray-300">Profesional</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Especialidad</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Contacto</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Matrícula</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Comisión</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Estado</TableHead>
                                <TableHead class="text-right text-gray-700 dark:text-gray-300">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Estado vacío -->
                            <TableRow v-if="filteredProfessionals.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <Users class="h-12 w-12 text-gray-400 dark:text-gray-600" />
                                        <p class="text-gray-600 dark:text-gray-400">
                                            No se encontraron profesionales
                                        </p>
                                        <PSButton 
                                            variant="outline" 
                                            size="sm" 
                                            @click="clearFilters"
                                        >
                                            Limpiar filtros
                                        </PSButton>
                                    </div>
                                </TableCell>
                            </TableRow>
                            
                            <!-- Filas de profesionales -->
                            <TableRow 
                                v-for="professional in filteredProfessionals" 
                                :key="professional.id"
                                class="border-green-200/30 hover:bg-green-50/30 dark:border-green-800/30 dark:hover:bg-green-950/20 transition-colors duration-200"
                            >
                                <!-- Profesional -->
                                <TableCell class="font-medium">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                            <UserCheck :class="getIconClasses('primary', 'md')" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ professional.first_name }} {{ professional.last_name }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                ID: {{ professional.id }}
                                            </p>
                                        </div>
                                    </div>
                                </TableCell>
                                
                                <!-- Especialidad -->
                                <TableCell>
                                    <Badge :class="getStatusClasses('info')">
                                        {{ professional.specialty.name }}
                                    </Badge>
                                </TableCell>
                                
                                <!-- Contacto -->
                                <TableCell>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ professional.email }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ professional.phone || 'Sin teléfono' }}
                                        </p>
                                    </div>
                                </TableCell>
                                
                                <!-- Matrícula -->
                                <TableCell class="font-mono text-sm">
                                    {{ professional.license_number }}
                                </TableCell>
                                
                                <!-- Comisión -->
                                <TableCell>
                                    <span class="font-semibold text-green-600 dark:text-green-400">
                                        {{ professional.commission_percentage }}%
                                    </span>
                                </TableCell>
                                
                                <!-- Estado -->
                                <TableCell>
                                    <Badge :class="getStatusClasses(professional.is_active ? 'active' : 'inactive')">
                                        {{ professional.is_active ? 'Activo' : 'Inactivo' }}
                                    </Badge>
                                </TableCell>
                                
                                <!-- Acciones -->
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <PSButton variant="ghost" size="sm">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </PSButton>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-48">
                                            <DropdownMenuItem 
                                                @click="openEditModal(professional)"
                                                class="cursor-pointer"
                                            >
                                                <Edit class="mr-2 h-4 w-4" />
                                                Editar
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                @click="toggleStatus(professional)"
                                                class="cursor-pointer"
                                            >
                                                <component 
                                                    :is="professional.is_active ? UserX : UserCheck" 
                                                    class="mr-2 h-4 w-4" 
                                                />
                                                {{ professional.is_active ? 'Desactivar' : 'Activar' }}
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </PSCard>

            <!-- Modal (mantener el existente) -->
            <ProfessionalModal
                :open="modalOpen"
                :professional="editingProfessional"
                :specialties="specialtyOptions"
                @close="modalOpen = false"
                @success="handleModalSuccess"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

// Importar nuestros componentes PS
import PSCard from '@/components/punto-salud/PSCard.vue';
import PSButton from '@/components/punto-salud/PSButton.vue';
import PSStatsCard from '@/components/punto-salud/PSStatsCard.vue';
import PSFilterCard from '@/components/punto-salud/PSFilterCard.vue';

// Importar tema y utilidades
import puntoSaludTheme, { getStatusClasses, getIconClasses } from '@/theme/PuntoSaludTheme.js';

import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    MoreHorizontal, 
    Edit, 
    UserCheck, 
    UserX,
    Filter,
    Users,
    Heart
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Import del modal existente
import ProfessionalModal from './ProfessionalModal.vue';

// Interfaces (mantener las existentes)
interface Specialty {
    id: number;
    name: string;
    description: string;
    is_active: boolean;
}

interface Professional {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone: string | null;
    license_number: string;
    commission_percentage: number;
    is_active: boolean;
    specialty: Specialty;
    created_at: string;
    updated_at: string;
}

interface Props {
    professionals: Professional[];
    specialties: Specialty[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Profesionales', href: '/professionals' },
];

// Estados reactivos para filtros (mantener lógica existente)
const searchTerm = ref('');
const selectedSpecialty = ref('all');
const selectedStatus = ref('all');

// Estados del modal (mantener lógica existente)
const modalOpen = ref(false);
const editingProfessional = ref<Professional | null>(null);

// Opciones de filtros (mantener lógica existente)
const specialtyOptions = computed(() => props.specialties);

// Computed para detectar filtros activos
const hasActiveFilters = computed(() => {
    return searchTerm.value !== '' || 
           selectedSpecialty.value !== 'all' || 
           selectedStatus.value !== 'all';
});

// Profesionales filtrados (mantener lógica existente)
const filteredProfessionals = computed(() => {
    return props.professionals.filter(professional => {
        // Filtro por búsqueda (nombre completo, email)
        const searchMatch = searchTerm.value === '' || 
            `${professional.first_name} ${professional.last_name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            professional.email.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            professional.license_number.toLowerCase().includes(searchTerm.value.toLowerCase());

        // Filtro por especialidad
        const specialtyMatch = selectedSpecialty.value === 'all' || 
            professional.specialty.id.toString() === selectedSpecialty.value;

        // Filtro por estado
        const statusMatch = selectedStatus.value === 'all' || 
            (selectedStatus.value === 'active' && professional.is_active) ||
            (selectedStatus.value === 'inactive' && !professional.is_active);

        return searchMatch && specialtyMatch && statusMatch;
    });
});

// Estadísticas (mantener lógica existente)
const stats = computed(() => {
    const total = props.professionals.length;
    const active = props.professionals.filter(p => p.is_active).length;
    const inactive = total - active;
    const specialtiesCount = props.specialties.length;

    return { total, active, inactive, specialtiesCount };
});

// Funciones del modal (mantener lógica existente)
const openCreateModal = () => {
    editingProfessional.value = null;
    modalOpen.value = true;
};

const openEditModal = (professional: Professional) => {
    editingProfessional.value = professional;
    modalOpen.value = true;
};

const handleModalSuccess = () => {
    // Refrescar la página para obtener los datos actualizados
    router.reload({ only: ['professionals'] });
};

// Funciones de acciones (mantener lógica existente)
const toggleStatus = (professional: Professional) => {
    const action = professional.is_active ? 'desactivar' : 'activar';
    if (confirm(`¿Estás seguro de ${action} este profesional?`)) {
        router.patch(`/professionals/${professional.id}`, {
            is_active: !professional.is_active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['professionals'] });
            }
        });
    }
};

// Limpiar filtros
const clearFilters = () => {
    searchTerm.value = '';
    selectedSpecialty.value = 'all';
    selectedStatus.value = 'all';
};
</script>

<style scoped>
/* Estilos adicionales para mejorar la tabla */
.table-row-hover {
    transition-property: background-color, color;
    transition-duration: 200ms;
}
.table-row-hover:hover {
    background-color: rgba(16, 185, 129, 0.3); /* emerald-50/30 */
}
@media (prefers-color-scheme: dark) {
    .table-row-hover:hover {
        background-color: rgba(2, 44, 34, 0.2); /* emerald-950/20 */
    }
}
</style>
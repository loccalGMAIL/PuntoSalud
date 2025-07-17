<template>
    <Head title="Pacientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-3">
            
            <!-- Header con estadísticas -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Pacientes
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Gestiona los pacientes del centro médico
                        </p>
                    </div>
                    <PSButton 
                        variant="primary"
                        :icon-left="Plus"
                        @click="openCreateModal"
                    >
                        Nuevo Paciente
                    </PSButton>
                </div>

                <!-- Cards de estadísticas usando PSStatsCard -->
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                    <PSStatsCard
                        title="Total"
                        :value="stats.total"
                        subtitle="pacientes registrados"
                        :icon="Heart"
                        height="sm"
                    />
                    
                    <PSStatsCard
                        title="Activos"
                        :value="stats.active"
                        value-color="success"
                        subtitle="pacientes activos"
                        :icon="UserCheck"
                        height="sm"

                    />
                    
                    <PSStatsCard
                        title="Con Obra Social"
                        :value="stats.withInsurance"
                        value-color="info"
                        subtitle="tienen cobertura médica"
                        :icon="Shield"
                        height="sm"
                    />
                    
                    <PSStatsCard
                        title="Sin Obra Social"
                        :value="stats.withoutInsurance"
                        value-color="warning"
                        subtitle="sin cobertura médica"
                        :icon="ShieldAlert"
                        height="sm"
                    />
                </div>
            </div>

            <!-- Filtros usando PSFilterCard -->
            <PSFilterCard
                title="Filtros y Búsqueda"
                :icon="Filter"
                layout="flex"
                :has-active-filters="hasActiveFilters"
                @clear-filters="clearFilters"
                class-name="py-3"
            >
                <div class="flex flex-wrap items-end gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Buscar
                    </label>
                    <Input
                        v-model="searchTerm"
                        placeholder="Buscar por nombre, DNI o email..."
                        :class="puntoSaludTheme.input.search"
                    />
                </div>

                <!-- Filtro por obra social -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Obra Social
                    </label>
                    <Select v-model="selectedHealthInsurance">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Todas las obras sociales" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las obras sociales</SelectItem>
                            <SelectItem value="none">Sin obra social</SelectItem>
                            <SelectItem 
                                v-for="insurance in healthInsurances" 
                                :key="insurance" 
                                :value="insurance"
                            >
                                {{ insurance }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Filtro por rango de edad -->
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Rango de Edad
                    </label>
                    <Select v-model="selectedAgeRange">
                        <SelectTrigger class="mt-1">
                            <SelectValue placeholder="Todas las edades" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las edades</SelectItem>
                            <SelectItem value="Menor">Menores (0-17)</SelectItem>
                            <SelectItem value="18-30">Jóvenes (18-30)</SelectItem>
                            <SelectItem value="31-50">Adultos (31-50)</SelectItem>
                            <SelectItem value="51-70">Mayores (51-70)</SelectItem>
                            <SelectItem value="70+">Ancianos (70+)</SelectItem>
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
                        <strong>{{ filteredPatients.length }}</strong> de 
                        <strong>{{ stats.total }}</strong> pacientes
                    </div>
                </div>
            </PSFilterCard>

            <!-- Tabla de pacientes usando PSCard -->
            <PSCard
                title="Lista de Pacientes"
                :icon="Users"
                variant="default"
            >
                <div class="rounded-md border border-green-200/50 dark:border-green-800/30">
                    <Table>
                        <TableHeader>
                            <TableRow class="border-green-200/50 dark:border-green-800/30">
                                <TableHead class="text-gray-700 dark:text-gray-300">Paciente</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">DNI</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Contacto</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Edad</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Obra Social</TableHead>
                                <TableHead class="text-gray-700 dark:text-gray-300">Estado</TableHead>
                                <TableHead class="text-right text-gray-700 dark:text-gray-300">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <!-- Estado vacío -->
                            <TableRow v-if="filteredPatients.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <Users class="h-12 w-12 text-gray-400 dark:text-gray-600" />
                                        <p class="text-gray-600 dark:text-gray-400">
                                            No se encontraron pacientes
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
                            
                            <!-- Filas de pacientes -->
                            <TableRow 
                                v-for="patient in filteredPatients" 
                                :key="patient.id"
                                class="border-green-200/30 hover:bg-green-50/30 dark:border-green-800/30 dark:hover:bg-green-950/20 transition-colors duration-200"
                            >
                                <!-- Paciente -->
                                <TableCell class="font-medium">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                            <Heart :class="getIconClasses('primary', 'md')" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ patient.first_name }} {{ patient.last_name }}
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                ID: {{ patient.id }}
                                            </p>
                                        </div>
                                    </div>
                                </TableCell>
                                
                                <!-- DNI -->
                                <TableCell class="font-mono text-sm">
                                    {{ patient.dni }}
                                </TableCell>
                                
                                <!-- Contacto -->
                                <TableCell>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ patient.email || 'Sin email' }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ patient.phone }}
                                        </p>
                                    </div>
                                </TableCell>
                                
                                <!-- Edad -->
                                <TableCell>
                                    <div class="text-center">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ calculateAge(patient.birth_date) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            años
                                        </p>
                                    </div>
                                </TableCell>
                                
                                <!-- Obra Social -->
                                <TableCell>
                                    <Badge 
                                        v-if="patient.health_insurance"
                                        :class="getStatusClasses('info')"
                                    >
                                        {{ patient.health_insurance }}
                                    </Badge>
                                    <Badge 
                                        v-else
                                        :class="getStatusClasses('cancelled')"
                                    >
                                        Sin obra social
                                    </Badge>
                                </TableCell>
                                
                                <!-- Estado -->
                                <TableCell>
                                    <Badge :class="getStatusClasses(patient.is_active ? 'active' : 'inactive')">
                                        {{ patient.is_active ? 'Activo' : 'Inactivo' }}
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
                                                @click="openEditModal(patient)"
                                                class="cursor-pointer"
                                            >
                                                <Edit class="mr-2 h-4 w-4" />
                                                Editar
                                            </DropdownMenuItem>
                                            <DropdownMenuItem 
                                                @click="toggleStatus(patient)"
                                                class="cursor-pointer"
                                            >
                                                <component 
                                                    :is="patient.is_active ? UserX : UserCheck" 
                                                    class="mr-2 h-4 w-4" 
                                                />
                                                {{ patient.is_active ? 'Desactivar' : 'Activar' }}
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
            <PatientModal
                :open="modalOpen"
                :patient="editingPatient"
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
    Calendar,
    Heart,
    Users,
    Shield,
    ShieldAlert
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Import del modal existente
import PatientModal from './PatientModal.vue';

// Interfaces (mantener las existentes)
interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    birth_date: string;
    email: string | null;
    phone: string;
    address: string | null;
    health_insurance: string | null;
    health_insurance_number: string | null;
    medical_notes: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    patients: Patient[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pacientes', href: '/patients' },
];

// Estados reactivos para filtros (mantener lógica existente)
const searchTerm = ref('');
const selectedHealthInsurance = ref('all');
const selectedStatus = ref('all');
const selectedAgeRange = ref('all');

// Estados del modal (mantener lógica existente)
const modalOpen = ref(false);
const editingPatient = ref<Patient | null>(null);

// Computed para detectar filtros activos
const hasActiveFilters = computed(() => {
    return searchTerm.value !== '' || 
           selectedHealthInsurance.value !== 'all' || 
           selectedStatus.value !== 'all' ||
           selectedAgeRange.value !== 'all';
});

// Opciones de filtros (mantener lógica existente)
const healthInsurances = computed(() => {
    const uniqueInsurances = props.patients
        .filter(p => p.health_insurance)
        .reduce((acc, patient) => {
            if (!acc.includes(patient.health_insurance!)) {
                acc.push(patient.health_insurance!);
            }
            return acc;
        }, [] as string[]);
    
    return uniqueInsurances.sort();
});

// Función para calcular edad
const calculateAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
};

// Función para filtrar por rango de edad
const getAgeRange = (birthDate: string) => {
    const age = calculateAge(birthDate);
    if (age < 18) return 'Menor';
    if (age <= 30) return '18-30';
    if (age <= 50) return '31-50';
    if (age <= 70) return '51-70';
    return '70+';
};

// Pacientes filtrados (mantener lógica existente)
const filteredPatients = computed(() => {
    return props.patients.filter(patient => {
        // Filtro por búsqueda (nombre completo, DNI, email)
        const searchMatch = searchTerm.value === '' || 
            `${patient.first_name} ${patient.last_name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            patient.dni.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (patient.email && patient.email.toLowerCase().includes(searchTerm.value.toLowerCase()));

        // Filtro por obra social
        const insuranceMatch = selectedHealthInsurance.value === 'all' || 
            (selectedHealthInsurance.value === 'none' && !patient.health_insurance) ||
            patient.health_insurance === selectedHealthInsurance.value;

        // Filtro por estado
        const statusMatch = selectedStatus.value === 'all' || 
            (selectedStatus.value === 'active' && patient.is_active) ||
            (selectedStatus.value === 'inactive' && !patient.is_active);

        // Filtro por rango de edad
        const ageMatch = selectedAgeRange.value === 'all' || 
            getAgeRange(patient.birth_date) === selectedAgeRange.value;

        return searchMatch && insuranceMatch && statusMatch && ageMatch;
    });
});

// Estadísticas (mantener lógica existente)
const stats = computed(() => {
    const total = props.patients.length;
    const active = props.patients.filter(p => p.is_active).length;
    const withInsurance = props.patients.filter(p => p.health_insurance).length;
    const withoutInsurance = total - withInsurance;

    return { total, active, withInsurance, withoutInsurance };
});

// Funciones del modal (mantener lógica existente)
const openCreateModal = () => {
    editingPatient.value = null;
    modalOpen.value = true;
};

const openEditModal = (patient: Patient) => {
    editingPatient.value = patient;
    modalOpen.value = true;
};

const handleModalSuccess = () => {
    // Refrescar la página para obtener los datos actualizados
    router.reload({ only: ['patients'] });
};

// Funciones de acciones (mantener lógica existente)
const toggleStatus = (patient: Patient) => {
    const action = patient.is_active ? 'desactivar' : 'activar';
    if (confirm(`¿Estás seguro de ${action} este paciente?`)) {
        router.patch(`/patients/${patient.id}`, {
            is_active: !patient.is_active
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['patients'] });
            }
        });
    }
};

// Limpiar filtros
const clearFilters = () => {
    searchTerm.value = '';
    selectedHealthInsurance.value = 'all';
    selectedStatus.value = 'all';
    selectedAgeRange.value = 'all';
};
</script>

<style scoped>
/* Estilos adicionales para mejorar la tabla */
.table-row-hover {
    transition-property: background-color, color;
    transition-duration: 200ms;
}
.table-row-hover:hover {
    background-color: rgba(34, 197, 94, 0.3); /* green-50/30 */
}
@media (prefers-color-scheme: dark) {
    .table-row-hover:hover {
        background-color: rgba(20, 83, 45, 0.2); /* green-950/20 */
    }
}
</style>
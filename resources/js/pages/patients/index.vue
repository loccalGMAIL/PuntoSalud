<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    MoreHorizontal, 
    Eye, 
    Edit, 
    UserCheck, 
    UserX,
    Filter,
    Calendar,
    Heart
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Import del modal
import PatientModal from './PatientModal.vue';

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

// Estados reactivos para filtros
const searchTerm = ref('');
const selectedHealthInsurance = ref('all');
const selectedStatus = ref('all');
const selectedAgeRange = ref('all');

// Estados del modal
const modalOpen = ref(false);
const editingPatient = ref<Patient | null>(null);

// Opciones de filtros
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
const calculateAge = (birthDate: string): number => {
    const birth = new Date(birthDate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
};

// Función para obtener rango de edad
const getAgeRange = (age: number): string => {
    if (age < 18) return 'Menor';
    if (age <= 30) return '18-30';
    if (age <= 50) return '31-50';
    if (age <= 70) return '51-70';
    return '70+';
};

// Pacientes filtrados
const filteredPatients = computed(() => {
    return props.patients.filter(patient => {
        // Filtro por búsqueda (nombre completo, DNI, email)
        const searchMatch = searchTerm.value === '' || 
            `${patient.first_name} ${patient.last_name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            patient.dni.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            (patient.email && patient.email.toLowerCase().includes(searchTerm.value.toLowerCase()));

        // Filtro por obra social
        const insuranceMatch = selectedHealthInsurance.value === 'all' || 
            patient.health_insurance === selectedHealthInsurance.value ||
            (selectedHealthInsurance.value === 'sin_obra_social' && !patient.health_insurance);

        // Filtro por estado
        const statusMatch = selectedStatus.value === 'all' || 
            (selectedStatus.value === 'active' && patient.is_active) ||
            (selectedStatus.value === 'inactive' && !patient.is_active);

        // Filtro por rango de edad
        const ageMatch = selectedAgeRange.value === 'all' || 
            getAgeRange(calculateAge(patient.birth_date)) === selectedAgeRange.value;

        return searchMatch && insuranceMatch && statusMatch && ageMatch;
    });
});

// Estadísticas
const stats = computed(() => {
    const total = props.patients.length;
    const active = props.patients.filter(p => p.is_active).length;
    const inactive = total - active;
    const withInsurance = props.patients.filter(p => p.health_insurance).length;
    const withoutInsurance = total - withInsurance;

    return { total, active, inactive, withInsurance, withoutInsurance };
});

// Funciones del modal
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

// Funciones de acciones
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

<template>
    <Head title="Pacientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Header con estadísticas -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Pacientes</h1>
                        <p class="text-muted-foreground">
                            Gestiona los pacientes del centro médico
                        </p>
                    </div>
                    <Button @click="openCreateModal">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Paciente
                    </Button>
                </div>

                <!-- Cards de estadísticas -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total</CardTitle>
                            <Heart class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.total }}</div>
                            <p class="text-xs text-muted-foreground">
                                pacientes registrados
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Activos</CardTitle>
                            <UserCheck class="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                            <p class="text-xs text-muted-foreground">
                                pacientes activos
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Con Obra Social</CardTitle>
                            <Heart class="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-blue-600">{{ stats.withInsurance }}</div>
                            <p class="text-xs text-muted-foreground">
                                tienen cobertura médica
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Sin Obra Social</CardTitle>
                            <Heart class="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-orange-600">{{ stats.withoutInsurance }}</div>
                            <p class="text-xs text-muted-foreground">
                                sin cobertura médica
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-4 w-4" />
                        Filtros y Búsqueda
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Búsqueda -->
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium">Buscar</label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="Buscar por nombre, DNI o email..."
                                    class="pl-8"
                                />
                            </div>
                        </div>

                        <!-- Filtro por obra social -->
                        <div>
                            <label class="text-sm font-medium">Obra Social</label>
                            <Select v-model="selectedHealthInsurance">
                                <SelectTrigger>
                                    <SelectValue placeholder="Todas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todas</SelectItem>
                                    <SelectItem value="sin_obra_social">Sin obra social</SelectItem>
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
                            <label class="text-sm font-medium">Edad</label>
                            <Select v-model="selectedAgeRange">
                                <SelectTrigger>
                                    <SelectValue placeholder="Todas" />
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
                            <label class="text-sm font-medium">Estado</label>
                            <Select v-model="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todos</SelectItem>
                                    <SelectItem value="active">Activos</SelectItem>
                                    <SelectItem value="inactive">Inactivos</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Botón limpiar filtros y contador -->
                    <div class="flex justify-between items-center mt-4">
                        <div class="text-sm text-muted-foreground">
                            Mostrando {{ filteredPatients.length }} de {{ stats.total }} pacientes
                        </div>
                        <Button variant="outline" @click="clearFilters">
                            Limpiar filtros
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabla de pacientes -->
            <Card>
                <CardHeader>
                    <CardTitle>Lista de Pacientes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>DNI / Edad</TableHead>
                                    <TableHead>Contacto</TableHead>
                                    <TableHead>Obra Social</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filteredPatients.length === 0">
                                    <TableCell colspan="6" class="h-24 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <p class="text-muted-foreground">No se encontraron pacientes</p>
                                            <Button variant="outline" size="sm" @click="clearFilters">
                                                Limpiar filtros
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                
                                <TableRow 
                                    v-for="patient in filteredPatients" 
                                    :key="patient.id"
                                    :class="{ 'opacity-60': !patient.is_active }"
                                >
                                    <!-- Paciente -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ patient.first_name }} {{ patient.last_name }}
                                            </span>
                                            <span class="text-sm text-muted-foreground">
                                                {{ patient.email || 'Sin email' }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- DNI / Edad -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <Badge variant="outline" class="font-mono w-fit">
                                                {{ patient.dni }}
                                            </Badge>
                                            <span class="text-sm text-muted-foreground mt-1">
                                                {{ calculateAge(patient.birth_date) }} años
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- Contacto -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="text-sm">{{ patient.phone }}</span>
                                            <span class="text-xs text-muted-foreground">
                                                {{ patient.address || 'Sin dirección' }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- Obra Social -->
                                    <TableCell>
                                        <div v-if="patient.health_insurance" class="flex flex-col">
                                            <Badge variant="secondary" class="w-fit">
                                                {{ patient.health_insurance }}
                                            </Badge>
                                            <span v-if="patient.health_insurance_number" class="text-xs text-muted-foreground mt-1">
                                                N° {{ patient.health_insurance_number }}
                                            </span>
                                        </div>
                                        <span v-else class="text-sm text-muted-foreground">
                                            Sin obra social
                                        </span>
                                    </TableCell>

                                    <!-- Estado -->
                                    <TableCell>
                                        <Badge :variant="patient.is_active ? 'default' : 'destructive'">
                                            {{ patient.is_active ? 'Activo' : 'Inactivo' }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Acciones -->
                                    <TableCell class="text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" class="h-8 w-8 p-0">
                                                    <span class="sr-only">Abrir menú</span>
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="openEditModal(patient)">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Editar
                                                </DropdownMenuItem>
                                                
                                                <DropdownMenuItem 
                                                    @click="toggleStatus(patient)"
                                                    :class="patient.is_active ? 'text-red-600' : 'text-green-600'"
                                                >
                                                    <UserX v-if="patient.is_active" class="mr-2 h-4 w-4" />
                                                    <UserCheck v-else class="mr-2 h-4 w-4" />
                                                    {{ patient.is_active ? 'Desactivar' : 'Activar' }}
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modal de Paciente -->
        <PatientModal
            v-model:open="modalOpen"
            :patient="editingPatient"
            @success="handleModalSuccess"
        />
    </AppLayout>
</template>
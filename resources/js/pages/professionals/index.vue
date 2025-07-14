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
    Filter
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Import del modal
import ProfessionalModal from './ProfessionalModal.vue';
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

// Estados reactivos para filtros
const searchTerm = ref('');
const selectedSpecialty = ref('all');
const selectedStatus = ref('all');

// Estados del modal
const modalOpen = ref(false);
const editingProfessional = ref<Professional | null>(null);

// Opciones de filtros (ya no necesitamos calcularlas, vienen como prop)
const specialtyOptions = computed(() => props.specialties);

// Profesionales filtrados
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

// Estadísticas
const stats = computed(() => {
    const total = props.professionals.length;
    const active = props.professionals.filter(p => p.is_active).length;
    const inactive = total - active;
    const specialtiesCount = props.specialties.length;

    return { total, active, inactive, specialtiesCount };
});

// Funciones del modal
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

// Funciones de acciones
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

<template>
    <Head title="Profesionales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Header con estadísticas -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Profesionales</h1>
                        <p class="text-muted-foreground">
                            Gestiona los profesionales médicos del centro
                        </p>
                    </div>
                    <Button @click="openCreateModal">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Profesional
                    </Button>
                </div>

                <!-- Cards de estadísticas -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.total }}</div>
                            <p class="text-xs text-muted-foreground">
                                profesionales registrados
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Activos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
                            <p class="text-xs text-muted-foreground">
                                profesionales activos
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Inactivos</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">{{ stats.inactive }}</div>
                            <p class="text-xs text-muted-foreground">
                                profesionales inactivos
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Especialidades</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.specialtiesCount }}</div>
                            <p class="text-xs text-muted-foreground">
                                especialidades médicas
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
                    <div class="flex flex-col gap-4 md:flex-row md:items-end">
                        <!-- Búsqueda -->
                        <div class="flex-1">
                            <label class="text-sm font-medium">Buscar</label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="Buscar por nombre, email o matrícula..."
                                    class="pl-8"
                                />
                            </div>
                        </div>

                        <!-- Filtro por especialidad -->
                        <div class="min-w-[200px]">
                            <label class="text-sm font-medium">Especialidad</label>
                            <Select v-model="selectedSpecialty">
                                <SelectTrigger>
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
                        <div class="min-w-[150px]">
                            <label class="text-sm font-medium">Estado</label>
                            <Select v-model="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="Todos los estados" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todos</SelectItem>
                                    <SelectItem value="active">Activos</SelectItem>
                                    <SelectItem value="inactive">Inactivos</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Botón limpiar filtros -->
                        <Button variant="outline" @click="clearFilters">
                            Limpiar filtros
                        </Button>
                    </div>

                    <!-- Contador de resultados -->
                    <div class="mt-4 text-sm text-muted-foreground">
                        Mostrando {{ filteredProfessionals.length }} de {{ stats.total }} profesionales
                    </div>
                </CardContent>
            </Card>

            <!-- Tabla de profesionales -->
            <Card>
                <CardHeader>
                    <CardTitle>Lista de Profesionales</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Profesional</TableHead>
                                    <TableHead>Especialidad</TableHead>
                                    <TableHead>Contacto</TableHead>
                                    <TableHead>Matrícula</TableHead>
                                    <TableHead>Comisión</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filteredProfessionals.length === 0">
                                    <TableCell colspan="7" class="h-24 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <p class="text-muted-foreground">No se encontraron profesionales</p>
                                            <Button variant="outline" size="sm" @click="clearFilters">
                                                Limpiar filtros
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                
                                <TableRow 
                                    v-for="professional in filteredProfessionals" 
                                    :key="professional.id"
                                    :class="{ 'opacity-60': !professional.is_active }"
                                >
                                    <!-- Profesional -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ professional.first_name }} {{ professional.last_name }}
                                            </span>
                                            <span class="text-sm text-muted-foreground">
                                                {{ professional.email }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- Especialidad -->
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ professional.specialty.name }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Contacto -->
                                    <TableCell>
                                        <span class="text-sm">
                                            {{ professional.phone || 'Sin teléfono' }}
                                        </span>
                                    </TableCell>

                                    <!-- Matrícula -->
                                    <TableCell>
                                        <Badge variant="outline" class="font-mono">
                                            {{ professional.license_number }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Comisión -->
                                    <TableCell>
                                        <span class="font-medium">{{ professional.commission_percentage }}%</span>
                                    </TableCell>

                                    <!-- Estado -->
                                    <TableCell>
                                        <Badge :variant="professional.is_active ? 'default' : 'destructive'">
                                            {{ professional.is_active ? 'Activo' : 'Inactivo' }}
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
                                                <DropdownMenuItem @click="openEditModal(professional)">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Editar
                                                </DropdownMenuItem>
                                                
                                                <DropdownMenuItem 
                                                    @click="toggleStatus(professional)"
                                                    :class="professional.is_active ? 'text-red-600' : 'text-green-600'"
                                                >
                                                    <UserX v-if="professional.is_active" class="mr-2 h-4 w-4" />
                                                    <UserCheck v-else class="mr-2 h-4 w-4" />
                                                    {{ professional.is_active ? 'Desactivar' : 'Activar' }}
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

        <!-- Modal de Profesional -->
        <ProfessionalModal
            v-model:open="modalOpen"
            :professional="editingProfessional"
            :specialties="specialties"
            @success="handleModalSuccess"
        />
    </AppLayout>
</template>
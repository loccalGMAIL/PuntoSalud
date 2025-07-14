<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
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

// Opciones de filtros
const specialties = computed(() => {
    const uniqueSpecialties = props.professionals.reduce((acc, prof) => {
        if (!acc.find(s => s.id === prof.specialty.id)) {
            acc.push(prof.specialty);
        }
        return acc;
    }, [] as Specialty[]);
    return uniqueSpecialties;
});

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
    const specialtiesCount = specialties.value.length;

    return { total, active, inactive, specialtiesCount };
});

// Funciones de acciones
const toggleStatus = (professional: Professional) => {
    const action = professional.is_active ? 'desactivar' : 'activar';
    if (confirm(`¿Estás seguro de ${action} este profesional?`)) {
        router.patch(`/professionals/${professional.id}`, {
            is_active: !professional.is_active
        });
    }
};

const deleteProfessional = (professional: Professional) => {
    if (confirm(`¿Estás seguro de eliminar al profesional ${professional.first_name} ${professional.last_name}?`)) {
        router.delete(`/professionals/${professional.id}`);
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
                    <Button as-child>
                        <Link :href="route('professionals.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Nuevo Profesional
                        </Link>
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
                            <select 
                                v-model="selectedSpecialty" 
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="all">Todas las especialidades</option>
                                <option 
                                    v-for="specialty in specialties" 
                                    :key="specialty.id" 
                                    :value="specialty.id.toString()"
                                >
                                    {{ specialty.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtro por estado -->
                        <div class="min-w-[150px]">
                            <label class="text-sm font-medium">Estado</label>
                            <select 
                                v-model="selectedStatus"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="all">Todos</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                            </select>
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

            <!-- Lista de profesionales -->
            <Card>
                <CardHeader>
                    <CardTitle>Lista de Profesionales</CardTitle>
                </CardHeader>
                <CardContent>
                    <!-- Header de la tabla -->
                    <div class="hidden md:grid md:grid-cols-7 gap-4 p-4 border-b bg-muted/50 rounded-t-lg">
                        <div class="font-medium text-sm">Profesional</div>
                        <div class="font-medium text-sm">Especialidad</div>
                        <div class="font-medium text-sm">Contacto</div>
                        <div class="font-medium text-sm">Matrícula</div>
                        <div class="font-medium text-sm">Comisión</div>
                        <div class="font-medium text-sm">Estado</div>
                        <div class="font-medium text-sm text-right">Acciones</div>
                    </div>

                    <!-- Sin resultados -->
                    <div v-if="filteredProfessionals.length === 0" class="h-24 flex flex-col items-center justify-center">
                        <p class="text-muted-foreground">No se encontraron profesionales</p>
                        <Button variant="outline" size="sm" class="mt-2" @click="clearFilters">
                            Limpiar filtros
                        </Button>
                    </div>
                    
                    <!-- Lista de profesionales -->
                    <div v-else class="divide-y">
                        <div 
                            v-for="professional in filteredProfessionals" 
                            :key="professional.id"
                            :class="[
                                'grid grid-cols-1 md:grid-cols-7 gap-4 p-4 hover:bg-muted/30 transition-colors',
                                { 'opacity-60': !professional.is_active }
                            ]"
                        >
                            <!-- Profesional -->
                            <div class="flex flex-col">
                                <span class="font-medium">
                                    {{ professional.first_name }} {{ professional.last_name }}
                                </span>
                                <span class="text-sm text-muted-foreground">
                                    {{ professional.email }}
                                </span>
                                <!-- Mobile: mostrar info adicional -->
                                <div class="md:hidden mt-2 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium">Especialidad:</span>
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground">
                                            {{ professional.specialty.name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium">Estado:</span>
                                        <span :class="[
                                            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                            professional.is_active 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                        ]">
                                            {{ professional.is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Especialidad (solo desktop) -->
                            <div class="hidden md:flex">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-secondary text-secondary-foreground">
                                    {{ professional.specialty.name }}
                                </span>
                            </div>

                            <!-- Contacto (solo desktop) -->
                            <div class="hidden md:block">
                                <span class="text-sm">
                                    {{ professional.phone || 'Sin teléfono' }}
                                </span>
                            </div>

                            <!-- Matrícula (solo desktop) -->
                            <div class="hidden md:block">
                                <code class="text-sm bg-muted px-1 py-0.5 rounded">{{ professional.license_number }}</code>
                            </div>

                            <!-- Comisión (solo desktop) -->
                            <div class="hidden md:block">
                                <span class="font-medium">{{ professional.commission_percentage }}%</span>
                            </div>

                            <!-- Estado (solo desktop) -->
                            <div class="hidden md:flex">
                                <span :class="[
                                    'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                    professional.is_active 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' 
                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                ]">
                                    {{ professional.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <!-- Acciones -->
                            <div class="flex justify-end">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" class="h-8 w-8 p-0">
                                            <span class="sr-only">Abrir menú</span>
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem as-child>
                                            <Link :href="route('professionals.show', professional.id)">
                                                <Eye class="mr-2 h-4 w-4" />
                                                Ver detalles
                                            </Link>
                                        </DropdownMenuItem>
                                        
                                        <DropdownMenuItem as-child>
                                            <Link :href="route('professionals.edit', professional.id)">
                                                <Edit class="mr-2 h-4 w-4" />
                                                Editar
                                            </Link>
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
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
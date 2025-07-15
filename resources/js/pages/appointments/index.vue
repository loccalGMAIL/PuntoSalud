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
    Edit, 
    Calendar,
    Clock,
    User,
    CheckCircle,
    XCircle,
    AlertTriangle,
    Filter
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Import del modal
import AppointmentModal from './AppointmentModal.vue';

interface Professional {
    id: number;
    first_name: string;
    last_name: string;
    specialty: {
        name: string;
    };
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone: string;
}

interface Office {
    id: number;
    number: string;
    name: string;
}

interface Appointment {
    id: number;
    professional_id: number;
    patient_id: number;
    office_id: number | null;
    appointment_date: string;
    duration: string;
    status: 'scheduled' | 'attended' | 'cancelled' | 'absent';
    notes: string | null;
    amount: number | null;
    professional: Professional;
    patient: Patient;
    office: Office | null;
    created_at: string;
}

interface Props {
    appointments: Appointment[];
    professionals: Professional[];
    patients: Patient[];
    offices: Office[];
    filters: {
        start_date?: string;
        end_date?: string;
        professional_id?: number;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Agenda', href: '/appointments' },
];

// Estados reactivos para filtros
const searchTerm = ref('');
const selectedProfessional = ref(props.filters.professional_id?.toString() || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const startDate = ref(props.filters.start_date || new Date().toISOString().split('T')[0]);
const endDate = ref(props.filters.end_date || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);

// Estados del modal
const modalOpen = ref(false);
const editingAppointment = ref<Appointment | null>(null);

// Función para formatear fecha y hora
const formatDateTime = (dateTime: string) => {
    const date = new Date(dateTime);
    return {
        date: date.toLocaleDateString('es-AR'),
        time: date.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' }),
        dayName: date.toLocaleDateString('es-AR', { weekday: 'short' })
    };
};

// Función para formatear duración
const formatDuration = (duration: string) => {
    const parts = duration.split(':');
    const hours = parseInt(parts[0]);
    const minutes = parseInt(parts[1]);
    
    if (hours > 0) {
        return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
    }
    return `${minutes}m`;
};

// Función para obtener variante del badge según estado
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'scheduled': return 'default';
        case 'attended': return 'secondary';
        case 'cancelled': return 'destructive';
        case 'absent': return 'outline';
        default: return 'default';
    }
};

// Función para obtener texto del estado
const getStatusText = (status: string) => {
    switch (status) {
        case 'scheduled': return 'Programado';
        case 'attended': return 'Atendido';
        case 'cancelled': return 'Cancelado';
        case 'absent': return 'Ausente';
        default: return status;
    }
};

// Función para obtener ícono del estado
const getStatusIcon = (status: string) => {
    switch (status) {
        case 'scheduled': return Clock;
        case 'attended': return CheckCircle;
        case 'cancelled': return XCircle;
        case 'absent': return AlertTriangle;
        default: return Clock;
    }
};

// Turnos filtrados
const filteredAppointments = computed(() => {
    return props.appointments.filter(appointment => {
        // Filtro por búsqueda (paciente o profesional)
        const searchMatch = searchTerm.value === '' || 
            `${appointment.patient.first_name} ${appointment.patient.last_name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            `${appointment.professional.first_name} ${appointment.professional.last_name}`.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            appointment.patient.dni.includes(searchTerm.value);

        // Filtro por profesional
        const professionalMatch = selectedProfessional.value === 'all' || 
            appointment.professional_id.toString() === selectedProfessional.value;

        // Filtro por estado
        const statusMatch = selectedStatus.value === 'all' || 
            appointment.status === selectedStatus.value;

        return searchMatch && professionalMatch && statusMatch;
    });
});

// Estadísticas
const stats = computed(() => {
    const total = props.appointments.length;
    const scheduled = props.appointments.filter(a => a.status === 'scheduled').length;
    const attended = props.appointments.filter(a => a.status === 'attended').length;
    const cancelled = props.appointments.filter(a => a.status === 'cancelled').length;

    return { total, scheduled, attended, cancelled };
});

// Funciones del modal
const openCreateModal = () => {
    editingAppointment.value = null;
    modalOpen.value = true;
};

const openEditModal = (appointment: Appointment) => {
    editingAppointment.value = appointment;
    modalOpen.value = true;
};

const handleModalSuccess = () => {
    router.reload({ only: ['appointments'] });
};

// Función para cambiar estado del turno
const changeStatus = (appointment: Appointment, newStatus: string) => {
    if (confirm(`¿Cambiar estado del turno a "${getStatusText(newStatus)}"?`)) {
        router.patch(`/appointments/${appointment.id}`, {
            status: newStatus
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['appointments'] });
            }
        });
    }
};

// Función para aplicar filtros de fecha
const applyDateFilters = () => {
    router.get('/appointments', {
        start_date: startDate.value,
        end_date: endDate.value,
        professional_id: selectedProfessional.value !== 'all' ? selectedProfessional.value : undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
    }, {
        preserveState: true,
        replace: true
    });
};

// Limpiar filtros
const clearFilters = () => {
    searchTerm.value = '';
    selectedProfessional.value = 'all';
    selectedStatus.value = 'all';
    startDate.value = new Date().toISOString().split('T')[0];
    endDate.value = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    applyDateFilters();
};
</script>

<template>
    <Head title="Agenda de Turnos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Header con estadísticas -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Agenda de Turnos</h1>
                        <p class="text-muted-foreground">
                            Gestiona los turnos y la agenda del centro médico
                        </p>
                    </div>
                    <Button @click="openCreateModal">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Turno
                    </Button>
                </div>

                <!-- Cards de estadísticas -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total</CardTitle>
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.total }}</div>
                            <p class="text-xs text-muted-foreground">
                                turnos en el período
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Programados</CardTitle>
                            <Clock class="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-blue-600">{{ stats.scheduled }}</div>
                            <p class="text-xs text-muted-foreground">
                                por atender
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Atendidos</CardTitle>
                            <CheckCircle class="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">{{ stats.attended }}</div>
                            <p class="text-xs text-muted-foreground">
                                turnos completados
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Cancelados</CardTitle>
                            <XCircle class="h-4 w-4 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">{{ stats.cancelled }}</div>
                            <p class="text-xs text-muted-foreground">
                                turnos cancelados
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
                        Filtros de Agenda
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <!-- Fecha desde -->
                        <div>
                            <label class="text-sm font-medium">Desde</label>
                            <Input
                                v-model="startDate"
                                type="date"
                                @change="applyDateFilters"
                            />
                        </div>

                        <!-- Fecha hasta -->
                        <div>
                            <label class="text-sm font-medium">Hasta</label>
                            <Input
                                v-model="endDate"
                                type="date"
                                @change="applyDateFilters"
                            />
                        </div>

                        <!-- Búsqueda -->
                        <div>
                            <label class="text-sm font-medium">Buscar</label>
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="Paciente o profesional..."
                                    class="pl-8"
                                />
                            </div>
                        </div>

                        <!-- Filtro por profesional -->
                        <div>
                            <label class="text-sm font-medium">Profesional</label>
                            <Select v-model="selectedProfessional">
                                <SelectTrigger>
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Todos los profesionales</SelectItem>
                                    <SelectItem 
                                        v-for="professional in professionals" 
                                        :key="professional.id" 
                                        :value="professional.id.toString()"
                                    >
                                        Dr. {{ professional.first_name }} {{ professional.last_name }}
                                    </SelectItem>
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
                                    <SelectItem value="all">Todos los estados</SelectItem>
                                    <SelectItem value="scheduled">Programados</SelectItem>
                                    <SelectItem value="attended">Atendidos</SelectItem>
                                    <SelectItem value="cancelled">Cancelados</SelectItem>
                                    <SelectItem value="absent">Ausentes</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Botón limpiar filtros -->
                        <div class="flex items-end">
                            <Button variant="outline" @click="clearFilters" class="w-full">
                                Limpiar filtros
                            </Button>
                        </div>
                    </div>

                    <!-- Contador de resultados -->
                    <div class="mt-4 text-sm text-muted-foreground">
                        Mostrando {{ filteredAppointments.length }} de {{ stats.total }} turnos
                    </div>
                </CardContent>
            </Card>

            <!-- Tabla de turnos -->
            <Card>
                <CardHeader>
                    <CardTitle>Lista de Turnos</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha y Hora</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Profesional</TableHead>
                                    <TableHead>Duración</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Consultorio</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="filteredAppointments.length === 0">
                                    <TableCell colspan="7" class="h-24 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <p class="text-muted-foreground">No se encontraron turnos</p>
                                            <Button variant="outline" size="sm" @click="clearFilters">
                                                Limpiar filtros
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                                
                                <TableRow 
                                    v-for="appointment in filteredAppointments" 
                                    :key="appointment.id"
                                    :class="{ 'opacity-60': ['cancelled', 'absent'].includes(appointment.status) }"
                                >
                                    <!-- Fecha y Hora -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ formatDateTime(appointment.appointment_date).date }}
                                            </span>
                                            <span class="text-sm text-muted-foreground">
                                                {{ formatDateTime(appointment.appointment_date).dayName }} - {{ formatDateTime(appointment.appointment_date).time }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- Paciente -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ appointment.patient.first_name }} {{ appointment.patient.last_name }}
                                            </span>
                                            <span class="text-sm text-muted-foreground">
                                                DNI: {{ appointment.patient.dni }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <!-- Profesional -->
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                Dr. {{ appointment.professional.first_name }} {{ appointment.professional.last_name }}
                                            </span>
                                            <Badge variant="outline" class="w-fit text-xs">
                                                {{ appointment.professional.specialty.name }}
                                            </Badge>
                                        </div>
                                    </TableCell>

                                    <!-- Duración -->
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ formatDuration(appointment.duration) }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Estado -->
                                    <TableCell>
                                        <Badge :variant="getStatusVariant(appointment.status)" class="flex items-center gap-1 w-fit">
                                            <component :is="getStatusIcon(appointment.status)" class="h-3 w-3" />
                                            {{ getStatusText(appointment.status) }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Consultorio -->
                                    <TableCell>
                                        <span v-if="appointment.office" class="text-sm">
                                            {{ appointment.office.number }} - {{ appointment.office.name }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">
                                            Sin asignar
                                        </span>
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
                                                <DropdownMenuItem @click="openEditModal(appointment)">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Editar
                                                </DropdownMenuItem>
                                                
                                                <DropdownMenuItem 
                                                    v-if="appointment.status === 'scheduled'"
                                                    @click="changeStatus(appointment, 'attended')"
                                                    class="text-green-600"
                                                >
                                                    <CheckCircle class="mr-2 h-4 w-4" />
                                                    Marcar Atendido
                                                </DropdownMenuItem>
                                                
                                                <DropdownMenuItem 
                                                    v-if="appointment.status === 'scheduled'"
                                                    @click="changeStatus(appointment, 'absent')"
                                                    class="text-orange-600"
                                                >
                                                    <AlertTriangle class="mr-2 h-4 w-4" />
                                                    Marcar Ausente
                                                </DropdownMenuItem>
                                                
                                                <DropdownMenuItem 
                                                    v-if="appointment.status === 'scheduled'"
                                                    @click="changeStatus(appointment, 'cancelled')"
                                                    class="text-red-600"
                                                >
                                                    <XCircle class="mr-2 h-4 w-4" />
                                                    Cancelar
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

        <!-- Modal de Turno -->
        <AppointmentModal
            v-model:open="modalOpen"
            :appointment="editingAppointment"
            :professionals="professionals"
            :patients="patients"
            :offices="offices"
            @success="handleModalSuccess"
        />
    </AppLayout>
</template>
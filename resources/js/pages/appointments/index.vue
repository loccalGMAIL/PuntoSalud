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
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    MoreHorizontal, 
    Edit, 
    Calendar,
    Clock,
    CheckCircle,
    XCircle,
    AlertTriangle,
    Trash2
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

// Import del modal
import AppointmentModal from './AppointmentModal.vue';

interface Professional {
    id: number;
    first_name: string;
    last_name: string;
    specialty: {
        id: number;
        name: string;
    };
    is_active: boolean;
}

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    dni: string;
    phone: string;
    email?: string;
    is_active: boolean;
}

interface Office {
    id: number;
    number: string;
    name: string;
    is_active: boolean;
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
    confirmed_at: string | null;
    professional: Professional;
    patient: Patient;
    office: Office | null;
    created_at: string;
}

interface Stats {
    total: number;
    scheduled: number;
    attended: number;
    cancelled: number;
    absent: number;
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
        search?: string;
    };
    stats: Stats;
}

const props = defineProps<Props>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Inicio', href: '/dashboard' },
    { label: 'Agenda de Turnos', href: '/appointments' }
];

// Estado del modal
const modalOpen = ref(false);
const editingAppointment = ref<Appointment | null>(null);

// Filtros
const searchTerm = ref(props.filters.search || '');
const selectedProfessional = ref(props.filters.professional_id?.toString() || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const startDate = ref(props.filters.start_date || new Date().toISOString().split('T')[0]);
const endDate = ref(props.filters.end_date || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);

// Aplicar filtros
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (startDate.value) params.append('start_date', startDate.value);
    if (endDate.value) params.append('end_date', endDate.value);
    if (selectedProfessional.value !== 'all') params.append('professional_id', selectedProfessional.value);
    if (selectedStatus.value !== 'all') params.append('status', selectedStatus.value);
    if (searchTerm.value) params.append('search', searchTerm.value);

    router.get('/appointments', Object.fromEntries(params), {
        preserveState: true,
        replace: true
    });
};

// Debounced search
let searchTimeout: NodeJS.Timeout;
watch(searchTerm, (newValue) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
});

// Limpiar filtros
const clearFilters = () => {
    searchTerm.value = '';
    selectedProfessional.value = 'all';
    selectedStatus.value = 'all';
    startDate.value = new Date().toISOString().split('T')[0];
    endDate.value = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    applyFilters();
};

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
    modalOpen.value = false;
    editingAppointment.value = null;
    // Pequeño delay para asegurar que el modal se cierre completamente
    setTimeout(() => {
        router.reload({ only: ['appointments', 'stats'] });
    }, 200);
};

// Función para cambiar estado del turno
const changeStatus = (appointment: Appointment, newStatus: string) => {
    const statusMessages = {
        scheduled: 'Programado',
        attended: 'Atendido',
        cancelled: 'Cancelado',
        absent: 'Ausente'
    };

    if (confirm(`¿Cambiar estado del turno a "${statusMessages[newStatus]}"?`)) {
        router.patch(`/appointments/${appointment.id}`, {
            status: newStatus
        }, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['appointments', 'stats'] });
            }
        });
    }
};

// Función para eliminar/cancelar turno
const deleteAppointment = (appointment: Appointment) => {
    if (confirm('¿Estás seguro de que quieres cancelar este turno?')) {
        router.delete(`/appointments/${appointment.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['appointments', 'stats'] });
            }
        });
    }
};

// Utilidades de formato
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
};

const formatDuration = (duration: string) => {
    const parts = duration.split(':');
    const hours = parseInt(parts[0]);
    const minutes = parseInt(parts[1]);
    
    if (hours > 0) {
        return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
    }
    return `${minutes}m`;
};

// Utilidades de estado
const getStatusColor = (status: string) => {
    const colors = {
        scheduled: 'bg-blue-100 text-blue-800',
        attended: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        absent: 'bg-orange-100 text-orange-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    const icons = {
        scheduled: Clock,
        attended: CheckCircle,
        cancelled: XCircle,
        absent: AlertTriangle
    };
    return icons[status] || Clock;
};

const getStatusText = (status: string) => {
    const texts = {
        scheduled: 'Programado',
        attended: 'Atendido',
        cancelled: 'Cancelado',
        absent: 'Ausente'
    };
    return texts[status] || status;
};

// Verificar si se puede cambiar estado
const canChangeStatus = (appointment: Appointment) => {
    return appointment.status === 'scheduled';
};

// Opciones de estado disponibles
const getAvailableStatuses = (currentStatus: string) => {
    if (currentStatus === 'scheduled') {
        return ['attended', 'cancelled', 'absent'];
    }
    return [];
};
</script>

<template>
    <Head title="Agenda de Turnos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Agenda de Turnos</h1>
                    <p class="text-muted-foreground">
                        Gestiona los turnos y la agenda del centro médico
                    </p>
                </div>
                <Button @click="openCreateModal" class="bg-emerald-600 hover:bg-emerald-700">
                    <Plus class="mr-2 h-4 w-4" />
                    Nuevo Turno
                </Button>
            </div>

            <!-- Estadísticas simples -->
            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Total</div>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="text-2xl font-bold">{{ stats.total }}</div>
                    <p class="text-xs text-muted-foreground">turnos totales</p>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Programados</div>
                        <Clock class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="text-2xl font-bold text-blue-600">{{ stats.scheduled }}</div>
                    <p class="text-xs text-muted-foreground">pendientes</p>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Atendidos</div>
                        <CheckCircle class="h-4 w-4 text-green-600" />
                    </div>
                    <div class="text-2xl font-bold text-green-600">{{ stats.attended }}</div>
                    <p class="text-xs text-muted-foreground">completados</p>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Cancelados</div>
                        <XCircle class="h-4 w-4 text-red-600" />
                    </div>
                    <div class="text-2xl font-bold text-red-600">{{ stats.cancelled }}</div>
                    <p class="text-xs text-muted-foreground">cancelados</p>
                </div>
                
                <div class="rounded-lg border bg-card p-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-medium">Ausentes</div>
                        <AlertTriangle class="h-4 w-4 text-orange-600" />
                    </div>
                    <div class="text-2xl font-bold text-orange-600">{{ stats.absent }}</div>
                    <p class="text-xs text-muted-foreground">no asistieron</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="rounded-lg border bg-card p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-medium">Filtros</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <!-- Rango de fechas -->
                    <div class="md:col-span-2">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-sm font-medium">Desde</label>
                                <Input
                                    v-model="startDate"
                                    type="date"
                                    @change="applyFilters"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-medium">Hasta</label>
                                <Input
                                    v-model="endDate"
                                    type="date"
                                    @change="applyFilters"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda -->
                    <div>
                        <label class="text-sm font-medium">Buscar</label>
                        <div class="relative">
                            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchTerm"
                                placeholder="Paciente, DNI..."
                                class="pl-8"
                            />
                        </div>
                    </div>

                    <!-- Filtro por profesional -->
                    <div>
                        <label class="text-sm font-medium">Profesional</label>
                        <Select v-model="selectedProfessional" @update:model-value="applyFilters">
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
                        <Select v-model="selectedStatus" @update:model-value="applyFilters">
                            <SelectTrigger>
                                <SelectValue placeholder="Todos" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos los estados</SelectItem>
                                <SelectItem value="scheduled">Programado</SelectItem>
                                <SelectItem value="attended">Atendido</SelectItem>
                                <SelectItem value="cancelled">Cancelado</SelectItem>
                                <SelectItem value="absent">Ausente</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Botón limpiar -->
                    <div class="flex items-end">
                        <Button variant="outline" @click="clearFilters" class="w-full">
                            Limpiar Filtros
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Tabla de turnos -->
            <div class="rounded-lg border bg-card">
                <div class="p-4 border-b">
                    <h3 class="font-medium">Turnos ({{ appointments.length }})</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha/Hora</TableHead>
                                    <TableHead>Paciente</TableHead>
                                    <TableHead>Profesional</TableHead>
                                    <TableHead>Duración</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Monto</TableHead>
                                    <TableHead>Consultorio</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="appointment in appointments" 
                                    :key="appointment.id"
                                    class="hover:bg-muted/50"
                                >
                                    <TableCell class="font-medium">
                                        <div class="flex flex-col">
                                            <span>{{ formatDate(appointment.appointment_date) }}</span>
                                            <span class="text-sm text-muted-foreground">{{ formatTime(appointment.appointment_date) }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ appointment.patient.last_name }}, {{ appointment.patient.first_name }}</span>
                                            <span class="text-sm text-muted-foreground">{{ appointment.patient.dni }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span class="font-medium">Dr. {{ appointment.professional.first_name }} {{ appointment.professional.last_name }}</span>
                                            <span class="text-sm text-muted-foreground">{{ appointment.professional.specialty.name }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {{ formatDuration(appointment.duration) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getStatusColor(appointment.status)" class="flex items-center gap-1 w-fit">
                                            <component :is="getStatusIcon(appointment.status)" class="h-3 w-3" />
                                            {{ getStatusText(appointment.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="appointment.amount" class="font-medium">
                                            ${{ appointment.amount.toLocaleString() }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="appointment.office">
                                            {{ appointment.office.number }} - {{ appointment.office.name }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
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
                                                
                                                <!-- Cambios de estado -->
                                                <template v-if="canChangeStatus(appointment)">
                                                    <DropdownMenuItem 
                                                        v-for="status in getAvailableStatuses(appointment.status)"
                                                        :key="status"
                                                        @click="changeStatus(appointment, status)"
                                                    >
                                                        <component :is="getStatusIcon(status)" class="mr-2 h-4 w-4" />
                                                        Marcar como {{ getStatusText(status) }}
                                                    </DropdownMenuItem>
                                                </template>
                                                
                                                <!-- Cancelar turno -->
                                                <DropdownMenuItem 
                                                    v-if="appointment.status === 'scheduled'"
                                                    @click="deleteAppointment(appointment)"
                                                    class="text-red-600 focus:text-red-600"
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Cancelar Turno
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        
                        <!-- Estado vacío -->
                        <div v-if="appointments.length === 0" class="text-center py-8">
                            <Calendar class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-2 text-sm font-semibold">No hay turnos</h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                No se encontraron turnos para los filtros seleccionados.
                            </p>
                            <div class="mt-6">
                                <Button @click="openCreateModal" class="bg-emerald-600 hover:bg-emerald-700">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Crear Primer Turno
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de turno -->
        <AppointmentModal
            :open="modalOpen"
            :appointment="editingAppointment"
            :professionals="professionals"
            :patients="patients"
            :offices="offices"
            @update:open="modalOpen = $event"
            @success="handleModalSuccess"
        />
    </AppLayout>
</template>
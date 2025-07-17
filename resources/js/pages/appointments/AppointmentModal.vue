<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { router, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Calendar, Clock, User, Building, DollarSign, Info } from 'lucide-vue-next';
import { computed, watch, ref } from 'vue';

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
    status: string;
    notes: string | null;
    amount: number | null;
}

interface Props {
    open: boolean;
    appointment?: Appointment | null;
    professionals: Professional[];
    patients: Patient[];
    offices: Office[];
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}

const props = withDefaults(defineProps<Props>(), {
    appointment: null
});

const emit = defineEmits<Emits>();

// Estado
const loadingSlots = ref(false);
const availableSlots = ref<string[]>([]);
const selectedSlot = ref<string>('');

// Determinar si es edición o creación
const isEditing = computed(() => !!props.appointment);
const title = computed(() => isEditing.value ? 'Editar Turno' : 'Nuevo Turno');

// Opciones de duración (en minutos)
const durationOptions = [
    { value: '15', label: '15 minutos' },
    { value: '30', label: '30 minutos' },
    { value: '45', label: '45 minutos' },
    { value: '60', label: '1 hora' },
    { value: '90', label: '1 hora 30 min' },
    { value: '120', label: '2 horas' }
];

// Opciones de estado (solo para edición)
const statusOptions = [
    { value: 'scheduled', label: 'Programado' },
    { value: 'attended', label: 'Atendido' },
    { value: 'cancelled', label: 'Cancelado' },
    { value: 'absent', label: 'Ausente' }
];

// Formulario con Inertia - CORREGIDO: cambié valores iniciales
const form = useForm({
    professional_id: null,
    patient_id: null,
    appointment_date: '',
    appointment_time: '',
    duration: '30',
    office_id: '0',  // Mantener como '0' que es válido
    notes: '',
    amount: null,    // CORREGIDO: null en lugar de ''
    status: 'scheduled',
});

// Función para resetear el formulario - CORREGIDO
const resetForm = () => {
    form.reset();
    form.clearErrors();
    availableSlots.value = [];
    selectedSlot.value = '';
    
    // CORREGIDO: Restablecer valores por defecto sin cadenas vacías
    form.professional_id = null;
    form.patient_id = null;
    form.appointment_date = '';
    form.appointment_time = '';
    form.duration = '30';
    form.office_id = '0';
    form.notes = '';
    form.amount = null;  // CORREGIDO: null en lugar de ''
    form.status = 'scheduled';
};

// Función para cargar datos del turno en edición - CORREGIDO
const loadAppointmentData = () => {
    if (props.appointment) {
        const appointmentDate = new Date(props.appointment.appointment_date);
        
        form.professional_id = props.appointment.professional_id.toString();
        form.patient_id = props.appointment.patient_id.toString();
        form.appointment_date = appointmentDate.toISOString().split('T')[0];
        form.appointment_time = appointmentDate.toTimeString().slice(0, 5);
        form.duration = durationStringToMinutes(props.appointment.duration).toString();
        form.office_id = props.appointment.office_id?.toString() || '0'; // CORREGIDO: usar '0' en lugar de ''
        form.notes = props.appointment.notes || '';
        form.amount = props.appointment.amount || null; // CORREGIDO: null en lugar de ''
        form.status = props.appointment.status;
    }
};

// Watcher para cargar datos cuando se abre el modal
watch(() => props.open, (newValue) => {
    if (newValue) {
        if (isEditing.value) {
            loadAppointmentData();
        } else {
            resetForm();
            // Establecer fecha mínima como hoy
            const today = new Date();
            form.appointment_date = today.toISOString().split('T')[0];
        }
    } else {
        resetForm();
    }
});

// Cargar slots disponibles cuando cambian los datos relevantes - DESHABILITADO TEMPORALMENTE
// watch([() => form.professional_id, () => form.appointment_date, () => form.duration], 
//     ([professionalId, date, duration]) => {
//         if (professionalId && date && duration && props.open && !loadingSlots.value) {
//             loadAvailableSlots();
//         }
//     }, { 
//         immediate: false,
//         deep: false 
//     }
// );

// Función para cargar slots disponibles
const loadAvailableSlots = async () => {
    if (!form.professional_id || !form.appointment_date || !form.duration || loadingSlots.value) {
        availableSlots.value = [];
        return;
    }

    loadingSlots.value = true;
    
    try {
        const response = await fetch(`/appointments/available-slots?professional_id=${form.professional_id}&date=${form.appointment_date}&duration=${form.duration}`);
        
        if (!response.ok) {
            throw new Error('Error al cargar horarios');
        }
        
        const slots = await response.json();
        availableSlots.value = Array.isArray(slots) ? slots : [];
        
        // Si estamos editando y el horario actual está disponible, preseleccionarlo
        if (isEditing.value && form.appointment_time) {
            const currentTime = form.appointment_time;
            if (slots.includes(currentTime)) {
                selectedSlot.value = currentTime;
            }
        }
    } catch (error) {
        console.error('Error loading available slots:', error);
        availableSlots.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

// Función para enviar el formulario
const submitForm = () => {
    // Validaciones básicas
    if (!form.professional_id || !form.patient_id || !form.appointment_date || !form.duration) {
        alert('Por favor complete todos los campos obligatorios');
        return;
    }

    // Usar el slot seleccionado o el tiempo manual
    const appointmentTime = selectedSlot.value || form.appointment_time;
    
    if (!appointmentTime) {
        alert('Debe seleccionar un horario');
        return;
    }

    // Actualizar el form directamente
    form.appointment_time = appointmentTime;
    form.duration = parseInt(form.duration);
    
    if (form.office_id && form.office_id !== '0') {
        form.office_id = parseInt(form.office_id);
    } else {
        form.office_id = null;
    }
    
    if (form.amount) {
        form.amount = parseFloat(form.amount);
    } else {
        form.amount = null;
    }

    if (isEditing.value) {
        // Actualizar turno existente
        form.patch(route('appointments.update', props.appointment!.id), {
            onSuccess: () => {
                emit('success');
                emit('update:open', false);
            },
            onError: (errors) => {
                console.log('Error al actualizar:', errors);
            },
            preserveScroll: true,
        });
    } else {
        // Crear nuevo turno
        form.post(route('appointments.store'), {
            onSuccess: () => {
                emit('success');
                emit('update:open', false);
            },
            onError: (errors) => {
                console.log('Error al crear:', errors);
            },
            preserveScroll: true,
        });
    }
};

// Utilidades
const durationStringToMinutes = (duration: string): number => {
    const parts = duration.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
};

// Profesional seleccionado
const selectedProfessional = computed(() => {
    if (!form.professional_id) return null;
    return props.professionals.find(p => p.id.toString() === form.professional_id);
});

// Paciente seleccionado
const selectedPatient = computed(() => {
    if (!form.patient_id) return null;
    return props.patients.find(p => p.id.toString() === form.patient_id);
});

// Fecha mínima (hoy para nuevos turnos)
const minDate = computed(() => {
    if (isEditing.value) return undefined;
    return new Date().toISOString().split('T')[0];
});

// Seleccionar slot
const selectSlot = (slot: string) => {
    selectedSlot.value = slot;
    form.appointment_time = slot;
};
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Calendar class="h-5 w-5" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Modifica los datos del turno' : 'Programa un nuevo turno para el paciente' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Profesional -->
                <div class="space-y-2">
                    <Label for="professional_id">Profesional *</Label>
                    <Select v-model="form.professional_id" required>
                        <SelectTrigger>
                            <SelectValue placeholder="Seleccionar profesional..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="professional in professionals" 
                                :key="professional.id"
                                :value="professional.id.toString()"
                            >
                                Dr. {{ professional.first_name }} {{ professional.last_name }} - {{ professional.specialty.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.professional_id" class="text-sm text-red-600">
                        {{ form.errors.professional_id }}
                    </div>
                </div>

                <!-- Paciente -->
                <div class="space-y-2">
                    <Label for="patient_id">Paciente *</Label>
                    <Select v-model="form.patient_id" required>
                        <SelectTrigger>
                            <SelectValue placeholder="Seleccionar paciente..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="patient in patients" 
                                :key="patient.id"
                                :value="patient.id.toString()"
                            >
                                {{ patient.last_name }}, {{ patient.first_name }} - {{ patient.dni }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.patient_id" class="text-sm text-red-600">
                        {{ form.errors.patient_id }}
                    </div>
                </div>

                <!-- Fecha y Duración -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="appointment_date">Fecha *</Label>
                        <Input
                            id="appointment_date"
                            v-model="form.appointment_date"
                            type="date"
                            :min="minDate"
                            required
                        />
                        <div v-if="form.errors.appointment_date" class="text-sm text-red-600">
                            {{ form.errors.appointment_date }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="duration">Duración *</Label>
                        <Select v-model="form.duration" required>
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar duración..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="option in durationOptions" 
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="form.errors.duration" class="text-sm text-red-600">
                            {{ form.errors.duration }}
                        </div>
                    </div>
                </div>

                <!-- Horarios disponibles -->
                <div v-if="form.professional_id && form.appointment_date && form.duration" class="space-y-2">
                    <Label>Horarios Disponibles</Label>
                    <div v-if="loadingSlots" class="flex items-center gap-2 text-sm text-muted-foreground">
                        <LoaderCircle class="h-4 w-4 animate-spin" />
                        Cargando horarios...
                    </div>
                    <div v-else-if="availableSlots.length > 0" class="grid grid-cols-6 gap-2">
                        <Button
                            v-for="slot in availableSlots"
                            :key="slot"
                            type="button"
                            variant="outline"
                            :class="selectedSlot === slot ? 'bg-emerald-100 border-emerald-500' : ''"
                            @click="selectSlot(slot)"
                            class="h-8 text-xs"
                        >
                            {{ slot }}
                        </Button>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">
                        No hay horarios disponibles para esta fecha y duración.
                    </div>
                </div>

                <!-- Horario manual -->
                <div class="space-y-2">
                    <Label for="appointment_time">Horario (manual)</Label>
                    <Input
                        id="appointment_time"
                        v-model="form.appointment_time"
                        type="time"
                        min="08:00"
                        max="18:00"
                        step="900"
                    />
                    <div v-if="form.errors.appointment_time" class="text-sm text-red-600">
                        {{ form.errors.appointment_time }}
                    </div>
                </div>

                <!-- Consultorio y Monto -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="office_id">Consultorio</Label>
                        <Select v-model="form.office_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Seleccionar consultorio..." />
                            </SelectTrigger>
                            <SelectContent>
                                <!-- CORREGIDO: Cambié value="" por value="0" -->
                                <SelectItem value="0">Sin consultorio</SelectItem>
                                <SelectItem 
                                    v-for="office in offices" 
                                    :key="office.id"
                                    :value="office.id.toString()"
                                >
                                    {{ office.number }} - {{ office.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <div v-if="form.errors.office_id" class="text-sm text-red-600">
                            {{ form.errors.office_id }}
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="amount">Monto</Label>
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                        />
                        <div v-if="form.errors.amount" class="text-sm text-red-600">
                            {{ form.errors.amount }}
                        </div>
                    </div>
                </div>

                <!-- Estado (solo para edición) -->
                <div v-if="isEditing" class="space-y-2">
                    <Label for="status">Estado</Label>
                    <Select v-model="form.status">
                        <SelectTrigger>
                            <SelectValue placeholder="Seleccionar estado..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem 
                                v-for="option in statusOptions" 
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.status" class="text-sm text-red-600">
                        {{ form.errors.status }}
                    </div>
                </div>

                <!-- Notas -->
                <div class="space-y-2">
                    <Label for="notes">Notas</Label>
                    <Input
                        id="notes"
                        v-model="form.notes"
                        placeholder="Notas adicionales sobre el turno..."
                    />
                    <div v-if="form.errors.notes" class="text-sm text-red-600">
                        {{ form.errors.notes }}
                    </div>
                </div>

                <!-- Información -->
                <div class="bg-blue-50 p-3 rounded-lg">
                    <div class="flex items-center gap-2 text-sm text-blue-800">
                        <Info class="h-4 w-4" />
                        <span><strong>Horario laboral:</strong> De 8:00 a 18:00, lunes a viernes.</span>
                    </div>
                </div>

                <!-- Errores generales -->
                <div v-if="form.errors.error" class="text-sm text-red-600 bg-red-50 p-3 rounded">
                    {{ form.errors.error }}
                </div>
            </form>

            <DialogFooter>
                <Button 
                    type="button" 
                    variant="outline" 
                    @click="emit('update:open', false)"
                    :disabled="form.processing"
                >
                    Cancelar
                </Button>
                <Button 
                    @click="submitForm"
                    :disabled="form.processing"
                    class="bg-emerald-600 hover:bg-emerald-700"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isEditing ? 'Actualizar' : 'Crear' }} Turno
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
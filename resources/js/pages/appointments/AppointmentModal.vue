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
import { router, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Calendar, Clock, User, Building } from 'lucide-vue-next';
import { computed, watch, ref } from 'vue';

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

// Determinar si es edición o creación
const isEditing = computed(() => !!props.appointment);
const title = computed(() => isEditing.value ? 'Editar Turno' : 'Nuevo Turno');
const description = computed(() => 
    isEditing.value 
        ? 'Modifica los datos del turno' 
        : 'Programa un nuevo turno para el paciente'
);

// Búsqueda de pacientes
const patientSearch = ref('');
const filteredPatients = computed(() => {
    if (!patientSearch.value) return props.patients.slice(0, 10); // Mostrar solo los primeros 10
    
    return props.patients.filter(patient => 
        `${patient.first_name} ${patient.last_name}`.toLowerCase().includes(patientSearch.value.toLowerCase()) ||
        patient.dni.includes(patientSearch.value)
    ).slice(0, 10);
});

// Opciones de duración
const durationOptions = [
    { value: '00:15:00', label: '15 minutos' },
    { value: '00:30:00', label: '30 minutos' },
    { value: '00:45:00', label: '45 minutos' },
    { value: '01:00:00', label: '1 hora' },
    { value: '01:30:00', label: '1 hora 30 min' },
    { value: '02:00:00', label: '2 horas' }
];

// Opciones de estado (solo para edición)
const statusOptions = [
    { value: 'scheduled', label: 'Programado' },
    { value: 'attended', label: 'Atendido' },
    { value: 'cancelled', label: 'Cancelado' },
    { value: 'absent', label: 'Ausente' }
];

// Formulario con Inertia
const form = useForm({
    professional_id: '',
    patient_id: '',
    appointment_date: '',
    appointment_time: '',
    duration: '00:30:00',
    office_id: '',
    notes: '',
    amount: '',
    status: 'scheduled',
});

// Función para resetear el formulario
const resetForm = () => {
    form.reset();
    form.clearErrors();
    patientSearch.value = '';
};

// Función para cargar datos del turno en edición
const loadAppointmentData = () => {
    if (props.appointment) {
        const appointmentDate = new Date(props.appointment.appointment_date);
        
        form.professional_id = props.appointment.professional_id.toString();
        form.patient_id = props.appointment.patient_id.toString();
        form.appointment_date = appointmentDate.toISOString().split('T')[0];
        form.appointment_time = appointmentDate.toTimeString().slice(0, 5);
        form.duration = props.appointment.duration;
        form.office_id = props.appointment.office_id?.toString() || '';
        form.notes = props.appointment.notes || '';
        form.amount = props.appointment.amount?.toString() || '';
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
        // Resetear cuando se cierra el modal
        resetForm();
    }
});

// Función para cerrar el modal
// const closeModal = () => {
//     resetForm();
// };

// Función para enviar el formulario
const submitForm = () => {
    // Combinar fecha y hora
    const appointmentDateTime = `${form.appointment_date}T${form.appointment_time}:00`;
    
    // const data = {
    //     professional_id: parseInt(form.professional_id),
    //     patient_id: parseInt(form.patient_id),
    //     appointment_date: appointmentDateTime,
    //     duration: form.duration,
    //     office_id: form.office_id ? parseInt(form.office_id) : null,
    //     notes: form.notes || null,
    //     amount: form.amount ? parseFloat(form.amount) : null,
    //     ...(isEditing.value && { status: form.status })
    // };

    if (isEditing.value) {
        // Actualizar turno existente
        form.patch(route('appointments.update', props.appointment!.id), {
            onSuccess: () => {
                emit('success');
                emit('update:open', false);
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
            preserveScroll: true,
        });
    }
};

// Profesional seleccionado para mostrar especialidad
const selectedProfessional = computed(() => {
    if (!form.professional_id) return null;
    return props.professionals.find(p => p.id.toString() === form.professional_id);
});

// Paciente seleccionado para mostrar datos
const selectedPatient = computed(() => {
    if (!form.patient_id) return null;
    return props.patients.find(p => p.id.toString() === form.patient_id);
});

// Fecha mínima (hoy)
const minDate = computed(() => {
    return new Date().toISOString().split('T')[0];
});

// Validación de horario (horario laboral básico)
const isValidTime = computed(() => {
    if (!form.appointment_time) return true;
    
    const time = form.appointment_time;
    const [hours, minutes] = time.split(':').map(Number);
    const totalMinutes = hours * 60 + minutes;
    
    // Horario de 8:00 a 18:00
    return totalMinutes >= 8 * 60 && totalMinutes <= 18 * 60;
});
</script>

<template>
    <Dialog :open="open" @update:open="(value) => emit('update:open', value)">
        <DialogContent class="sm:max-w-3xl max-h-[85vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Calendar class="h-5 w-5" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Información del Turno -->
                <div>
                    <h3 class="text-sm font-medium mb-3 flex items-center gap-2">
                        <Calendar class="h-4 w-4" />
                        Información del Turno
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="appointment_date">Fecha *</Label>
                            <Input
                                id="appointment_date"
                                v-model="form.appointment_date"
                                type="date"
                                :min="isEditing ? undefined : minDate"
                                :class="{ 'border-red-500': form.errors.appointment_date }"
                                required
                            />
                            <p v-if="form.errors.appointment_date" class="text-sm text-red-600">
                                {{ form.errors.appointment_date }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="appointment_time">Hora *</Label>
                            <Input
                                id="appointment_time"
                                v-model="form.appointment_time"
                                type="time"
                                step="900"
                                :class="{ 
                                    'border-red-500': form.errors.appointment_time || !isValidTime,
                                    'border-yellow-500': form.appointment_time && !isValidTime
                                }"
                                required
                            />
                            <p v-if="!isValidTime && form.appointment_time" class="text-sm text-yellow-600">
                                Horario fuera del horario laboral (8:00 - 18:00)
                            </p>
                            <p v-if="form.errors.appointment_time" class="text-sm text-red-600">
                                {{ form.errors.appointment_time }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="duration">Duración *</Label>
                            <Select v-model="form.duration" required>
                                <SelectTrigger :class="{ 'border-red-500': form.errors.duration }">
                                    <SelectValue placeholder="Selecciona duración" />
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
                            <p v-if="form.errors.duration" class="text-sm text-red-600">
                                {{ form.errors.duration }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Profesional -->
                <div>
                    <h3 class="text-sm font-medium mb-3 flex items-center gap-2">
                        <User class="h-4 w-4" />
                        Profesional
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="professional">Profesional *</Label>
                            <Select v-model="form.professional_id" required>
                                <SelectTrigger :class="{ 'border-red-500': form.errors.professional_id }">
                                    <SelectValue placeholder="Selecciona profesional" />
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
                            <p v-if="form.errors.professional_id" class="text-sm text-red-600">
                                {{ form.errors.professional_id }}
                            </p>
                        </div>

                        <div v-if="selectedProfessional" class="space-y-2">
                            <Label>Especialidad</Label>
                            <div class="p-2 bg-muted/50 rounded-md">
                                <span class="text-sm font-medium">{{ selectedProfessional.specialty.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paciente -->
                <div>
                    <h3 class="text-sm font-medium mb-3 flex items-center gap-2">
                        <User class="h-4 w-4" />
                        Paciente
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="patient">Paciente *</Label>
                            <div class="space-y-2">
                                <Input
                                    v-model="patientSearch"
                                    placeholder="Buscar paciente por nombre o DNI..."
                                    class="mb-2"
                                />
                                <Select v-model="form.patient_id" required>
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.patient_id }">
                                        <SelectValue placeholder="Selecciona paciente" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem 
                                            v-for="patient in filteredPatients" 
                                            :key="patient.id" 
                                            :value="patient.id.toString()"
                                        >
                                            {{ patient.first_name }} {{ patient.last_name }} - {{ patient.dni }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <p v-if="form.errors.patient_id" class="text-sm text-red-600">
                                {{ form.errors.patient_id }}
                            </p>
                        </div>

                        <div v-if="selectedPatient" class="space-y-2">
                            <Label>Datos del Paciente</Label>
                            <div class="p-3 bg-muted/50 rounded-md space-y-1">
                                <div class="text-sm">
                                    <span class="font-medium">{{ selectedPatient.first_name }} {{ selectedPatient.last_name }}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    DNI: {{ selectedPatient.dni }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Tel: {{ selectedPatient.phone }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalles Adicionales -->
                <div>
                    <h3 class="text-sm font-medium mb-3 flex items-center gap-2">
                        <Building class="h-4 w-4" />
                        Detalles Adicionales
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="office">Consultorio</Label>
                            <Select v-model="form.office_id">
                                <SelectTrigger :class="{ 'border-red-500': form.errors.office_id }">
                                    <SelectValue placeholder="Sin asignar" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Sin asignar</SelectItem>
                                    <SelectItem 
                                        v-for="office in offices" 
                                        :key="office.id" 
                                        :value="office.id.toString()"
                                    >
                                        {{ office.number }} - {{ office.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.office_id" class="text-sm text-red-600">
                                {{ form.errors.office_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="amount">Importe ($)</Label>
                            <Input
                                id="amount"
                                v-model="form.amount"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                :class="{ 'border-red-500': form.errors.amount }"
                            />
                            <p v-if="form.errors.amount" class="text-sm text-red-600">
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <!-- Estado (solo en edición) -->
                        <div v-if="isEditing" class="space-y-2">
                            <Label for="status">Estado</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="status in statusOptions" 
                                        :key="status.value" 
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <div class="space-y-2">
                        <Label for="notes">Notas del Turno</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="2"
                            placeholder="Observaciones, motivo de la consulta, preparación especial..."
                            :class="[
                                'flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                                { 'border-red-500': form.errors.notes }
                            ]"
                        ></textarea>
                        <p v-if="form.errors.notes" class="text-sm text-red-600">
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </div>
            </form>

            <DialogFooter class="flex gap-3 pt-6 border-t">
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
                    :disabled="form.processing || !isValidTime"
                    class="min-w-[120px]"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isEditing ? 'Actualizar Turno' : 'Crear Turno' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
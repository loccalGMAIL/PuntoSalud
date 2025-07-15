<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { router, useForm } from '@inertiajs/vue3';
import { LoaderCircle, UserPlus, Edit, Calendar } from 'lucide-vue-next';
import { computed, watch } from 'vue';

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
}

interface Props {
    open: boolean;
    patient?: Patient | null;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}

const props = withDefaults(defineProps<Props>(), {
    patient: null
});

const emit = defineEmits<Emits>();

// Determinar si es edición o creación
const isEditing = computed(() => !!props.patient);
const title = computed(() => isEditing.value ? 'Editar Paciente' : 'Nuevo Paciente');
const description = computed(() => 
    isEditing.value 
        ? 'Modifica los datos del paciente' 
        : 'Completa la información para registrar un nuevo paciente'
);

// Formulario con Inertia
const form = useForm({
    first_name: '',
    last_name: '',
    dni: '',
    birth_date: '',
    email: '',
    phone: '',
    address: '',
    health_insurance: '',
    health_insurance_number: '',
    medical_notes: '',
    is_active: true,
});

// Función para resetear el formulario
const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Función para cargar datos del paciente en edición
const loadPatientData = () => {
    if (props.patient) {
        form.first_name = props.patient.first_name;
        form.last_name = props.patient.last_name;
        form.dni = props.patient.dni;
        form.birth_date = props.patient.birth_date;
        form.email = props.patient.email || '';
        form.phone = props.patient.phone;
        form.address = props.patient.address || '';
        form.health_insurance = props.patient.health_insurance || '';
        form.health_insurance_number = props.patient.health_insurance_number || '';
        form.medical_notes = props.patient.medical_notes || '';
        form.is_active = props.patient.is_active;
    }
};

// Watcher para cargar datos cuando se abre el modal
watch(() => props.open, (newValue) => {
    if (newValue) {
        if (isEditing.value) {
            loadPatientData();
        } else {
            resetForm();
        }
    }
});

// Función para cerrar el modal
const closeModal = () => {
    emit('update:open', false);
    resetForm();
};

// Función para enviar el formulario
const submitForm = () => {
    const data = {
        first_name: form.first_name,
        last_name: form.last_name,
        dni: form.dni,
        birth_date: form.birth_date,
        email: form.email || null,
        phone: form.phone,
        address: form.address || null,
        health_insurance: form.health_insurance || null,
        health_insurance_number: form.health_insurance_number || null,
        medical_notes: form.medical_notes || null,
        ...(isEditing.value && { is_active: form.is_active })
    };

    if (isEditing.value) {
        // Actualizar paciente existente
        form.patch(route('patients.update', props.patient!.id), {
            onSuccess: () => {
                emit('success');
                closeModal();
            },
            preserveScroll: true,
        });
    } else {
        // Crear nuevo paciente
        form.post(route('patients.store'), {
            onSuccess: () => {
                emit('success');
                closeModal();
            },
            preserveScroll: true,
        });
    }
};

// Función para calcular la edad
const calculateAge = computed(() => {
    if (!form.birth_date) return '';
    
    const birth = new Date(form.birth_date);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age >= 0 ? `${age} años` : '';
});

// Máxima fecha permitida (hoy)
const maxDate = computed(() => {
    return new Date().toISOString().split('T')[0];
});
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent class="sm:max-w-3xl max-h-[85vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <UserPlus v-if="!isEditing" class="h-5 w-5" />
                    <Edit v-else class="h-5 w-5" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Información Personal -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <Label for="first_name">Nombre *</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                placeholder="Juan"
                                :class="{ 'border-red-500': form.errors.first_name }"
                                required
                            />
                            <p v-if="form.errors.first_name" class="text-sm text-red-600">
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="last_name">Apellido *</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                placeholder="Pérez"
                                :class="{ 'border-red-500': form.errors.last_name }"
                                required
                            />
                            <p v-if="form.errors.last_name" class="text-sm text-red-600">
                                {{ form.errors.last_name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="dni">DNI *</Label>
                            <Input
                                id="dni"
                                v-model="form.dni"
                                type="text"
                                placeholder="12.345.678"
                                :class="{ 'border-red-500': form.errors.dni }"
                                required
                            />
                            <p v-if="form.errors.dni" class="text-sm text-red-600">
                                {{ form.errors.dni }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="birth_date">Fecha de Nacimiento *</Label>
                            <div class="relative">
                                <Input
                                    id="birth_date"
                                    v-model="form.birth_date"
                                    type="date"
                                    :max="maxDate"
                                    :class="{ 'border-red-500': form.errors.birth_date }"
                                    required
                                />
                                <Calendar class="absolute right-2 top-2.5 h-4 w-4 text-muted-foreground pointer-events-none" />
                            </div>
                            <p v-if="calculateAge" class="text-xs text-muted-foreground">
                                {{ calculateAge }}
                            </p>
                            <p v-if="form.errors.birth_date" class="text-sm text-red-600">
                                {{ form.errors.birth_date }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información de Contacto</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label for="phone">Teléfono *</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="+54 11 1234-5678"
                                :class="{ 'border-red-500': form.errors.phone }"
                                required
                            />
                            <p v-if="form.errors.phone" class="text-sm text-red-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="juan.perez@ejemplo.com"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="text-sm text-red-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Dirección</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                type="text"
                                placeholder="Av. Corrientes 1234"
                                :class="{ 'border-red-500': form.errors.address }"
                            />
                            <p v-if="form.errors.address" class="text-sm text-red-600">
                                {{ form.errors.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información de Obra Social -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Obra Social</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="health_insurance">Obra Social</Label>
                            <Input
                                id="health_insurance"
                                v-model="form.health_insurance"
                                type="text"
                                placeholder="Ej: OSDE, Swiss Medical, PAMI"
                                :class="{ 'border-red-500': form.errors.health_insurance }"
                            />
                            <p v-if="form.errors.health_insurance" class="text-sm text-red-600">
                                {{ form.errors.health_insurance }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="health_insurance_number">Número de Afiliado</Label>
                            <Input
                                id="health_insurance_number"
                                v-model="form.health_insurance_number"
                                type="text"
                                placeholder="123456789"
                                :class="{ 'border-red-500': form.errors.health_insurance_number }"
                            />
                            <p v-if="form.errors.health_insurance_number" class="text-sm text-red-600">
                                {{ form.errors.health_insurance_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información Médica -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información Médica</h3>
                    <div class="space-y-2">
                        <Label for="medical_notes">Notas Médicas</Label>
                        <textarea
                            id="medical_notes"
                            v-model="form.medical_notes"
                            rows="3"
                            placeholder="Alergias, medicamentos, observaciones importantes..."
                            :class="[
                                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                                { 'border-red-500': form.errors.medical_notes }
                            ]"
                        ></textarea>
                        <p v-if="form.errors.medical_notes" class="text-sm text-red-600">
                            {{ form.errors.medical_notes }}
                        </p>
                    </div>
                </div>

                <!-- Estado (solo en edición) -->
                <div v-if="isEditing">
                    <h3 class="text-sm font-medium mb-3">Estado</h3>
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <Label class="text-sm">Paciente activo</Label>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Los pacientes inactivos no aparecerán en las opciones de turnos
                    </p>
                </div>
            </form>

            <DialogFooter class="flex gap-3 pt-6 border-t">
                <Button
                    type="button"
                    variant="outline"
                    @click="closeModal"
                    :disabled="form.processing"
                >
                    Cancelar
                </Button>
                <Button
                    @click="submitForm"
                    :disabled="form.processing"
                    class="min-w-[120px]"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isEditing ? 'Actualizar Paciente' : 'Crear Paciente' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
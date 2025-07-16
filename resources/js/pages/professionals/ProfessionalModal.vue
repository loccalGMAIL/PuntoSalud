<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    Dialog,
    DialogContent,  // ⭐ Volvemos a DialogContent normal
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { router, useForm } from '@inertiajs/vue3';
import { LoaderCircle, UserPlus, Edit, Plus } from 'lucide-vue-next';
import { computed, watch, ref } from 'vue';

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
    specialty_id: number;
    commission_percentage: number;
    is_active: boolean;
}

interface Props {
    open: boolean;
    professional?: Professional | null;
    specialties: Specialty[];
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}

const props = withDefaults(defineProps<Props>(), {
    professional: null
});

const emit = defineEmits<Emits>();

// Estados para especialidades
const showNewSpecialtyForm = ref(false);
const newSpecialtyForm = useForm({
    name: '',
    description: ''
});

// Determinar si es edición o creación
const isEditing = computed(() => !!props.professional);
const title = computed(() => isEditing.value ? 'Editar Profesional' : 'Nuevo Profesional');
const description = computed(() => 
    isEditing.value 
        ? 'Modifica los datos del profesional' 
        : 'Completa la información para registrar un nuevo profesional'
);

// Formulario con Inertia
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    license_number: '',
    specialty_id: '',
    commission_percentage: 0,
    is_active: true,
});

// Función para resetear el formulario
const resetForm = () => {
    form.reset();
    form.clearErrors();
    showNewSpecialtyForm.value = false;
    newSpecialtyForm.reset();
};

// Función para cargar datos del profesional en edición
const loadProfessionalData = () => {
    if (props.professional) {
        form.first_name = props.professional.first_name;
        form.last_name = props.professional.last_name;
        form.email = props.professional.email;
        form.phone = props.professional.phone || '';
        form.license_number = props.professional.license_number;
        form.specialty_id = props.professional.specialty_id.toString();
        form.commission_percentage = props.professional.commission_percentage;
        form.is_active = props.professional.is_active;
    }
};

// Watcher para cargar datos cuando se abre el modal
watch(() => props.open, (newValue) => {
    if (newValue) {
        if (isEditing.value) {
            loadProfessionalData();
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

// Función para crear nueva especialidad
const createSpecialty = () => {
    newSpecialtyForm.post(route('specialties.store'), {
        onSuccess: () => {
            showNewSpecialtyForm.value = false;
            newSpecialtyForm.reset();
            emit('success'); // Recargar especialidades
        },
        preserveScroll: true,
    });
};

// Función para enviar el formulario
const submitForm = () => {
    // const data = {
    //     first_name: form.first_name,
    //     last_name: form.last_name,
    //     email: form.email,
    //     phone: form.phone || null,
    //     license_number: form.license_number,
    //     specialty_id: parseInt(form.specialty_id),
    //     commission_percentage: parseFloat(form.commission_percentage.toString()),
    //     ...(isEditing.value && { is_active: form.is_active })
    // };

    if (isEditing.value) {
        // Actualizar profesional existente
        form.patch(route('professionals.update', props.professional!.id), {
            onSuccess: () => {
                emit('success');
                closeModal();
            },
            preserveScroll: true,
        });
    } else {
        // Crear nuevo profesional
        form.post(route('professionals.store'), {
            onSuccess: () => {
                emit('success');
                closeModal();
            },
            preserveScroll: true,
        });
    }
};

// Especialidad seleccionada (para mostrar descripción)
const selectedSpecialty = computed(() => {
    if (!form.specialty_id) return null;
    return props.specialties.find(s => s.id.toString() === form.specialty_id);
});
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <!-- ⭐ Modal más ancho y con altura controlada -->
        <DialogContent class="sm:max-w-2xl max-h-[85vh] overflow-y-auto">
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
                <!-- ⭐ Información Personal - 3 columnas -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                            <Label for="license_number">Matrícula *</Label>
                            <Input
                                id="license_number"
                                v-model="form.license_number"
                                type="text"
                                placeholder="MP 12345"
                                :class="{ 'border-red-500': form.errors.license_number }"
                                required
                            />
                            <p v-if="form.errors.license_number" class="text-sm text-red-600">
                                {{ form.errors.license_number }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ⭐ Información de Contacto - 2 columnas -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información de Contacto</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="email">Email *</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="juan.perez@ejemplo.com"
                                :class="{ 'border-red-500': form.errors.email }"
                                required
                            />
                            <p v-if="form.errors.email" class="text-sm text-red-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Teléfono</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                placeholder="+54 11 1234-5678"
                                :class="{ 'border-red-500': form.errors.phone }"
                            />
                            <p v-if="form.errors.phone" class="text-sm text-red-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ⭐ Información Profesional - 2 columnas -->
                <div>
                    <h3 class="text-sm font-medium mb-3">Información Profesional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Especialidad con opción de crear nueva -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="specialty">Especialidad *</Label>
                                <Button 
                                    type="button" 
                                    variant="ghost" 
                                    size="sm" 
                                    @click="showNewSpecialtyForm = !showNewSpecialtyForm"
                                    class="h-auto p-1 text-xs"
                                >
                                    <Plus class="h-3 w-3 mr-1" />
                                    Nueva
                                </Button>
                            </div>

                            <Select v-model="form.specialty_id" required>
                                <SelectTrigger :class="{ 'border-red-500': form.errors.specialty_id }">
                                    <SelectValue placeholder="Selecciona una especialidad" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem 
                                        v-for="specialty in specialties" 
                                        :key="specialty.id" 
                                        :value="specialty.id.toString()"
                                    >
                                        {{ specialty.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            
                            <!-- Mostrar descripción de la especialidad -->
                            <div v-if="selectedSpecialty" class="flex items-center gap-2">
                                <Badge variant="outline" class="text-xs">
                                    {{ selectedSpecialty.description }}
                                </Badge>
                            </div>
                            
                            <p v-if="form.errors.specialty_id" class="text-sm text-red-600">
                                {{ form.errors.specialty_id }}
                            </p>
                        </div>

                        <!-- Comisión -->
                        <div class="space-y-2">
                            <Label for="commission">Porcentaje de Comisión *</Label>
                            <div class="relative">
                                <Input
                                    id="commission"
                                    v-model="form.commission_percentage"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    placeholder="15.00"
                                    :class="{ 'border-red-500': form.errors.commission_percentage }"
                                    class="pr-8"
                                    required
                                />
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-muted-foreground">
                                    %
                                </span>
                            </div>
                            <p v-if="form.errors.commission_percentage" class="text-sm text-red-600">
                                {{ form.errors.commission_percentage }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ⭐ Formulario para nueva especialidad (ocupando todo el ancho) -->
                <div v-if="showNewSpecialtyForm" class="p-4 border rounded-md bg-muted/50 space-y-4">
                    <h4 class="text-sm font-medium">Nueva Especialidad</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="new_specialty_name">Nombre de la especialidad</Label>
                            <Input
                                id="new_specialty_name"
                                v-model="newSpecialtyForm.name"
                                placeholder="Ej: Cardiología"
                                :class="{ 'border-red-500': newSpecialtyForm.errors.name }"
                            />
                            <p v-if="newSpecialtyForm.errors.name" class="text-sm text-red-600">
                                {{ newSpecialtyForm.errors.name }}
                            </p>
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="new_specialty_description">Descripción</Label>
                            <Input
                                id="new_specialty_description"
                                v-model="newSpecialtyForm.description"
                                placeholder="Ej: Especialidad del corazón"
                                :class="{ 'border-red-500': newSpecialtyForm.errors.description }"
                            />
                            <p v-if="newSpecialtyForm.errors.description" class="text-sm text-red-600">
                                {{ newSpecialtyForm.errors.description }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <Button 
                            type="button" 
                            size="sm" 
                            @click="createSpecialty"
                            :disabled="newSpecialtyForm.processing"
                        >
                            <LoaderCircle v-if="newSpecialtyForm.processing" class="mr-2 h-3 w-3 animate-spin" />
                            Crear Especialidad
                        </Button>
                        <Button 
                            type="button" 
                            variant="outline" 
                            size="sm" 
                            @click="showNewSpecialtyForm = false"
                        >
                            Cancelar
                        </Button>
                    </div>
                </div>

                <!-- ⭐ Estado (solo en edición) -->
                <div v-if="isEditing">
                    <h3 class="text-sm font-medium mb-3">Estado</h3>
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <Label class="text-sm">Profesional activo</Label>
                    </div>
                    <p class="text-sm text-muted-foreground mt-1">
                        Los profesionales inactivos no aparecerán en las opciones de turnos
                    </p>
                </div>
            </form>

            <!-- ⭐ Footer siempre visible -->
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
                    {{ isEditing ? 'Actualizar Profesional' : 'Crear Profesional' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Calendar, DollarSign, Users, TrendingUp, Clock, CheckCircle, Activity, Heart } from 'lucide-vue-next';

const props = defineProps<{
    dashboardData: {
        consultasHoy: {
            total: number;
            completadas: number;
            pendientes: number;
            canceladas: number;
        };
        ingresosHoy: {
            total: number;
            efectivo: number;
            transferencia: number;
        };
        profesionalesActivos: {
            total: number;
            enConsulta: number;
            disponibles: number;
        };
        consultasDetalle: Array<{
            id: number;
            paciente: string;
            profesional: string;
            hora: string;
            monto: number;
            status: string;
            statusLabel: string;
        }>;
        resumenCaja: {
            porProfesional: Array<{
                nombre: string;
                total: number;
            }>;
            totalGeneral: number;
            formasPago: {
                efectivo: number;
                transferencia: number;
            };
        };
        fecha: string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0
    }).format(amount);
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'attended': return 'emerald';
        case 'scheduled': return 'blue';
        case 'cancelled': return 'red';
        default: return 'gray';
    }
};

const getStatusIcon = (status: string) => {
    switch(status) {
        case 'attended': return CheckCircle;
        case 'scheduled': return Calendar;
        case 'cancelled': return 'X';
        default: return Clock;
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Métricas principales -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                
                <!-- Card 1: Consultas del día -->
                <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/50 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/20 dark:hover:shadow-emerald-900/20">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Consultas del Día</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ props.dashboardData.consultasHoy.total }}</p>
                                <span class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1" v-if="props.dashboardData.consultasHoy.total > 0">
                                    <TrendingUp class="h-3 w-3" />
                                    Activo
                                </span>
                            </div>
                            <div class="mt-3 flex gap-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <CheckCircle class="h-3 w-3 text-emerald-600" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ props.dashboardData.consultasHoy.completadas }} completadas</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3 w-3 text-amber-500" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ props.dashboardData.consultasHoy.pendientes }} pendientes</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                            <Activity class="h-6 w-6 text-emerald-700 dark:text-emerald-400" />
                        </div>
                    </div>
                    <!-- Decorative gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                </div>

                <!-- Card 2: Ingresos del día -->
                <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10 dark:hover:shadow-emerald-900/20">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ingresos del Día</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ formatCurrency(props.dashboardData.ingresosHoy.total) }}</p>
                                <span class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1" v-if="props.dashboardData.ingresosHoy.total > 0">
                                    <TrendingUp class="h-3 w-3" />
                                    Hoy
                                </span>
                            </div>
                            <div class="mt-3 space-y-1 text-xs">
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Efectivo:</span>
                                    <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ formatCurrency(props.dashboardData.ingresosHoy.efectivo) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Transferencia:</span>
                                    <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ formatCurrency(props.dashboardData.ingresosHoy.transferencia) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                            <DollarSign class="h-6 w-6 text-emerald-700 dark:text-emerald-400" />
                        </div>
                    </div>
                    <!-- Decorative gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                </div>

                <!-- Card 3: Profesionales activos -->
                <div class="group relative overflow-hidden rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:shadow-emerald-100/50 dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10 dark:hover:shadow-emerald-900/20">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Profesionales Activos</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ props.dashboardData.profesionalesActivos.total }}</p>
                                <span class="text-sm text-gray-500 dark:text-gray-400">activos hoy</span>
                            </div>
                            <div class="mt-3 flex gap-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ props.dashboardData.profesionalesActivos.enConsulta }} en consulta</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ props.dashboardData.profesionalesActivos.disponibles }} disponibles</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 transition-all duration-300 group-hover:bg-emerald-200 group-hover:scale-110 dark:bg-emerald-900/30">
                            <Heart class="h-6 w-6 text-emerald-700 dark:text-emerald-400" />
                        </div>
                    </div>
                    <!-- Decorative gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-600/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                </div>
            </div>

            <!-- Área principal dividida -->
            <div class="grid gap-4 lg:grid-cols-3">
                
                <!-- Izquierda: Lista de últimas consultas (2/3 del ancho) -->
                <div class="lg:col-span-2 rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/20 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <Calendar class="h-5 w-5 text-emerald-600" />
                            Consultas de Hoy
                        </h2>
                        <button class="text-sm text-emerald-600 hover:text-emerald-800 font-medium transition-colors duration-200 dark:text-emerald-400 dark:hover:text-emerald-300">
                            Ver todas →
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Consultas dinámicas desde la base de datos -->
                        <div v-for="consulta in props.dashboardData.consultasDetalle.slice(0, 5)" :key="consulta.id" 
                             :class="`group flex items-center justify-between p-4 rounded-lg border bg-white/80 transition-all duration-200 hover:shadow-md dark:bg-gray-800/50 border-${getStatusColor(consulta.status)}-100 hover:border-${getStatusColor(consulta.status)}-200 dark:border-${getStatusColor(consulta.status)}-800/30 dark:hover:border-${getStatusColor(consulta.status)}-700/50`">
                            <div class="flex items-center gap-4">
                                <div :class="`flex h-10 w-10 items-center justify-center rounded-full transition-transform duration-200 group-hover:scale-110 bg-${getStatusColor(consulta.status)}-100 dark:bg-${getStatusColor(consulta.status)}-900/30`">
                                    <component :is="getStatusIcon(consulta.status)" :class="`h-5 w-5 text-${getStatusColor(consulta.status)}-600 dark:text-${getStatusColor(consulta.status)}-400`" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ consulta.paciente }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ consulta.profesional }} • {{ consulta.hora }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(consulta.monto) }}</p>
                                <span :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-${getStatusColor(consulta.status)}-100 text-${getStatusColor(consulta.status)}-800 dark:bg-${getStatusColor(consulta.status)}-900/30 dark:text-${getStatusColor(consulta.status)}-400`">
                                    {{ consulta.statusLabel }}
                                </span>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay consultas -->
                        <div v-if="props.dashboardData.consultasDetalle.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <Calendar class="h-12 w-12 mx-auto mb-3 opacity-50" />
                            <p>No hay consultas programadas para hoy</p>
                        </div>
                    </div>
                </div>

                <!-- Derecha: Resumen de caja (1/3 del ancho) -->
                <div class="rounded-xl border border-emerald-200/50 bg-gradient-to-br from-white to-emerald-50/30 p-6 shadow-sm dark:border-emerald-800/30 dark:from-gray-900 dark:to-emerald-950/10">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <DollarSign class="h-5 w-5 text-emerald-600" />
                            Resumen de Caja
                        </h2>
                        <span class="text-sm text-gray-500 dark:text-gray-400 bg-emerald-100 px-2 py-1 rounded-md dark:bg-emerald-900/30">{{ props.dashboardData.fecha }}</span>
                    </div>

                    <!-- Totales por profesional -->
                    <div class="space-y-4 mb-6">
                        <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide flex items-center gap-2">
                            <Heart class="h-4 w-4" />
                            Por Profesional
                        </h3>
                        
                        <div class="space-y-3">
                            <div v-for="profesional in props.dashboardData.resumenCaja.porProfesional" :key="profesional.nombre" 
                                 class="flex justify-between items-center p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ profesional.nombre }}</span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ formatCurrency(profesional.total) }}</span>
                            </div>
                            
                            <!-- Mensaje cuando no hay datos -->
                            <div v-if="props.dashboardData.resumenCaja.porProfesional.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                <p class="text-sm">No hay consultas facturadas hoy</p>
                            </div>
                        </div>
                    </div>

                    <!-- Formas de pago -->
                    <div class="space-y-4 mb-6">
                        <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">Formas de Pago</h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-emerald-500"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Efectivo (70%)</span>
                                </div>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(props.dashboardData.resumenCaja.formasPago.efectivo) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Transferencia (30%)</span>
                                </div>
                                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ formatCurrency(props.dashboardData.resumenCaja.formasPago.transferencia) }}</span>
                            </div>
                        </div>

                        <!-- Barra de progreso visual -->
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700" v-if="props.dashboardData.resumenCaja.totalGeneral > 0">
                            <div class="bg-gradient-to-r from-emerald-500 to-blue-500 h-2 rounded-full relative">
                                <div class="bg-emerald-500 h-2 rounded-l-full w-[70%]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total general -->
                    <div class="border-t border-emerald-200 dark:border-emerald-700/50 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Total del Día</span>
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" :class="{ 'animate-pulse': props.dashboardData.resumenCaja.totalGeneral > 0 }">
                                {{ formatCurrency(props.dashboardData.resumenCaja.totalGeneral) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ props.dashboardData.consultasHoy.completadas }} consultas completadas</span>
                            <span>{{ props.dashboardData.consultasDetalle.length }} turnos del día</span>
                        </div>
                    </div>

                    <!-- Botón de acción -->
                    <button class="w-full mt-6 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg">
                        Ver Detalle de Caja
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<!-- 
MEJORAS APLICADAS - PASO 3:
✅ Colores de marca Punto Salud (verde emerald #166B3A)
✅ Gradientes sutiles y transiciones suaves
✅ Animaciones hover y microinteracciones
✅ Iconos temáticos (Activity, Heart, DollarSign)
✅ Estados visuales mejorados con colores semánticos
✅ Barra de progreso para formas de pago
✅ Optimización para dark mode
✅ Responsive design mejorado
✅ Sombras y efectos de profundidad
-->
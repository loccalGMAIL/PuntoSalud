<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Calendar, DollarSign, Users, TrendingUp, Clock, CheckCircle, Activity, Heart } from 'lucide-vue-next';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Datos de ejemplo - estos vendrán del backend
const dashboardData = {
    consultasHoy: {
        total: 12,
        completadas: 8,
        pendientes: 4
    },
    ingresosHoy: {
        total: 84000,
        efectivo: 54000,
        transferencia: 30000
    },
    profesionalesActivos: {
        total: 6,
        enConsulta: 3,
        disponibles: 3
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0
    }).format(amount);
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
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ dashboardData.consultasHoy.total }}</p>
                                <span class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-pulse">
                                    <TrendingUp class="h-3 w-3" />
                                    +2 vs ayer
                                </span>
                            </div>
                            <div class="mt-3 flex gap-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <CheckCircle class="h-3 w-3 text-emerald-600" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ dashboardData.consultasHoy.completadas }} completadas</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3 w-3 text-amber-500" />
                                    <span class="text-gray-600 dark:text-gray-400">{{ dashboardData.consultasHoy.pendientes }} pendientes</span>
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
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ formatCurrency(dashboardData.ingresosHoy.total) }}</p>
                                <span class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1 animate-pulse">
                                    <TrendingUp class="h-3 w-3" />
                                    +15%
                                </span>
                            </div>
                            <div class="mt-3 space-y-1 text-xs">
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Efectivo:</span>
                                    <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ formatCurrency(dashboardData.ingresosHoy.efectivo) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Transferencia:</span>
                                    <span class="font-medium text-emerald-700 dark:text-emerald-400">{{ formatCurrency(dashboardData.ingresosHoy.transferencia) }}</span>
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
                                <p class="text-3xl font-bold text-gray-900 dark:text-white transition-all duration-300 group-hover:scale-105">{{ dashboardData.profesionalesActivos.total }}</p>
                                <span class="text-sm text-gray-500 dark:text-gray-400">de 8 total</span>
                            </div>
                            <div class="mt-3 flex gap-4 text-xs">
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ dashboardData.profesionalesActivos.enConsulta }} en consulta</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="text-gray-600 dark:text-gray-400">{{ dashboardData.profesionalesActivos.disponibles }} disponibles</span>
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
                        <!-- Consulta 1 -->
                        <div class="group flex items-center justify-between p-4 rounded-lg border border-emerald-100 bg-white/80 transition-all duration-200 hover:border-emerald-200 hover:shadow-md dark:border-emerald-800/30 dark:bg-gray-800/50 dark:hover:border-emerald-700/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 transition-transform duration-200 group-hover:scale-110 dark:bg-emerald-900/30">
                                    <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">García, Luna Alejandra</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dr. Orlando Benjamín • 09:30</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$7.000</p>
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Completada
                                </span>
                            </div>
                        </div>

                        <!-- Consulta 2 -->
                        <div class="group flex items-center justify-between p-4 rounded-lg border border-amber-100 bg-white/80 transition-all duration-200 hover:border-amber-200 hover:shadow-md dark:border-amber-800/30 dark:bg-gray-800/50 dark:hover:border-amber-700/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 transition-transform duration-200 group-hover:scale-110 dark:bg-amber-900/30">
                                    <Clock class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Coronado, Felipe</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dra. García Luna Alejandra • 10:15</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$8.500</p>
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                    En curso
                                </span>
                            </div>
                        </div>

                        <!-- Consulta 3 -->
                        <div class="group flex items-center justify-between p-4 rounded-lg border border-blue-100 bg-white/80 transition-all duration-200 hover:border-blue-200 hover:shadow-md dark:border-blue-800/30 dark:bg-gray-800/50 dark:hover:border-blue-700/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 transition-transform duration-200 group-hover:scale-110 dark:bg-blue-900/30">
                                    <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Martínez, Ana Paula</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dr. Orlando Benjamín • 11:00</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$6.500</p>
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    Programada
                                </span>
                            </div>
                        </div>

                        <!-- Consulta 4 -->
                        <div class="group flex items-center justify-between p-4 rounded-lg border border-emerald-100 bg-white/80 transition-all duration-200 hover:border-emerald-200 hover:shadow-md dark:border-emerald-800/30 dark:bg-gray-800/50 dark:hover:border-emerald-700/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 transition-transform duration-200 group-hover:scale-110 dark:bg-emerald-900/30">
                                    <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">González, Roberto</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dra. García Luna Alejandra • 08:45</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$12.000</p>
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Completada
                                </span>
                            </div>
                        </div>

                        <!-- Consulta 5 -->
                        <div class="group flex items-center justify-between p-4 rounded-lg border border-blue-100 bg-white/80 transition-all duration-200 hover:border-blue-200 hover:shadow-md dark:border-blue-800/30 dark:bg-gray-800/50 dark:hover:border-blue-700/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 transition-transform duration-200 group-hover:scale-110 dark:bg-blue-900/30">
                                    <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Silva, Carmen Rosa</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Dr. Orlando Benjamín • 12:30</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$9.200</p>
                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                    Programada
                                </span>
                            </div>
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
                        <span class="text-sm text-gray-500 dark:text-gray-400 bg-emerald-100 px-2 py-1 rounded-md dark:bg-emerald-900/30">18/06/25</span>
                    </div>

                    <!-- Totales por profesional -->
                    <div class="space-y-4 mb-6">
                        <h3 class="text-sm font-medium text-emerald-700 dark:text-emerald-300 uppercase tracking-wide flex items-center gap-2">
                            <Heart class="h-4 w-4" />
                            Por Profesional
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Dr. Orlando Benjamín</span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">$22.700</span>
                            </div>
                            <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50/50 border border-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/30">
                                <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">Dra. García Luna</span>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">$20.500</span>
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
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">$54.000</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Transferencia (30%)</span>
                                </div>
                                <span class="font-semibold text-blue-600 dark:text-blue-400">$30.000</span>
                            </div>
                        </div>

                        <!-- Barra de progreso visual -->
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-gradient-to-r from-emerald-500 to-blue-500 h-2 rounded-full relative">
                                <div class="bg-emerald-500 h-2 rounded-l-full w-[70%]"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total general -->
                    <div class="border-t border-emerald-200 dark:border-emerald-700/50 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Total del Día</span>
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 animate-pulse">$84.000</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>12 consultas completadas</span>
                            <span>8 pacientes atendidos</span>
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


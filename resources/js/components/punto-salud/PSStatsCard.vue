<template>
  <div :class="cardClasses">
    <div class="flex items-start justify-between">
      <div>
        <!-- Título de la métrica -->
        <p :class="puntoSaludTheme.statsCard.description">
          {{ title }}
        </p>
        
        <!-- Número principal y texto adicional -->
        <div class="mt-2 flex items-baseline gap-2">
          <p :class="numberClasses">
            {{ formattedValue }}
          </p>
          <span v-if="subtitle" :class="puntoSaludTheme.statsCard.subtext">
            {{ subtitle }}
          </span>
        </div>
        
        <!-- Indicadores adicionales -->
        <div v-if="indicators?.length" class="mt-3 flex gap-4 text-xs">
          <div 
            v-for="indicator in indicators" 
            :key="indicator.label"
            class="flex items-center gap-1"
          >
            <!-- Punto de estado o icono -->
            <div 
              v-if="indicator.status" 
              :class="`h-2 w-2 rounded-full bg-${getStatusColor(indicator.status)}-500 ${indicator.pulse ? 'animate-pulse' : ''}`"
            ></div>
            <component 
              v-else-if="indicator.icon" 
              :is="indicator.icon" 
              :class="getIconClasses('muted', 'sm')"
            />
            
            <span :class="puntoSaludTheme.statsCard.subtext">
              {{ indicator.label }}
            </span>
          </div>
        </div>
      </div>
      
      <!-- Icono principal -->
      <div v-if="icon" :class="puntoSaludTheme.statsCard.iconContainer">
        <component 
          :is="icon" 
          :class="getIconClasses('primary', 'lg')"
        />
      </div>
    </div>
    
    <!-- Gradiente decorativo para hover -->
    <div :class="puntoSaludTheme.effects.decorativeGradient"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import puntoSaludTheme, { getIconClasses, combineClasses } from '@/theme/PuntoSaludTheme.js';

// Props del componente
const props = defineProps({
  // Texto principal
  title: {
    type: String,
    required: true
  },
  
  // Valor numérico principal
  value: {
    type: [Number, String],
    required: true
  },
  
  // Texto adicional junto al valor
  subtitle: {
    type: String,
    default: null
  },
  
  // Icono principal (componente de Lucide)
  icon: {
    type: [Object, String],
    default: null
  },
  
  // Color del número principal
  valueColor: {
    type: String,
    default: 'default', // 'default', 'success', 'warning', 'error', 'info'
    validator: (value) => ['default', 'success', 'warning', 'error', 'info'].includes(value)
  },
  
  // Indicadores adicionales
  indicators: {
    type: Array,
    default: () => [],
    // Formato: [{ label: 'texto', status: 'success'|'warning'|'error', pulse: true, icon: Component }]
  },
  
  // Formato de número
  format: {
    type: String,
    default: 'number', // 'number', 'currency', 'percentage'
    validator: (value) => ['number', 'currency', 'percentage'].includes(value)
  },
  
  // Clases adicionales
  className: {
    type: String,
    default: ''
  }
});

// === COMPUTED PROPERTIES ===

// Clases del contenedor principal
const cardClasses = computed(() => {
  return combineClasses(
    'group relative overflow-hidden',
    puntoSaludTheme.card.full,
    puntoSaludTheme.spacing.cardContent,
    props.className
  );
});

// Valor formateado según el tipo
const formattedValue = computed(() => {
  const value = props.value;
  
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value);
      
    case 'percentage':
      return `${value}%`;
      
    default:
      return typeof value === 'number' 
        ? value.toLocaleString('es-AR')
        : value;
  }
});

// Clases del número principal según el color
const numberClasses = computed(() => {
  const baseClasses = puntoSaludTheme.statsCard.mainNumber;
  
  const colorMap = {
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-amber-600 dark:text-amber-400', 
    error: 'text-red-600 dark:text-red-400',
    info: 'text-blue-600 dark:text-blue-400',
    default: 'text-gray-900 dark:text-white'
  };
  
  return combineClasses(baseClasses, colorMap[props.valueColor]);
});

// === HELPER FUNCTIONS ===

// Obtener color según estado
const getStatusColor = (status) => {
  const statusColors = {
    success: 'emerald',
    warning: 'amber',
    error: 'red',
    info: 'blue',
    active: 'emerald',
    inactive: 'red',
    pending: 'amber'
  };
  
  return statusColors[status] || 'gray';
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
<template>
  <div :class="cardClasses">
    <!-- Header de la card de filtros -->
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
        <component 
          v-if="icon" 
          :is="icon" 
          :class="getIconClasses('primary', 'md')"
        />
        {{ title }}
      </h3>
      
      <!-- Botón para limpiar filtros -->
      <PSButton
        v-if="showClearButton && hasActiveFilters"
        variant="ghost"
        size="sm"
        @click="handleClearFilters"
      >
        Limpiar filtros
      </PSButton>
    </div>
    
    <!-- Contenido de filtros -->
    <div :class="contentClasses">
      <slot></slot>
    </div>
    
    <!-- Footer opcional -->
    <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-green-100 dark:border-green-800/30">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import puntoSaludTheme, { getIconClasses, combineClasses } from '@/theme/PuntoSaludTheme.js';
import PSButton from './PSButton.vue';

// Props del componente
const props = defineProps({
  // Título de la sección de filtros
  title: {
    type: String,
    default: 'Filtros y Búsqueda'
  },
  
  // Icono del header
  icon: {
    type: [Object, String],
    default: null
  },
  
  // Layout del contenido
  layout: {
    type: String,
    default: 'grid',
    validator: (value) => ['grid', 'flex', 'custom'].includes(value)
  },
  
  // Columnas para layout grid
  columns: {
    type: [Number, String],
    default: 'auto'
  },
  
  // Mostrar botón de limpiar filtros
  showClearButton: {
    type: Boolean,
    default: true
  },
  
  // Indica si hay filtros activos
  hasActiveFilters: {
    type: Boolean,
    default: false
  },
  
  // Clases adicionales
  className: {
    type: String,
    default: ''
  }
});

// Emits
const emit = defineEmits(['clear-filters']);

// === COMPUTED PROPERTIES ===

// Clases del contenedor principal
const cardClasses = computed(() => {
  return combineClasses(
    puntoSaludTheme.card.full,
    puntoSaludTheme.spacing.cardContent,
    props.className
  );
});

// Clases del contenido según el layout
const contentClasses = computed(() => {
  const layouts = {
    grid: getGridClasses(),
    flex: 'flex flex-col md:flex-row md:items-end gap-4',
    custom: '' // El usuario define las clases
  };
  
  return layouts[props.layout] || layouts.grid;
});

// === METHODS ===

// Generar clases para grid según columnas
const getGridClasses = () => {
  if (typeof props.columns === 'number') {
    return `grid grid-cols-1 md:grid-cols-${props.columns} gap-4`;
  }
  
  // Configuraciones predefinidas
  const gridPresets = {
    'auto': 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4',
    '2': 'grid grid-cols-1 md:grid-cols-2 gap-4',
    '3': 'grid grid-cols-1 md:grid-cols-3 gap-4',
    '4': 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4',
    '5': 'grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4'
  };
  
  return gridPresets[props.columns] || gridPresets.auto;
};

// Manejar limpiar filtros
const handleClearFilters = () => {
  emit('clear-filters');
};
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
</style>
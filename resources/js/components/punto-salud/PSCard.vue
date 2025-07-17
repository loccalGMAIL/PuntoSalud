<template>
  <div :class="cardClasses">
    <!-- Header opcional -->
    <div 
      v-if="$slots.header || title || $slots.actions" 
      :class="headerClasses"
    >
      <!-- Título y descripción -->
      <div v-if="title || description">
        <h3 v-if="title" :class="titleClasses">
          <component 
            v-if="icon" 
            :is="icon" 
            :class="getIconClasses('primary', 'md')"
          />
          {{ title }}
        </h3>
        <p v-if="description" :class="descriptionClasses">
          {{ description }}
        </p>
      </div>
      
      <!-- Slot de header personalizado -->
      <slot name="header"></slot>
      
      <!-- Acciones del header -->
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <!-- Contenido principal -->
    <div :class="contentClasses">
      <slot></slot>
    </div>
    
    <!-- Footer opcional -->
    <div 
      v-if="$slots.footer" 
      :class="footerClasses"
    >
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import puntoSaludTheme, { getIconClasses, combineClasses } from '@/theme/PuntoSaludTheme.js';

// Props del componente
const props = defineProps({
  // Título de la card
  title: {
    type: String,
    default: null
  },
  
  // Descripción bajo el título
  description: {
    type: String,
    default: null
  },
  
  // Icono junto al título
  icon: {
    type: [Object, String],
    default: null
  },
  
  // Variante de la card
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'elevated', 'outlined', 'ghost'].includes(value)
  },
  
  // Padding del contenido
  padding: {
    type: String,
    default: 'default',
    validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
  },
  
  // Si la card es hoverable
  hoverable: {
    type: Boolean,
    default: false
  },
  
  // Si la card es clickeable
  clickable: {
    type: Boolean,
    default: false
  },
  
  // Altura de la card
  height: {
    type: String,
    default: 'auto',
    validator: (value) => ['auto', 'sm', 'md', 'lg', 'xl', 'full', 'screen'].includes(value)
  },
  
  // Clases adicionales
  className: {
    type: String,
    default: ''
  }
});

// Emits
const emit = defineEmits(['click']);

// === COMPUTED PROPERTIES ===

// Clases principales de la card
const cardClasses = computed(() => {
  const baseClasses = 'rounded-xl border bg-white dark:bg-gray-900';
  
  const variants = {
    default: puntoSaludTheme.card.full,
    elevated: `
      ${puntoSaludTheme.card.base} ${puntoSaludTheme.card.dark}
      shadow-lg hover:shadow-xl transition-all duration-300
    `,
    outlined: `
      border-2 border-green-200 bg-white shadow-none
      dark:border-green-700 dark:bg-gray-900
    `,
    ghost: `
      border-transparent bg-green-50/50 shadow-none
      dark:bg-green-950/20
    `
  };
  
  const heightClasses = {
    auto: '',
    sm: 'h-24',
    md: 'h-32', 
    lg: 'h-40',
    xl: 'h-48',
    full: 'h-full',
    screen: 'h-screen'
  };
  
  const interactionClasses = [
    props.hoverable ? puntoSaludTheme.card.hover : '',
    props.clickable ? 'cursor-pointer select-none' : '',
    props.clickable ? puntoSaludTheme.effects.hoverScale : ''
  ].filter(Boolean).join(' ');
  
  return combineClasses(
    variants[props.variant] || variants.default,
    heightClasses[props.height] || heightClasses.auto,
    interactionClasses,
    props.className
  );
});

// Clases del header
const headerClasses = computed(() => {
  const hasBorder = props.padding !== 'none';
  return combineClasses(
    'flex items-center justify-between',
    paddingClasses.value,
    hasBorder ? 'border-b border-green-100 dark:border-green-800/30' : '',
    props.padding !== 'none' ? 'pb-4 mb-4' : ''
  );
});

// Clases del contenido
const contentClasses = computed(() => {
  return paddingClasses.value;
});

// Clases del footer
const footerClasses = computed(() => {
  const hasBorder = props.padding !== 'none';
  return combineClasses(
    paddingClasses.value,
    hasBorder ? 'border-t border-green-100 dark:border-green-800/30' : '',
    props.padding !== 'none' ? 'pt-4 mt-4' : ''
  );
});

// Clases de padding según el tamaño
const paddingClasses = computed(() => {
  const paddings = {
    none: '',
    sm: 'p-4',
    default: 'p-6',
    lg: 'p-8'
  };
  
  return paddings[props.padding] || paddings.default;
});

// Clases del título
const titleClasses = computed(() => {
  return 'text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2';
});

// Clases de la descripción
const descriptionClasses = computed(() => {
  return 'mt-1 text-sm text-gray-600 dark:text-gray-400';
});

// === METHODS ===

// Manejar click en la card
const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event);
  }
};
</script>

<style scoped>
/* Focus para accesibilidad cuando es clickeable */
.cursor-pointer:focus {
  outline: none;
  box-shadow: 0 0 0 2px #22c55e, 0 0 0 4px #fff; /* ring-green-500 and ring-offset-2 approximation */
}
</style>
<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :href="tag === 'a' ? href : undefined"
    :to="tag === 'router-link' ? to : undefined"
    :disabled="disabled"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Icono izquierdo -->
    <component 
      v-if="iconLeft" 
      :is="iconLeft" 
      :class="iconClasses"
    />
    
    <!-- Contenido del botón -->
    <span v-if="$slots.default || label">
      <slot>{{ label }}</slot>
    </span>
    
    <!-- Icono derecho -->
    <component 
      v-if="iconRight" 
      :is="iconRight" 
      :class="iconClasses"
    />
    
    <!-- Loading spinner -->
    <div 
      v-if="loading" 
      class="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"
    ></div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import puntoSaludTheme, { combineClasses } from '@/theme/PuntoSaludTheme.js';

// Props del componente
const props = defineProps({
  // Variante del botón
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline', 'ghost', 'link'].includes(value)
  },
  
  // Tamaño del botón
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // Elemento HTML a renderizar
  tag: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'a', 'router-link'].includes(value)
  },
  
  // Tipo de botón (solo para tag="button")
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  
  // URL para enlaces (tag="a")
  href: {
    type: String,
    default: null
  },
  
  // Ruta para router-link (tag="router-link") 
  to: {
    type: [String, Object],
    default: null
  },
  
  // Estado deshabilitado
  disabled: {
    type: Boolean,
    default: false
  },
  
  // Estado de carga
  loading: {
    type: Boolean,
    default: false
  },
  
  // Texto del botón
  label: {
    type: String,
    default: null
  },
  
  // Icono izquierdo
  iconLeft: {
    type: [Object, String],
    default: null
  },
  
  // Icono derecho
  iconRight: {
    type: [Object, String],
    default: null
  },
  
  // Ancho completo
  fullWidth: {
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
const emit = defineEmits(['click']);

// === COMPUTED PROPERTIES ===

// Clases principales del botón
const buttonClasses = computed(() => {
  const baseClasses = `
    inline-flex items-center justify-center gap-2
    font-medium rounded-lg
    transition-all duration-200
    focus:outline-none focus:ring-2 focus:ring-offset-2
    disabled:opacity-50 disabled:cursor-not-allowed
    disabled:hover:scale-100
  `;
  
  return combineClasses(
    baseClasses,
    variantClasses.value,
    sizeClasses.value,
    props.fullWidth ? 'w-full' : '',
    props.loading ? 'cursor-wait' : '',
    props.className
  );
});

// Clases según la variante
const variantClasses = computed(() => {
  const variants = {
    primary: puntoSaludTheme.button.primary,
    
    secondary: puntoSaludTheme.button.secondary,
    
    outline: puntoSaludTheme.button.outline,
    
    ghost: `
      text-green-600 bg-transparent hover:bg-green-50
      dark:text-green-400 dark:hover:bg-green-950/20
    `,
    
    link: `
      text-green-600 bg-transparent hover:text-green-800 hover:underline
      p-0 h-auto shadow-none hover:shadow-none transform-none hover:scale-100
      dark:text-green-400 dark:hover:text-green-300
    `
  };
  
  return variants[props.variant] || variants.primary;
});

// Clases según el tamaño
const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-3 py-1.5 text-sm h-8',
    md: 'px-4 py-2 text-sm h-10', 
    lg: 'px-6 py-3 text-base h-12'
  };
  
  // Para variante link, no aplicar padding ni altura
  if (props.variant === 'link') {
    const linkSizes = {
      sm: 'text-sm',
      md: 'text-sm',
      lg: 'text-base'
    };
    return linkSizes[props.size];
  }
  
  return sizes[props.size] || sizes.md;
});

// Clases para los iconos
const iconClasses = computed(() => {
  const sizes = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5'
  };
  
  return sizes[props.size] || sizes.md;
});

// === METHODS ===

// Manejar click
const handleClick = (event) => {
  if (props.disabled || props.loading) {
    event.preventDefault();
    return;
  }
  
  emit('click', event);
};
</script>

<style scoped>
/* Focus visible para accesibilidad */
button:focus-visible,
a:focus-visible {
  outline: 2px solid #22c55e; /* Tailwind's green-500 */
  outline-offset: 2px;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5); /* mimic ring effect */
}
</style>
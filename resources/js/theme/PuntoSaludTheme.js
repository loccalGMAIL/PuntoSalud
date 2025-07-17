/**
 * PUNTO SALUD - TEMA Y ESTILOS BASE
 * Configuración centralizada de estilos para mantener consistencia visual
 * Basado en los estilos del dashboard existente
 */

export const puntoSaludTheme = {
  // === COLORES DE MARCA ===
  colors: {
    primary: 'green-600',             // Verde principal #166B3A
    primaryLight: 'green-50',         // Verde claro para backgrounds
    primaryBorder: 'green-200/50',    // Bordes sutiles
    primaryDark: 'green-800',         // Verde oscuro para dark mode
    
    // Estados semánticos
    success: 'green-600',
    warning: 'amber-500', 
    error: 'red-600',
    info: 'blue-600',
    
    // Grises neutros
    text: 'gray-900',
    textMuted: 'gray-600',
    textLight: 'gray-500'
  },

  // === CARDS BASE ===
  card: {
    // Card principal con gradiente sutil
    base: `
      rounded-xl border border-green-200/50 
      bg-gradient-to-br from-white to-green-50/30 
      shadow-sm
    `,
    
    // Efectos hover
    hover: `
      transition-all duration-300 
      hover:shadow-lg hover:shadow-green-100/50
    `,
    
    // Dark mode
    dark: `
      dark:border-green-800/30 
      dark:from-gray-900 dark:to-green-950/10 
      dark:hover:shadow-green-900/20
    `,
    
    // Card completa combinada
    full: `
      rounded-xl border border-green-200/50 
      bg-gradient-to-br from-white to-green-50/30 
      shadow-sm transition-all duration-300 
      hover:shadow-lg hover:shadow-green-100/50
      dark:border-green-800/30 dark:from-gray-900 dark:to-green-950/10 
      dark:hover:shadow-green-900/20
    `
  },

  // === BOTONES ===
  button: {
    // Botón primario con gradiente
    primary: `
      bg-gradient-to-r from-green-600 to-green-700 
      hover:from-green-700 hover:to-green-800 
      text-white font-medium
      px-4 py-2 rounded-lg
      transition-all duration-200 
      transform hover:scale-105 
      shadow-md hover:shadow-lg
    `,
    
    // Botón secundario
    secondary: `
      border border-green-200 
      text-green-700 bg-white
      hover:bg-green-50 
      px-4 py-2 rounded-lg
      transition-all duration-200
      dark:border-green-700 dark:text-green-400 
      dark:bg-gray-800 dark:hover:bg-green-950/20
    `,
    
    // Botón outline
    outline: `
      border border-green-600 
      text-green-600 bg-transparent
      hover:bg-green-600 hover:text-white
      px-4 py-2 rounded-lg
      transition-all duration-200
    `,
    
    // Botón de estado (success, warning, etc.)
    state: (state) => `
      px-2.5 py-0.5 rounded-full text-xs font-medium
      bg-${state}-100 text-${state}-800
      dark:bg-${state}-900/30 dark:text-${state}-400
    `
  },

  // === CARDS DE ESTADÍSTICAS ===
  statsCard: {
    base: `
      group relative overflow-hidden
      rounded-xl border border-green-200/50 
      bg-gradient-to-br from-white to-green-50/30 
      p-6 shadow-sm transition-all duration-300 
      hover:shadow-lg hover:shadow-green-100/50
      dark:border-green-800/30 dark:from-gray-900 dark:to-green-950/10
    `,
    
    // Icono container
    iconContainer: `
      flex h-12 w-12 items-center justify-center 
      rounded-lg bg-green-100 
      transition-all duration-300 
      group-hover:bg-green-200 group-hover:scale-110 
      dark:bg-green-900/30
    `,
    
    // Número principal
    mainNumber: `
      text-3xl font-bold text-gray-900 dark:text-white 
      transition-all duration-300 group-hover:scale-105
    `,
    
    // Texto descriptivo
    description: `
      text-sm font-medium text-gray-600 dark:text-gray-400
    `,
    
    // Subtexto
    subtext: `
      text-xs text-gray-500 dark:text-gray-400
    `
  },

  // === EFECTOS Y ANIMACIONES ===
  effects: {
    // Gradiente decorativo para hover
    decorativeGradient: `
      absolute inset-0 bg-gradient-to-r from-green-600/5 to-transparent 
      opacity-0 transition-opacity duration-300 group-hover:opacity-100
    `,
    
    // Pulso para indicadores activos
    pulse: 'animate-pulse',
    
    // Escala hover suave
    hoverScale: 'transform hover:scale-105 transition-transform duration-200',
    
    // Transición suave general
    smoothTransition: 'transition-all duration-300'
  },

  // === INPUTS Y FORMULARIOS ===
  input: {
    base: `
      w-full px-3 py-2 border border-gray-300 rounded-lg
      focus:ring-2 focus:ring-green-500 focus:border-green-500
      dark:border-gray-600 dark:bg-gray-800 dark:text-white
      transition-all duration-200
    `,
    
    search: `
      pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg
      focus:ring-2 focus:ring-green-500 focus:border-green-500
      dark:border-gray-600 dark:bg-gray-800 dark:text-white
    `
  },

  // === UTILIDADES DE ESPACIADO ===
  spacing: {
    section: 'gap-6',           // Entre secciones principales
    cardGrid: 'gap-4',          // Entre cards en grid
    formGroup: 'gap-4',         // Entre grupos de formulario
    cardContent: 'p-6',         // Padding interno de cards
    buttonGroup: 'gap-2'        // Entre botones
  },

  // === ALTURAS PREDEFINIDAS ===
  heights: {
    card: {
      sm: 'h-24 min-h-[6rem]',      // Card pequeña
      md: 'h-32 min-h-[8rem]',      // Card mediana (default stats)
      lg: 'h-40 min-h-[10rem]',     // Card grande  
      xl: 'h-48 min-h-[12rem]',     // Card extra grande
      auto: 'h-auto',               // Altura automática
      full: 'h-full'                // Altura completa del contenedor
    },
    
    content: {
      sm: 'min-h-[200px]',          // Contenido mínimo pequeño
      md: 'min-h-[300px]',          // Contenido mínimo mediano
      lg: 'min-h-[400px]',          // Contenido mínimo grande
      xl: 'min-h-[500px]',          // Contenido mínimo extra grande
      screen: 'min-h-screen'        // Altura de pantalla completa
    }
  },

  // === BREAKPOINTS RESPONSIVOS ===
  responsive: {
    statsGrid: 'grid gap-4 md:grid-cols-2 lg:grid-cols-4',
    formGrid: 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4',
    flexResponsive: 'flex flex-col md:flex-row md:items-center gap-4'
  }
};

// === FUNCIONES HELPER ===

/**
 * Genera clases para estados de badges/chips
 */
export const getStatusClasses = (status) => {
  const statusMap = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    inactive: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    cancelled: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
    info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
  };
  
  return `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${statusMap[status] || statusMap.pending}`;
};

/**
 * Combina clases del tema de forma segura
 */
export const combineClasses = (...classes) => {
  return classes.filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
};

/**
 * Genera clases para iconos con colores semánticos
 */
export const getIconClasses = (variant = 'primary', size = 'md') => {
  const sizes = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5', 
    lg: 'h-6 w-6'
  };
  
  const variants = {
    primary: 'text-green-600 dark:text-green-400',
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-amber-500 dark:text-amber-400',
    error: 'text-red-600 dark:text-red-400',
    muted: 'text-gray-500 dark:text-gray-400'
  };
  
  return combineClasses(sizes[size], variants[variant]);
};

// Export por defecto
export default puntoSaludTheme;
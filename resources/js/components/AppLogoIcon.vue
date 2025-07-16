<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <!-- Logo SVG simplificado de Punto Salud -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" :class="className" v-bind="$attrs">
        <!-- Fondo verde cuadrado principal -->
        <rect x="2" y="2" width="16" height="16" rx="2" fill="#166B3A" />
        
        <!-- Cruz médica blanca en el cuadrado -->
        <rect x="8.5" y="5" width="3" height="12" rx="0.5" fill="white" />
        <rect x="5" y="8.5" width="12" height="3" rx="0.5" fill="white" />
        
        <!-- Fondo verde cuadrado secundario -->
        <rect x="2" y="20" width="16" height="16" rx="2" fill="#0F4E2A" />
        
        <!-- Símbolo de corazón estilizado -->
        <path d="M10 25c-2 0-4 2-4 4c0 3 4 6 4 6s4-3 4-6c0-2-2-4-4-4z" fill="white" />
        
        <!-- Texto "S" estilizado (por Salud) -->
        <text x="25" y="14" font-family="Arial, sans-serif" font-weight="bold" font-size="12" fill="currentColor">S</text>
        
        <!-- Símbolo plus pequeño decorativo -->
        <rect x="32" y="8" width="1.5" height="6" fill="currentColor" opacity="0.7" />
        <rect x="29" y="10.25" width="6" height="1.5" fill="currentColor" opacity="0.7" />
    </svg>
</template>
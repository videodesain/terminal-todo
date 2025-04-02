<script setup>
import { computed, useSlots } from 'vue';
import { usePermission } from '@/Composables/usePermission';

const props = defineProps({
    permission: {
        type: [String, Array],
        required: false,
        default: null
    },
    any: {
        type: Array,
        required: false,
        default: null
    },
    requireAll: {
        type: Boolean,
        default: false
    }
});

const slots = useSlots();
const { hasPermission } = usePermission();

const hasDefaultSlot = computed(() => {
    return !!slots.default;
});

const checkPermission = computed(() => {
    // Jika prop 'any' digunakan
    if (props.any && Array.isArray(props.any)) {
        return props.any.some(p => hasPermission(p));
    }

    // Jika permission sebagai array
    if (Array.isArray(props.permission)) {
        return props.requireAll
            ? props.permission.every(p => hasPermission(p))
            : props.permission.some(p => hasPermission(p));
    }

    // Jika permission sebagai string
    if (props.permission) {
        return hasPermission(props.permission);
    }

    return false;
});
</script>

<template>
    <template v-if="checkPermission && hasDefaultSlot">
        <slot />
    </template>
</template> 
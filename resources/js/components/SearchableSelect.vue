<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';

type Option = { label: string; value: any };

const props = defineProps<{
  modelValue: any
  options: Option[]
  placeholder?: string
  disabled?: boolean
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', val: any): void
  (e: 'change', val: any): void
}>();

const open = ref(false);
const search = ref('');
const triggerRef = ref<HTMLElement | null>(null);
const listRef = ref<HTMLUListElement | null>(null);
const activeIndex = ref<number>(-1);

const selected = computed(() => props.options.find(o => o.value === props.modelValue) ?? null);

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase();
  if (!q) return props.options;
  return props.options.filter(o => o.label.toLowerCase().includes(q));
});

function toggle() {
  if (props.disabled) return;
  open.value = !open.value;
  if (open.value) {
    nextTick(() => {
      activeIndex.value = selected.value
        ? filtered.value.findIndex(o => o.value === selected.value?.value)
        : -1;
    });
  }
}

function close() {
  open.value = false;
  search.value = '';
  activeIndex.value = -1;
}

function selectOption(opt: Option) {
  emit('update:modelValue', opt.value);
  emit('change', opt.value);
  close();
  triggerRef.value?.focus();
}

function onKeydown(e: KeyboardEvent) {
  if (!open.value) {
    if (['Enter', ' ', 'ArrowDown'].includes(e.key)) {
      e.preventDefault();
      toggle();
    }
    return;
  }

  // open === true
  if (e.key === 'Escape') { e.preventDefault(); close(); return; }

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (!filtered.value.length) return;
    activeIndex.value = (activeIndex.value + 1) % filtered.value.length;
    scrollActiveIntoView();
    return;
  }

  if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (!filtered.value.length) return;
    activeIndex.value = (activeIndex.value - 1 + filtered.value.length) % filtered.value.length;
    scrollActiveIntoView();
    return;
  }

  if (e.key === 'Enter') {
    e.preventDefault();
    if (activeIndex.value >= 0 && filtered.value[activeIndex.value]) {
      selectOption(filtered.value[activeIndex.value]);
    }
    return;
  }
}

function scrollActiveIntoView() {
  nextTick(() => {
    const list = listRef.value;
    const idx = activeIndex.value;
    if (!list || idx < 0) return;
    const el = list.children.item(idx) as HTMLElement | null;
    el?.scrollIntoView({ block: 'nearest' });
  });
}

// close on click outside
function onDocumentClick(e: MouseEvent) {
  const t = e.target as Node;
  const root = triggerRef.value?.parentElement; // wrapper div
  if (root && !root.contains(t)) close();
}

onMounted(() => document.addEventListener('click', onDocumentClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick));

// Keep activeIndex valid when filtering changes
watch(filtered, () => {
  if (!open.value) return;
  if (activeIndex.value >= filtered.value.length) activeIndex.value = filtered.value.length - 1;
});
</script>

<template>
  <div class="relative w-full" @keydown="onKeydown">
    <!-- Trigger -->
    <button :disabled="disabled" ref="triggerRef" type="button"
      class="w-full inline-flex items-center justify-between rounded-md border px-3 py-2 text-left text-sm disabled:opacity-50"
      :class="open ? 'ring-2 ring-offset-1 ring-blue-500' : ''" @click="toggle" aria-haspopup="listbox"
      :aria-expanded="open">
      <span class="truncate">
        {{ selected ? selected.label : (placeholder ?? 'Select...') }}
      </span>
      <svg class="h-4 w-4 opacity-70" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
          d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
          clip-rule="evenodd" />
      </svg>
    </button>

    <!-- Dropdown -->
    <div v-if="open" class="absolute z-50 mt-1 w-full rounded-md border bg-white shadow-lg" role="dialog">
      <!-- Search -->
      <div class="p-2 border-b">
        <input type="text" v-model="search" placeholder="Search…" class="w-full rounded-md border px-2 py-1 text-sm"
          autofocus />
      </div>

      <!-- Options -->
      <ul ref="listRef" role="listbox" class="max-h-56 overflow-auto py-1 text-sm">
        <li v-if="!filtered.length" class="px-3 py-2 text-gray-500" aria-disabled="true">
          No results
        </li>

        <li v-for="(opt, idx) in filtered" :key="opt.value ?? idx" role="option"
          :aria-selected="selected && opt.value === selected.value" class="cursor-pointer px-3 py-2 hover:bg-gray-100"
          :class="{
            'bg-gray-100': activeIndex === idx,
            'font-medium': selected && opt.value === selected.value
          }" @mouseenter="activeIndex = idx" @mousedown.prevent @click="selectOption(opt)">
          {{ opt.label }}
        </li>

      </ul>
    </div>
  </div>
</template>

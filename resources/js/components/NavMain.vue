<script setup lang="ts">
import type { LucideIcon } from '@lucide/vue';
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar';
import { RouterLink } from 'vue-router';

interface NavItem {
  title: string;
  url: string;
  icon: LucideIcon;
  isActive?: boolean;
}

withDefaults(
  defineProps<{
    items: NavItem[];
    label?: string;
  }>(),
  {
    label: 'Menú',
  },
);
</script>

<template>
  <SidebarGroup>
    <SidebarGroupLabel>{{ label }}</SidebarGroupLabel>
    <SidebarMenu>
      <SidebarMenuItem
        v-for="item in items"
        :key="item.title"
      >
        <SidebarMenuButton
          as-child
          :tooltip="item.title"
          :is-active="item.isActive"
        >
          <RouterLink :to="item.url">
            <component
              :is="item.icon"
              class="size-4"
            />
            <span>{{ item.title }}</span>
          </RouterLink>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </SidebarGroup>
</template>

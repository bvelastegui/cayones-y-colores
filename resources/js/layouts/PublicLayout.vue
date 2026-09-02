<script setup lang="ts">
import { RouterLink } from 'vue-router';
import ActionLink from '@/components/landing/atoms/ActionLink.vue';
import LandingBrand from '@/components/landing/atoms/LandingBrand.vue';
</script>

<template>
  <div class="bg-background flex min-h-screen flex-col">
    <a
      href="#main-content"
      class="bg-primary text-primary-foreground focus-visible:ring-ring sr-only fixed top-3 left-3 z-50 rounded-full px-4 py-2 font-semibold focus:not-sr-only focus-visible:ring-2"
    >
      Saltar al contenido
    </a>
    <header
      class="bg-background/90 border-border/70 sticky top-0 z-40 border-b backdrop-blur-xl"
    >
      <div
        class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8 lg:px-10"
      >
        <RouterLink
          to="/"
          class="focus-visible:ring-ring rounded-2xl focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
          aria-label="Crayones y Colores, ir al inicio"
        >
          <LandingBrand compact-on-mobile />
        </RouterLink>
        <nav
          class="hidden items-center gap-1 lg:flex"
          aria-label="Navegación principal"
        >
          <a
            href="/#propuesta"
            class="hover:bg-muted focus-visible:ring-ring rounded-full px-4 py-3 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
          >
            Propuesta
          </a>
          <a
            href="/#programas"
            class="hover:bg-muted focus-visible:ring-ring rounded-full px-4 py-3 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
          >
            Niveles
          </a>
          <a
            href="/#familias"
            class="hover:bg-muted focus-visible:ring-ring rounded-full px-4 py-3 text-sm font-semibold focus-visible:ring-2 focus-visible:outline-none"
          >
            Familias
          </a>
          <ActionLink
            to="/login"
            variant="ghost"
            >Acceder</ActionLink
          >
          <ActionLink to="/apply">Admisiones</ActionLink>
        </nav>
        <ActionLink
          to="/apply"
          class="lg:hidden"
          >Admisión</ActionLink
        >
      </div>
    </header>

    <main
      id="main-content"
      class="flex-1"
    >
      <slot />
    </main>

    <footer class="bg-card border-border border-t">
      <div
        class="mx-auto flex max-w-7xl flex-col gap-8 px-5 py-10 sm:px-8 md:flex-row md:items-end md:justify-between lg:px-10"
      >
        <div class="flex max-w-md flex-col gap-4">
          <LandingBrand />
          <p class="text-muted-foreground text-sm leading-6">
            Un espacio para descubrir, aprender y crecer con alegría.
          </p>
        </div>
        <div class="flex flex-col gap-3 text-sm md:items-end">
          <nav
            class="flex flex-wrap gap-x-5 gap-y-3"
            aria-label="Navegación del pie de página"
          >
            <RouterLink
              class="hover:text-primary"
              to="/apply"
              >Admisión</RouterLink
            >
            <RouterLink
              class="hover:text-primary"
              to="/login"
              >Portal institucional</RouterLink
            >
          </nav>
          <p class="text-muted-foreground">
            © {{ new Date().getFullYear() }} Crayones y Colores.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

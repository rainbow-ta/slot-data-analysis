<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="max-w-screen-2xl px-4 md:px-8 mx-auto">
    <div class="bg-white mb-4 lg:mb-8">
      <header class="flex justify-between items-center py-4 md:py-8">
        <Link
          :href="'/'"
          class="inline-flex items-center text-black-800 text-2xl md:text-3xl font-bold gap-2.5"
        >
          Home
        </Link>
      </header>
    </div>
    <header>
      <slot name="header" />
    </header>
    <main>
      <slot />
    </main>
    <footer>
      <slot name="footer" />
    </footer>
  </div>
</template>

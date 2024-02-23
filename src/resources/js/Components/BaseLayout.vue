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
      <div class="w-full mx-auto p-4 md:py-8">
        <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
        <span class="block text-sm text-gray-500 sm:text-center dark:text-gray-400">&copy; 2023 slot-data-analysis.</span>
      </div>
    </footer>
  </div>
</template>

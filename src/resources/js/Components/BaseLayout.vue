<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <body class="flex flex-col min-h-screen max-w-screen-2xl px-4 md:px-8 mx-auto">
    <header>
      <nav class="bg-white border-gray-200">
        <div class="flex flex-wrap items-center justify-between mx-auto py-6">
          <Link
            href="/"
            class="flex items-center space-x-3 rtl:space-x-reverse"
          >
            <img src="/public/logo.png" class="h-8" alt="SlotAna Logo" />
            <span class="self-center text-2xl font-semibold whitespace-nowrap">スロアナ</span>
          </Link>

          <button data-collapse-toggle="navbar-default" type="button" class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" aria-controls="navbar-default" aria-expanded="false">
              <span class="sr-only">Open main menu</span>
              <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
              </svg>
          </button>

          <div class="hidden w-full md:block md:w-auto" id="navbar-default">
            <ul class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white">
              <li>
                <Link
                  href="/"
                  class="block py-2 px-3 text-white bg-blue-700 rounded md:bg-transparent md:text-blue-700 md:p-0"
                  aria-current="page"
                >
                  Home
                </Link>
              </li>
              <!-- <li>
                <a href="#" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0">About</a>
              </li> -->
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="flex-grow">
        <slot name="main">
        </slot>
    </main>

    <footer class="w-full mx-auto p-4 md:py-8">
      <hr class="my-6 border-gray-200 sm:mx-auto dark:border-gray-700 lg:my-8" />
      <span class="block text-sm text-gray-500 sm:text-center dark:text-gray-400">&copy; 2023 スロアナ</span>
    </footer>
  </body>
</template>

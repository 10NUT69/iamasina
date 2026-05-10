<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1536px] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#1E1E1E] overflow-hidden shadow-sm sm:rounded-lg border border-transparent dark:border-[#333333]">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

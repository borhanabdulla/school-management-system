<button {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-indigo-500/30 shadow-lg shadow-indigo-500/30 transform hover:-translate-y-0.5 transition-all duration-200 ease-in-out']) }}>
    {{ $slot }}
</button>

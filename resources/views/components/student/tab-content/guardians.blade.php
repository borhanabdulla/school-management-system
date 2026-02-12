@props(['guardians'])

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($guardians as $guardian)
        <div class="group relative bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-900/50 transition-all duration-300">
            <!-- Decorative Background -->
            <div class="absolute top-0 left-0 right-0 h-24 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-slate-700/50 dark:to-slate-700/30"></div>
            
            <div class="relative p-6">
                <div class="flex items-start justify-between">
                    <!-- Avatar & Info -->
                    <div class="flex items-end gap-4 mt-2">
                        <div class="relative">
                            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-slate-800 p-1 shadow-sm ring-1 ring-gray-100 dark:ring-slate-700">
                                <div class="w-full h-full rounded-xl bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/40 dark:to-indigo-900/40 flex items-center justify-center text-purple-600 dark:text-purple-300 font-bold text-3xl">
                                    {{ mb_substr($guardian->first_name, 0, 1) }}
                                </div>
                            </div>
                            @if ($guardian->pivot->is_financial_sponsor)
                                <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-white p-1.5 rounded-full ring-2 ring-white dark:ring-slate-800 shadow-sm" title="المسؤول المالي">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mb-2">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $guardian->first_name }} {{ $guardian->last_name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    {{ $guardian->pivot->relationship }}
                                </span>
                                @if($guardian->job)
                                    <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        {{ $guardian->job }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-3">
                    @if ($guardian->phone)
                        <a href="tel:{{ $guardian->phone }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-gray-50 text-gray-700 hover:bg-gray-100 hover:text-gray-900 border border-gray-100 dark:bg-slate-700/50 dark:text-gray-300 dark:hover:bg-slate-700 dark:border-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                            <span class="text-sm font-medium">اتصال</span>
                        </a>
                        <a href="https://wa.me/{{ $guardian->phone }}" target="_blank" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 border border-green-100 dark:bg-green-900/10 dark:text-green-400 dark:hover:bg-green-900/20 dark:border-green-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
                            </svg>
                            <span class="text-sm font-medium">واتساب</span>
                        </a>
                    @else
                        <div class="col-span-2 py-2.5 text-center text-sm text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200 dark:bg-slate-700/30 dark:border-slate-700">
                            لا يوجد رقم هاتف
                        </div>
                    @endif
                </div>

                @if ($guardian->email)
                     <div class="mt-3">
                        <a href="mailto:{{ $guardian->email }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-400 dark:hover:bg-indigo-900/20 dark:border-indigo-900/20 transition-colors w-full">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            <span class="text-sm font-medium">إرسال بريد إلكتروني</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="md:col-span-2">
            <x-ui.empty-state 
                icon="users"
                title="لا يوجد أولياء أمور"
                description="لم يتم إضافة أي ولي أمر لهذا الطالب حتى الآن."
            />
        </div>
    @endforelse
</div>

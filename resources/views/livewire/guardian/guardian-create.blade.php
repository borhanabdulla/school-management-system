<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('guardian.add_new') }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ __('guardian.enter_personal_info') }}</p>
        </div>

        <form wire:submit="save" class="p-6 space-y-8">
            <!-- البيانات الشخصية -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 border-b pb-2">{{ __('guardian.personal_info') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.first_name') }}</label>
                        <input wire:model="form.first_name" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.last_name') }}</label>
                        <input wire:model="form.last_name" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.national_id') }}</label>
                        <input wire:model="form.national_id" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.national_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.nationality') }}</label>
                        <select wire:model="form.nationality_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('guardian.select_nationality') }}</option>
                            @foreach(\App\Domains\Shared\Models\Country::all() as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('form.nationality_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- بيانات الاتصال والعمل -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 border-b pb-2">{{ __('guardian.contact_work_info') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.phone') }}</label>
                        <input wire:model="form.phone" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.email') }}</label>
                        <input wire:model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">{{ __('guardian.email_note') }}</p>
                        @error('form.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.employer') }}</label>
                        <input wire:model="form.employer" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.employer') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.work_phone') }}</label>
                        <input wire:model="form.work_phone" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.work_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- العنوان -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 border-b pb-2">{{ __('guardian.address') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.city') }}</label>
                        <input wire:model="form.city" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.district') }}</label>
                        <input wire:model="form.district" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.district') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('guardian.street_name') }}</label>
                        <input wire:model="form.street_name" type="text" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500">
                        @error('form.street_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-6">
                <a href="{{ route('guardians.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('guardian.cancel') }}</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200">{{ __('guardian.save_guardian') }}</button>
            </div>
        </form>
    </div>
</div>

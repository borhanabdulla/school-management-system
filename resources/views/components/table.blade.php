@props(['headers'])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200']) }}>
        <thead class="bg-slate-50">
            <tr>
                @foreach ($headers as $header)
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-200">
            {{ $slot }}
        </tbody>
    </table>
</div>

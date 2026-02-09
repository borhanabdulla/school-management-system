@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- لاحظ إضافة academic. قبل اسم المكون --}}
            <livewire:academic.subject-manager />
        </div>
    </div>
@endsection

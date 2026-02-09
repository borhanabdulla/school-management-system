@extends('layouts.app')

@section('content')
    <div class="bg-surface dark:bg-gray-900 rounded-lg shadow-sm p-6">
        <livewire:academic.term-manager :academic_year_id="$academicYearId" />
    </div>
@endsection

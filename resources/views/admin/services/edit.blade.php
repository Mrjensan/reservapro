@extends('layouts.app')

@section('title', 'Editar servico')

@section('content')
<h1 class="h4 mb-4">Editar servico</h1>

<form method="POST" action="{{ route('admin.services.update', $service) }}" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body p-4">
        @include('admin.services._form')
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.services.index') }}" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </div>
</form>
@endsection

@extends('layouts.app')

@section('title', 'Novo servico')

@section('content')
<h1 class="h4 mb-4">Cadastrar servico</h1>

<form method="POST" action="{{ route('admin.services.store') }}" class="card shadow-sm">
    @csrf
    <div class="card-body p-4">
        @include('admin.services._form')
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('admin.services.index') }}" class="btn btn-link">Cancelar</a>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </div>
</form>
@endsection

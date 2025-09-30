@extends('layouts.app')

@section('title', 'Login administrativo')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Acesso administrativo</h1>
                <p class="text-muted mb-4">Utilize seu e-mail corporativo para acessar o painel de reservas.</p>
                <form method="POST" action="{{ route('login.perform') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required autofocus value="{{ old('email') }}">
                    </div>
                    <div>
                        <label class="form-label" for="password">Senha</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Manter-me conectado</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

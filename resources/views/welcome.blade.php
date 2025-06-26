@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="p-5 mb-4 bg-light rounded-3 text-center">
                <div class="container-fluid py-5">
                    <h1 class="display-5 fw-bold">Welcome to GigDaemon</h1>
                    <p class="fs-4">Your personal assistant for managing freelance projects, clients, and invoices.</p>
                    <hr class="my-4">
                    <p>Please log in or register to access your dashboard.</p>
                    @if (Route::has('login'))
                        <a class="btn btn-primary btn-lg" href="{{ route('login') }}" role="button">Login</a>
                    @endif
                    @if (Route::has('register'))
                         <a class="btn btn-secondary btn-lg" href="{{ route('register') }}" role="button">Register</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <div class="mb-4">
                            <h3 class="mb-0"><b>Login</b></h3>
                            <p class="text-muted mt-1 mb-0">E-Parking</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success py-2">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('login.authenticate') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="auth-footer row">
                    <div class="col my-1">
                        <p class="m-0 text-muted">Copyright © {{ date('Y') }} E-Parkir dinas perhubungan kota Probolinggo</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

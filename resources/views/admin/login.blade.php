<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Faces</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --faces-green: #2e7d32; --faces-green-dark: #1b5e20; }
        body { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 400px; width: 100%; border: none; box-shadow: 0 4px 24px rgba(0,0,0,.1); }
        .btn-green { background: var(--faces-green); border-color: var(--faces-green); color: #fff; }
        .btn-green:hover { background: var(--faces-green-dark); border-color: var(--faces-green-dark); color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card login-card mx-auto">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo" height="80" class="mb-2" onerror="this.style.display='none'">
                            <h3 class="fw-bold" style="color:#2e7d32;">FACES</h3>
                            <p class="text-muted mb-0">Faculty of Computing</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger py-2">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.login') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-green w-100 py-2 fw-semibold">Sign In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

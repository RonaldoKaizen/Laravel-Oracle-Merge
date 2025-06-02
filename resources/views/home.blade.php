<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #progress-container {
            display: none;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Hola, {{ Auth::user()->name }}</h2>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-danger">Cerrar sesión</button>
            </form>
        </div>

        <button id="btn-sync" class="btn btn-success">Actualizar Alumnos</button>

        <div id="progress-container" class="mt-4">
            <div class="progress">
                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%">0%</div>
            </div>
            <p id="status-text" class="mt-2"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btn-sync').click(function() {
                $('#btn-sync').attr('disabled', true);
                $('#progress-container').show();

                let progressBar = $('#progress-bar');
                let statusText = $('#status-text');
                let width = 0;

                // Simula avance hasta 90% mientras se ejecuta la petición
                let interval = setInterval(function() {
                    if (width >= 90) {
                        clearInterval(interval);
                    } else {
                        width += 10;
                        progressBar.css('width', width + '%').text(width + '%');
                    }
                }, 200);

                $.ajax({
                    url: "{{ route('alumno.sync') }}",
                    method: "POST",
                    data: {},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        clearInterval(interval);
                        progressBar.css('width', '100%').text('100%');
                        statusText.text(response.message);
                    },
                    error: function(xhr) {
                        clearInterval(interval);
                        progressBar.removeClass('progress-bar-animated');
                        statusText.text('Ocurrió un error. Intenta de nuevo.');
                    },
                    complete: function() {
                        $('#btn-sync').attr('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="display-1 text-danger mb-4">
                        <i class="fas fa-exclamation-triangle"></i> 404
                    </h1>
                    <h2 class="mb-4">Страница не найдена</h2>
                    <p class="lead mb-4">
                        Запрашиваемая страница не существует или была перемещена.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="/" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> На главную
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i> Назад
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
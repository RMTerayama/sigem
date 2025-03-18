<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Falha na Autenticação</title>
</head>

<body>

    <?php

    if (!isset($mensagem)) {
        // Se não foi definido uma mensagem via "include", verifica se há erro na URL
        $mensagem = "Token inválido ou expirado. Por favor, faça login novamente.";

        if (isset($_GET['erro'])) {
            if ($_GET['erro'] === 'nao_autenticado') {
                $mensagem = "Você não está autenticado. Faça login para acessar o sistema.";
            } elseif ($_GET['erro'] === 'sem_permissao') {
                $mensagem = "Você não tem permissão para acessar esta página.";
            }
        }
    }
    ?>

    <img src="src/PapelParedeMaio2022.jpg" style="width: 100%; height: 100%; object-fit: cover;" alt="">

    <div class="modal fade" id="modalFalha" tabindex="-1" aria-labelledby="Imagem da prefeitura" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ModalLabel">Acesso Negado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo $mensagem; ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                        onclick="window.location.href='/sigem/login.php'">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var modal = new bootstrap.Modal(document.getElementById('modalFalha'));
            modal.show();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>

</body>

</html>

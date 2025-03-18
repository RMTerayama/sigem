<?php
session_start(); // Inicia a sessão para acessar o role do usuário

$role = $_SESSION["usuario_autenticado"]["role"] ?? 'guest';
$nome = $_SESSION["usuario_autenticado"]["nome"] ?? 'name_NotFound';
$id_usuario=$_SESSION["usuario_autenticado"]["id_user"] ?? 'id_NotFound';


?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #004F99;">
    <div class="container-fluid">
        <a class="navbar-brand" href="./protegido/dashboard.php">
            SIGEM
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Usuário (somente admin pode ver) -->
                <?php if ($role === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Usuário
                    </a>
                    <div class="dropdown-menu" aria-labelledby="usuarioDropdown">
                        <a class="dropdown-item" href="#">Criar novo usuário</a>
                        <a class="dropdown-item" href="#">Pesquisar por usuário</a>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Sistemas -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="sistemasDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Sistemas
                    </a>
                    <div class="dropdown-menu" aria-labelledby="sistemasDropdown">
                        <a class="dropdown-item" href="#">SIGEM</a>
                    </div>
                </li>

                <!-- Estoque (apenas coordenador e admin) -->
                <?php if ($role === 'coordenador' || $role === 'admin' ): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="estoqueDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Estoque
                    </a>
                    <div class="dropdown-menu" aria-labelledby="estoqueDropdown">
                        <a class="dropdown-item" href="#">Relatório Geral de itens</a>
                        <a class="dropdown-item" href="/sigem/itens.php">Gestão de itens</a>
                    </div>
                </li>
                <?php endif; ?>

                <!-- Termo -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="termoDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Termo
                    </a>
                    <div class="dropdown-menu" aria-labelledby="termoDropdown">
                        <a class="dropdown-item" href="/sigem/termo.php">Gestão de termos</a>
                    </div>
                </li>
            </ul>

            <!-- Nome do Usuário e Botão Sair -->
                <div class="d-flex ms-auto align-items-center">
                    <div>
                        <a href="#">
                            <span class="text-white me-3 fw-bold" id="usuarioNome"><?php echo $nome; ?></span>
                        </a>
                       
                    </div>

                    <form action="/sigem/api/login-jwt/auth/logout.php" method="POST">
                        <button class="btn btn-danger" type="submit">Sair</button>
                    </form>
                </div>

        </div>
    </div>
</nav>



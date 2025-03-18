<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Bloqueado</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            background-color: #173557; /* Cor de fundo */
            color: #FFFFFF; /* Cor do texto */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            text-align: center;
        }
        .btn-login {
            background-color: #D41317; /* Cor do botão */
            border: none;
            color: #FFFFFF; /* Cor do texto do botão */
            padding: 10px 20px;
            font-size: 1.2rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-login:hover {
            background-color: #b31013; /* Cor do botão ao passar o mouse */
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .material-icons {
            font-size: 2.5rem; /* Tamanho dos ícones */
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Ícone de bloqueio -->
        <h1>
            <i class="material-icons">block</i>
            Acesso Bloqueado
        </h1>
        <p>Você não tem permissão para acessar este conteúdo.</p>
        <!-- Botão com ícone de login -->
        <a href="login.php" class="btn btn-login">
            <i class="material-icons">login</i>
            Ir para a Tela de Login
        </a>
    </div>

    <!-- Bootstrap JS (opcional, se precisar de funcionalidades JS do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
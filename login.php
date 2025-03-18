<?php 
session_start();

if (isset($_SESSION['token'])) {
    header("Location: protegido/dashboard.php");
    exit;
}

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST["usuario"]; // ✅ Alterado de 'email' para 'usuario'
    $senha = $_POST["senha"];

    $context = stream_context_create([
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json",
            "content" => json_encode(["usuario" => $usuario, "senha" => $senha]), // ✅ Agora envia 'usuario'
            "ignore_errors" => true
        ]
    ]);

    $response = file_get_contents("http://172.18.2.49/sigem/api/login-jwt/auth/login.php", false, $context);

    if ($response === false || empty($response)) {
        $error = "Erro ao conectar ao servidor. Tente novamente.";
    } else {
        $data = json_decode($response, true);

        if ($data === null) {
            $error = "Erro ao processar a resposta do servidor.";
        } else if (isset($data["message"])) {
            $error = $data["message"];
        } else {
            $error = "Erro desconhecido ao fazer login.";
        }
    }

    if (isset($data["token"])) {
        setcookie("token", $data["token"], time() + 3600, "/");
        $_SESSION["token"] = $data["token"];
        
        if (isset($data["usuario"])) {
            $_SESSION["usuario"] = $data["usuario"];
        }

        header("Location: protegido/dashboard.php");
        exit;
    } else {
        $error = $data["message"] ?? "Erro ao fazer login.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/styleLogin.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <title>SIGEM - Login</title>
    <style>

    </style>
</head>

<body>
    <div class="container">
        <div class="left">
            <img src="./src/logopref.png" class="logo" alt="Logo">
        </div>
        <div class="right">
            <h3>Bem-vindo ao SIGEM!</h3>
            <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <input type="text" placeholder="Usuário" id="usuario" name="usuario">
                <input type="password" placeholder="Senha" id="senha" name="senha">
                <button type="submit">Entrar</button>
            </form>
            <a href="">Esqueceu a senha? Clique aqui para continuar</a>
        </div>
    </div>
</body>

</html>
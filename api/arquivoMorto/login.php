<?php 
require '../../../includes/conexoes.php';
require '../config/jwt.php';
require '../../../vendor/autoload.php';

use Firebase\JWT\JWT;

ob_clean();
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->senha)) {
    $stmt = $conn->prepare("SELECT id_user, nome, senha_hash FROM users WHERE email = :email");
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        

        if (password_verify($data->senha, $user['senha_hash'])) {
            // Criar payload do JWT
            $payload = [
                "iss" => "localhost",
                "iat" => time(),
                "exp" => time() + TOKEN_EXPIRATION,
                "sub" => $user['id_user'],
                "name" => $user['nome']
            ];
            // Gerar Token
            $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGO);

            // Armazenar token em cookie seguro
            setcookie("token", $jwt, time() + TOKEN_EXPIRATION, "/", "", true, true);

            echo json_encode([
                "message" => "Login bem-sucedido!",
                "token" => $jwt,
                "nome" => $user['nome']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Senha incorreta."]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Usuário não encontrado."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Dados incompletos."]);
}

// http_response_code apresentando comportamento anômalo, terminam a execução da função antes de enviar a resposta json

?>


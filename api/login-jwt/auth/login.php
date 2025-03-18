<?php 
require '../../../includes/conexoes.php';
require '../config/jwt.php';
require '../../../vendor/autoload.php';

use Firebase\JWT\JWT;

ob_clean();
header("Content-Type: application/json");

// Recebe os dados da requisição
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->usuario) && !empty($data->senha)) { // ✅ Agora verifica o usuário em vez do email
    $stmt = $conn->prepare("SELECT id_user, usuario, nome, senha_hash, departamento_id, role FROM users WHERE usuario = :usuario");
    $stmt->bindParam(":usuario", $data->usuario);
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
                "usuario" => $user['usuario'], // ✅ Agora usa 'usuario'
                "name" => $user['nome'], 
                "role" => $user['role'],
                "departamento_id" => $user['departamento_id']
            ];
            // Gerar Token
            $jwt = JWT::encode($payload, JWT_SECRET, JWT_ALGO);
        
            // Armazenar token em cookie seguro
            setcookie("token", $jwt, time() + TOKEN_EXPIRATION, "/", "", false, true);
        
            // Escolher a página de destino com base na role
            $redirectPage = ($user['role'] === 'padrao') ? '/sigem/protegido/termo.php' : '/sigem/protegido/dashboard.php';
        
            echo json_encode([
                "message" => "Login bem-sucedido!",
                "token" => $jwt,
                "usuario" => $user['usuario'], // ✅ Agora retorna 'usuario'
                "nome" => $user['nome'],
                "role" => $user['role'],
                "departamento_id" => $user['departamento_id'],
                "redirect" => $redirectPage
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
?>

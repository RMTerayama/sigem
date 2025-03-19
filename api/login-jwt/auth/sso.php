<?php
require '../../../includes/conexoes.php';
require '../config/jwt.php';
require '../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

// 🔹 Definir domínios permitidos para CORS
$allowedOrigins = [
    "http://localhost:3000",
    "http://172.18.2.49:3002" // Adicione novos domínios se necessário
];

// 🔹 Captura a origem da requisição
$origin = $_SERVER['HTTP_ORIGIN'] ?? "";

// 🔹 Configuração CORS dinâmica
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With, Accept, Origin");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// 🔹 Responder a requisições OPTIONS (Preflight) e sair
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 🔹 Captura o token JWT enviado no cabeçalho Authorization
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "No authorization header"]);
    exit;
}

$authHeader = $headers['Authorization'];
list($bearer, $jwt) = explode(' ', $authHeader);

if ($bearer !== 'Bearer') {
    http_response_code(401);
    echo json_encode(["message" => "Invalid token format"]);
    exit;
}

try {
    // 🔹 Decodifica o token JWT recebido do UniTL
    $decoded = JWT::decode($jwt, new Key(JWT_SECRET, JWT_ALGO));

    // 🔹 Verifica se o usuário existe no banco de dados
    $stmt = $conn->prepare("SELECT id_user, usuario, nome, departamento_id, role FROM users WHERE id_user = :id_user");
    $stmt->bindParam(":id_user", $decoded->sub);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 🔹 Define o cookie `token` corretamente
        setcookie("token", $jwt, [
            "expires" => time() + TOKEN_EXPIRATION,
            "path" => "/",
            "secure" => false, // ⚠️ HTTPS não está sendo usado
            "httponly" => true,
            "samesite" => "Lax"
        ]);

        // 🔹 Armazena os dados do usuário na sessão
        session_start();
        $_SESSION["usuario_autenticado"] = [
            "id_user" => $user['id_user'],
            "usuario" => $user['usuario'],
            "nome" => $user['nome'],
            "role" => $user['role'],
            "departamento_id" => $user['departamento_id']
        ];
        session_write_close();

        // 🔹 Escolhe a página de destino com base no papel do usuário
        $redirectPage = ($user['role'] === 'padrao') ? '/sigem/protegido/termo.php' : '/sigem/protegido/dashboard.php';

        // 🔹 Retorna JSON para o frontend com a URL de redirecionamento
        echo json_encode([
            "message" => "SSO login success",
            "redirect" => $redirectPage
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Usuário do token não encontrado no banco"]);
        exit;
    }

} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["message" => "Token expired"]);
    exit;
} catch (\Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid token"]);
    exit;
}
?>

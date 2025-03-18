<?php
include '../includes/conexoes.php';

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Lê os dados enviados via POST
$data = json_decode(file_get_contents('php://input'), true);
$termoId = $data['termoId'];
$assinatura = $data['assinatura'];

// Verifica se o dado contém o cabeçalho "data:image/png;base64,"
if (strpos($assinatura, 'data:image/png;base64,') === 0) {
    // Remove o cabeçalho para armazenar apenas o base64 puro
    $assinatura = substr($assinatura, strlen('data:image/png;base64,'));
}

// Valida se a string é um base64 válido
if (!base64_decode($assinatura, true)) {
    echo json_encode(['success' => false, 'error' => 'A assinatura não é um base64 válido.']);
    exit;
}

try {
    // Prepara a query para atualizar o termo com a assinatura
    $sql = "UPDATE termos_saida SET assinatura = :assinatura, assinado = 1 WHERE id = :termoId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':assinatura' => $assinatura,
        ':termoId' => $termoId
    ]);

    // Retorna sucesso
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Retorna erro com detalhes
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
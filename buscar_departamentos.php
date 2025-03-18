<?php
// Inclui a conexão com o banco de dados
include './includes/conexao_pmtl.php';

// Verifica se o ID da secretaria foi enviado
if (isset($_GET['secretaria_id'])) {
    $secretaria_id = intval($_GET['secretaria_id']); // Converte para inteiro para segurança

    // Busca os departamentos da secretaria selecionada
    $sql = "SELECT id, nome FROM departamentos WHERE secretaria_id = :secretaria_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':secretaria_id' => $secretaria_id]);
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os departamentos como JSON
    header('Content-Type: application/json');
    echo json_encode($departamentos);
} else {
    // Retorna um erro se o ID da secretaria não for fornecido
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID da secretaria não fornecido.']);
}
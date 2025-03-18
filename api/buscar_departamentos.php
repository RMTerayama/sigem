<?php
header('Content-Type: application/json');
include '../includes/conexoes.php';

if (!isset($_GET['secretaria_id'])) {
    echo json_encode(["error" => "Nenhum ID de secretaria fornecido."]);
    exit;
}

ob_start();

$secretaria_id = intval($_GET['secretaria_id']);

$sql = "SELECT id, nome FROM departamentos WHERE secretaria_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$secretaria_id]);
$departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se não houver departamentos, retorna um JSON informando
if (empty($departamentos)) {
    echo json_encode(["error" => "Nenhum departamento encontrado para essa secretaria."]);
} else {
    echo json_encode($departamentos);
}
exit;

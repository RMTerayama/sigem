<?php
session_start();
header('Content-Type: application/json');
include '../includes/conexoes.php';

try {
    // Obtém os itens filtrados por departamento
    $sql_itens = "SELECT * FROM item WHERE departamento_id = :departamento_id";
    $stmt = $pdo->prepare($sql_itens);
    $stmt->bindValue(':departamento_id', $_SESSION["usuario_autenticado"]["departamento_id"], PDO::PARAM_INT);
    $stmt->execute();
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os itens em formato JSON
    echo json_encode($itens);

} catch (PDOException $e) {
    
    echo json_encode(["error" => "Erro ao buscar itens: " . $e->getMessage()]);
}
?>

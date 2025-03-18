<?php
include './includes/conexao_sigem.php';

if (isset($_POST['id_item']) && isset($_POST['quantidade'])) {
    $id_item = $_POST['id_item'];
    $quantidade = $_POST['quantidade'];

    // Atualiza a quantidade no banco
    $stmt = $pdo->prepare("UPDATE item SET quantidade = :quantidade WHERE id_item = :id_item");
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Quantidade atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar a quantidade.";
    }
}
?>

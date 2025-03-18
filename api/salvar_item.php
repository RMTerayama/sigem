<?php
require '../includes/conexao_sigem.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    
    // Corrigido: verifica se o checkbox foi marcado
    $patrimonio = isset($_POST['patrimonio']) && $_POST['patrimonio'] === 'on' ? 1 : 0;

    if (!empty($nome) && !empty($modelo) && !empty($descricao) && $quantidade > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO item (nome, modelo, descricao, quantidade, patrimonio) VALUES (:nome, :modelo, :descricao, :quantidade, :patrimonio)");
            $stmt->execute([
                ':nome' => $nome,
                ':modelo' => $modelo,
                ':descricao' => $descricao,
                ':quantidade' => $quantidade,
                ':patrimonio' => $patrimonio
            ]);

            echo json_encode(["success" => true, "message" => "Item cadastrado com sucesso!"]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Erro ao cadastrar item: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Todos os campos são obrigatórios!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Requisição inválida!"]);
}
?>

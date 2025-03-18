<?php

require "../includes/conexoes.php";

header('Content-Type: application/json'); // 🔹 Garante que a resposta será JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Responsável por atualizar o banco de dados e impedir que arquivos antigos (leia-se atualizados) fiquem armazenados desnecessariamente
function atualizaBD($pdo, $caminho, $termo_id){

    $stmt = $pdo->prepare("SELECT dir_pdf from termos_saida where numero_termo = :termo_id");
    $stmt->bindParam(":termo_id", $termo_id);
    $stmt->execute();

    $termo = $stmt->fetch(PDO::FETCH_ASSOC);

    //Arrumar aqui
    if (!is_null($termo['dir_pdf'])) {
        // 🔹 Converte a URL do banco para o caminho absoluto no servidor
        $caminho_local = str_replace("http://172.18.2.49/sigem/", "/var/www/sigem/", $termo['dir_pdf']);

        // 🔹 Verifica se o arquivo realmente existe antes de tentar deletá-lo
        if (file_exists($caminho_local)) {
            unlink($caminho_local);
        }
    }


    // Atualiza banco
    $stmt = $pdo->prepare("UPDATE termos_saida SET dir_pdf = :caminho WHERE numero_termo = :termo_id");
    $stmt->bindParam(":caminho", $caminho);
    $stmt->bindParam(":termo_id", $termo_id);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // Diretório onde o arquivo será salvo
    $targetDir = __DIR__ . "/../termos/";

    // Garante que o diretório existe
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true); // 🔹 Cria a pasta se não existir
    }

    // Gera um nome único para o arquivo
    $extensao = '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $uniqName = uniqid() . $extensao;

    $targetDir = __DIR__ . "/../termos/". $uniqName;

    // Move o arquivo para a pasta de destino
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetDir)) {

        atualizaBD($pdo, "http://172.18.2.49/sigem/termos/" . $uniqName, $_POST["numero_termo"]);

        // Adiciona logging para depuração
        file_put_contents("debug_upload.log", "Arquivo salvo: " . $uniqName . PHP_EOL, FILE_APPEND);

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Arquivo salvo com sucesso.", "file" => basename($uniqName)]);
        exit;
    } else {
        file_put_contents("debug_upload.log", "Erro ao mover arquivo: " . print_r(error_get_last(), true) . PHP_EOL, FILE_APPEND);
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erro ao mover o arquivo."]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Nenhum arquivo enviado."]);
    exit;
}


?>

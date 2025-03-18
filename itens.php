<?php
session_start(); // Inicia a sessão para acessar os dados do usuário

require './api/login-jwt/auth/validate.php'; // 🔹 Garante que o usuário está autenticado
require './api/login-jwt/auth/verificar_permissao.php'; // 🔹 Importa a função de permissão

verificarPermissao(['admin', 'coordenador']); // 🔹 Apenas Admin e Coordenador podem acessar

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEM - Sistema de Gestão</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> <!-- Ícones do Google -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.9"></script>
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.13"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/styleMain.css">
    <link rel="stylesheet" href="./css/styleItens.css">
</head>
<body>
    <style>
        
    </style>
    <?php include 'navbar.php'; ?> <!-- Inclui o menu -->

    <div class="container">
        <h2 class="my-4">Lista de Itens Cadastrados</h2>

        <div class="add-item">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <span class="material-icons">add</span> Adicionar Novo Item
            </button>
        </div>

        <input type="text" id="search" class="form-control" placeholder="Pesquisar por nome ou modelo" onkeyup="searchItems()">

        <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped">
    <thead class="table-dark text-center">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Modelo</th>
            <th>Descrição</th>
            <th>Quantidade</th>
            <th>Patrimônio</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody class="text-center" id="itemList">
        <?php
        include './includes/conexao_sigem.php';

        if (empty($_SESSION["usuario_autenticado"]["departamento_id"])) {
            die("<tr><td colspan='7'>Erro: Usuário não autenticado ou departamento não definido.</td></tr>");
        }

        $departamento_id = $_SESSION["usuario_autenticado"]["departamento_id"];
        $sql_itens = "SELECT * FROM item WHERE departamento_id = :departamento_id";
        $stmt = $pdo->prepare($sql_itens);
        $stmt->bindValue(':departamento_id', $departamento_id, PDO::PARAM_INT);
        $stmt->execute();
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($itens)) {
            echo "<tr><td colspan='7' class='text-center text-muted'>Nenhum item encontrado para este departamento.</td></tr>";
        } else {
            foreach ($itens as $row) {
                echo "<tr id='item-{$row['id_item']}'>
                        <td>{$row['id_item']}</td>
                        <td>
                            <span id='nome-{$row['id_item']}'>" . htmlspecialchars($row['nome']) . "</span>
                            <input type='text' id='edit-nome-{$row['id_item']}' class='editable' style='display:none;' value='" . htmlspecialchars($row['nome']) . "' />
                        </td>
                        <td>
                            <span id='modelo-{$row['id_item']}'>" . htmlspecialchars($row['modelo']) . "</span>
                            <input type='text' id='edit-modelo-{$row['id_item']}' class='editable' style='display:none;' value='" . htmlspecialchars($row['modelo']) . "' />
                        </td>
                        <td>
                            <span id='descricao-{$row['id_item']}'>" . htmlspecialchars($row['descricao']) . "</span>
                            <input type='text' id='edit-descricao-{$row['id_item']}' class='editable' style='display:none;' value='" . htmlspecialchars($row['descricao']) . "' />
                        </td>
                        <td>
                            <button class='btn btn-sm btn-decrement' onclick='openDecrementModal({$row['id_item']}, {$row['quantidade']})'>-</button>
                            <span id='quantidade-{$row['id_item']}'>{$row['quantidade']}</span>

                            <button class='btn btn-sm btn-increment' onclick='openIncrementModal({$row['id_item']}, {$row['quantidade']})'>+</button>
                        </td>
                        <td>" . ($row['identificacao'] == 'nserie' ? "N° de série" : "Patrimônio") . "</td>
                        <td>
                            <button class='btn btn-sm btn-edit' id='edit-btn-{$row['id_item']}' onclick='editItem({$row['id_item']})' aria-label='Editar item'>
                                <span class='material-icons'>edit</span>
                            </button>
                            <button class='btn btn-sm btn-save' id='save-edit-btn-{$row['id_item']}' onclick='saveItem({$row['id_item']})' style='display:none;' aria-label='Salvar edição'>
                                <span class='material-icons'>save</span>
                            </button>
                        </td>
                    </tr>";
            }
        }
        ?>
    </tbody>
</table>
</div>

    </div>


    <!-- Modal para incremento de quantidade -->
    <div class="modal fade" id="incrementModal" tabindex="-1" role="dialog" aria-labelledby="incrementModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incrementModalLabel">Incrementar Quantidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>

                </div>
                <div class="modal-body">
                    <label for="incrementQuantity">Quantidade a ser adicionada:</label>
                    <input type="number" id="incrementQuantity" class="form-control" min="1" />
                    <input type="hidden" id="itemId" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveIncrement()">Salvar</button>

                </div>
            </div>
        </div>
    </div>




<!-- Modal para Cadastro de Novo Item -->
<div class="modal fade" id="addItemModal" tabindex="-1"  role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- modal-dialog-centered centraliza o modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Cadastrar Novo Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome:</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="modelo" class="form-label">Modelo:</label>
                        <input type="text" class="form-control" id="modelo" name="modelo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição:</label>
                        <input type="text" class="form-control" id="descricao" name="descricao" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade:</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="patrimonio" class="form-label">Identificação?</label>
                        <select class="form-select" id="patrimonio" name="patrimonio" required>     
                            <option value="1">Numero de série</option>
                            <option value="0">Numero de patrimonio</option>
                        </select>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal para Decrementar Quantidade -->
<div class="modal fade" id="decrementModal" tabindex="-1" aria-labelledby="decrementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="decrementModalLabel">Decrementar Quantidade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="decrementForm">
                    <div class="mb-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidadedecremento" required>
                    </div>
                    <div class="mb-3">
                        <label for="justificativa" class="form-label">Justificativa</label>
                        <textarea class="form-control" id="justificativa" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="decrementQuantity()">Salvar Alteração</button>
            </div>
        </div>
    </div>
</div>

 <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS e dependências -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="./js/script.js"></script>

    <script>
 $(document).ready(function () {
        $("#addItemForm").submit(function (event) {
            event.preventDefault(); // Evita o recarregamento da página
            
            const formData = {
                nome: $("#nome").val(),
                modelo: $("#modelo").val(),
                descricao: $("#descricao").val(),
                quantidade: $("#quantidade").val(),
                patrimonio: $("#patrimonio").val()
            };

            $.ajax({
                url: "./api/add_item.php",
                method: "POST",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        alert("Item cadastrado com sucesso!");
                        location.reload(); // Atualiza a página
                    } else {
                        alert("Erro ao cadastrar: " + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Erro AJAX:", xhr.responseText);
                    alert("Erro ao comunicar com o servidor.");
                }
            });
        });
    });


   // Função para abrir o modal de incremento
        function openIncrementModal(itemId, currentQuantity) {
            // Definindo o ID do item e a quantidade atual
            document.getElementById('itemId').value = itemId;
            $('#incrementModal').modal('show'); // Mostra o modal
        }
    document.getElementById('openModalBtn').addEventListener('click', function() {
        console.log('Botão clicado! Tentando abrir modal...');
        $('#addItemModal').modal('show');
    });

// Função para salvar o incremento de quantidade
function saveIncrement() {
    const itemId = document.getElementById('itemId').value;
    const incrementQuantity = document.getElementById('incrementQuantity').value;

    if (incrementQuantity && incrementQuantity > 0) {
        $.ajax({
            url: './api/increment_quantity.php',
            method: 'POST',
            data: {
                id_item: itemId,
                increment_quantity: incrementQuantity
            },
            dataType: 'json', // Garante que a resposta será tratada como JSON automaticamente
            success: function(response) {
                console.log('Resposta do servidor:', response);

                if (response.status === 'success') {
                    $('#incrementModal').modal('hide');
                    alert('Quantidade atualizada com sucesso! Nova quantidade: ' + response.nova_quantidade);
                    location.reload();
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erro AJAX:', xhr.responseText);
                alert('Erro ao comunicar com o servidor.');
            }
        });
    } else {
        alert('Por favor, insira uma quantidade válida.');
    }
}

        // Função para realizar a pesquisa instantânea
        function searchItems() {
            const input = document.getElementById('search').value.toLowerCase();
            const rows = document.getElementById('itemList').getElementsByTagName('tr');
            
            Array.from(rows).forEach(row => {
                const cols = row.getElementsByTagName('td');
                const nome = cols[1].textContent.toLowerCase();
                const modelo = cols[2].textContent.toLowerCase();

                if (nome.includes(input) || modelo.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Função para iniciar a edição do item
        function editItem(itemId) {
    document.getElementById(`nome-${itemId}`).style.display = 'none';
    document.getElementById(`modelo-${itemId}`).style.display = 'none';
    document.getElementById(`descricao-${itemId}`).style.display = 'none';

    document.getElementById(`edit-nome-${itemId}`).style.display = 'inline';
    document.getElementById(`edit-modelo-${itemId}`).style.display = 'inline';
    document.getElementById(`edit-descricao-${itemId}`).style.display = 'inline';

    document.getElementById(`save-edit-btn-${itemId}`).style.display = 'inline'; // Exibe o botão "Salvar"
    document.getElementById(`edit-btn-${itemId}`).style.display = 'none'; // Esconde o botão "Editar"


}

function saveItem(itemId) {
    const nome = document.getElementById(`edit-nome-${itemId}`).value;
    const modelo = document.getElementById(`edit-modelo-${itemId}`).value;
    const descricao = document.getElementById(`edit-descricao-${itemId}`).value;

    $.ajax({
        url: './api/edit_item.php', 
        method: 'POST',
        data: {
            id_item: itemId,
            nome: nome,
            modelo: modelo,
            descricao: descricao
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert('Item atualizado com sucesso!');

                // Atualiza os elementos da tabela com os novos valores
                document.getElementById(`nome-${itemId}`).textContent = nome;
                document.getElementById(`modelo-${itemId}`).textContent = modelo;
                document.getElementById(`descricao-${itemId}`).textContent = descricao;

                // Oculta os inputs e exibe os valores atualizados
                document.getElementById(`nome-${itemId}`).style.display = 'inline';
                document.getElementById(`modelo-${itemId}`).style.display = 'inline';
                document.getElementById(`descricao-${itemId}`).style.display = 'inline';

                document.getElementById(`edit-nome-${itemId}`).style.display = 'none';
                document.getElementById(`edit-modelo-${itemId}`).style.display = 'none';
                document.getElementById(`edit-descricao-${itemId}`).style.display = 'none';

                document.getElementById(`save-edit-btn-${itemId}`).style.display = 'none'; // Esconde o botão "Salvar"
                document.getElementById(`edit-btn-${itemId}`).style.display = 'inline'; // Exibe o botão "Editar"

            } else {
                alert('Erro ao atualizar item: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro AJAX:', xhr.responseText);
            alert('Erro ao comunicar com o servidor.');
        }
    });
}







function openDecrementModal(id_item, quantidade) {
    // Definir os valores dos inputs com os dados do item
    document.getElementById('quantidade').value = quantidade;
    document.getElementById('justificativa').value = ''; // Limpar campo de justificativa

    // Salvar o id do item no modal
    document.getElementById('decrementModal').dataset.itemId = id_item;

    // Abrir o modal
    var myModal = new bootstrap.Modal(document.getElementById('decrementModal'));
    myModal.show();
}

function decrementQuantity() {
    // Recupera os valores dos campos
    var id_item = document.getElementById('decrementModal').dataset.itemId;
    var quantidadedescremento = document.getElementById('quantidadedecremento').value;
    var justificativa = document.getElementById('justificativa').value;

    if (!quantidadedescremento || !justificativa) {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    // Enviar os dados para o servidor via AJAX (exemplo)
    var xhr = new XMLHttpRequest();
    xhr.open('POST', './api/decrementar_quantidade.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                // Atualizar a quantidade na tabela
                document.getElementById('quantidade-' + id_item).innerText = response.nova_quantidade;
                alert('Quantidade atualizada com sucesso!');
                    // Fechar o modal de decremento
            var decrementModal = bootstrap.Modal.getInstance(document.getElementById('decrementModal'));
            decrementModal.hide();
            } else {
                alert('Erro ao atualizar a quantidade.');
            }
        }
    };
    xhr.send('id_item=' + id_item + '&quantidadedescremento=' + quantidadedescremento + '&justificativa=' + justificativa);
}

    </script>
      <footer>
        <p>&copy; 2025 SIGEM - Sistema Integrado de Gestão de Materiais</p>
    </footer>
</body>
</html>

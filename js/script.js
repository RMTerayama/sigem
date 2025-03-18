
// document.getElementById("salvarItem").addEventListener("click", function() {
//     const nome = document.getElementById("itemNome").value.trim();
//     const modelo = document.getElementById("itemModelo").value.trim();
//     const descricao = document.getElementById("itemDescricao").value.trim();
//     const quantidade = document.getElementById("itemQuantidade").value.trim();
//     const patrimoniado = document.getElementById("itemPatrimoniado").checked ? 1 : 0; // Convertendo para número

//     if (nome && modelo && descricao && quantidade) {
//         const formData = new FormData();
//         formData.append("nome", nome);
//         formData.append("modelo", modelo);
//         formData.append("descricao", descricao);
//         formData.append("quantidade", quantidade);
//         formData.append("patrimonio", patrimoniado);

//         fetch("api/salvar_item.php", {
//             method: "POST",
//             body: formData
//         })

//         .then(response => response.json())
//         .then(data => {
//             alert(data.message);
//             if (data.success) {
//                 $("#cadastroItemModal").modal("hide");
//                 document.getElementById("cadastroItemForm").reset();
//             }
//         })
//         .catch(error => console.error("Erro:", error));
//     } else {
//         alert("Preencha todos os campos corretamente!");
//     }
// });
    

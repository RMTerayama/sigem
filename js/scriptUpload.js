document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".button-upload").forEach(button => {
        button.addEventListener("click", function () {
            const numeroTermo = this.getAttribute("data-numero-termo");
            const fileInput = this.closest("td").querySelector(".file-upload");
            fileInput.click();

            fileInput.addEventListener("change", function () {
                if (this.files.length > 0) {
                    let formData = new FormData();
                    formData.append("file", this.files[0]);
                    formData.append("numero_termo", numeroTermo);

                    fetch("/sigem/api/uploadPDF.php", {
                        method: "POST",
                        body: formData,
                    })
                    .then(response => response.text()) // Pegamos a resposta como texto primeiro
                    .then(text => {
                        console.log("Resposta do servidor (RAW):", text); // 🔹 Mostra a resposta CRUA no console

                        try {
                            let data = JSON.parse(text); // Convertendo para JSON
                            console.log("Resposta JSON do servidor:", data);

                            let mensagem = data.message || "Erro desconhecido no upload.";

                            // Exibe a mensagem na modal
                            document.getElementById("messageDialog").innerText = mensagem;
                            const modal = new bootstrap.Modal(document.getElementById('modalEnvio'));
                            modal.show();

                            setTimeout(() => {
                                document.activeElement.blur();
                                modal.hide();
                            }, 1000);

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);

                        } catch (error) {
                            console.error("Erro ao converter resposta para JSON:", error, "Resposta recebida:", text);
                            alert("Erro ao processar a resposta do servidor. Verifique o console.");
                        }
                    })
                    .catch(error => {
                        console.error("Erro na requisição:", error);
                        alert("Erro ao enviar o arquivo. Tente novamente.");
                    })
                    .finally(() => {
                        fileInput.value = ""; 
                    });
                } else {
                    console.warn("Nenhum arquivo selecionado!");
                }
            }, { once: true });
        });
    });
});

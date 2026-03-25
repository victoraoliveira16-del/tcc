document.addEventListener('DOMContentLoaded', () => {
    const btnEmprestimo = document.querySelectorAll('.nav-btn')[0];
    const btnDevolucoes = document.querySelectorAll('.nav-btn')[1];
    const subtitle = document.getElementById('card-subtitle');

    const secaoEmprestimo = document.getElementById('secao-emprestimo');
    const secaoDevolucoes = document.getElementById('secao-devolucoes');

    // Trocar para Devoluções
    btnDevolucoes.addEventListener('click', () => {
        btnEmprestimo.classList.remove('active');
        btnDevolucoes.classList.add('active');
        subtitle.innerText = "Devoluções e Atrasos";

        secaoEmprestimo.style.display = "none";  // Esconde o form
        secaoDevolucoes.style.display = "block"; // Mostra as devoluções
    });

    // Trocar para Empréstimos (SEM RELOAD)
    btnEmprestimo.addEventListener('click', () => {
        btnDevolucoes.classList.remove('active');
        btnEmprestimo.classList.add('active');
        subtitle.innerText = "Empréstimos";

        secaoEmprestimo.style.display = "block"; // Mostra o form
        secaoDevolucoes.style.display = "none";  // Esconde as devoluções
    });

    // Validação de envio
    const form = document.getElementById('form-emprestimo');
    form.addEventListener('submit', (e) => {
        const leitor = document.querySelector('input[name="leitor"]').value;
        if (leitor.length < 3) {
            e.preventDefault();
            alert("Digite o nome completo.");
        }
    });
});

function atualizarValorMulta() {
    const select = document.getElementById('select-pagamento');
    const inputValor = document.getElementById('valor_exibicao');

    // Pega o valor da multa guardado no atributo 'data-valor' da opção selecionada
    const valor = select.options[select.selectedIndex].getAttribute('data-valor');

    if (valor) {
        // Formata para exibir como moeda
        inputValor.value = "R$ " + parseFloat(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    }
}

function verificarPix() {
    const metodo = document.getElementById('metodo_pagamento').value;
    const areaPix = document.getElementById('area-pix');

    if (metodo === 'pix') {
        areaPix.style.display = 'block'; // Mostra o QR Code
    } else {
        areaPix.style.display = 'none';  // Esconde se for outro método
    }
}
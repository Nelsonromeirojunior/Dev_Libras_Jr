<?php
header('Content-Type: application/json');

if ($_POST) {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Conexão OK! Dados recebidos: ' . print_r($_POST, true)
    ]);
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Método não é POST'
    ]);
}

<?php

/**
 * Arquivo de teste para diagnosticar problemas de conexão
 * Coloque este arquivo na mesma pasta do seu projeto
 * Acesse: http://localhost/portfolio/teste_conexao.php
 */

echo "<h1>🔍 Diagnóstico do Sistema - DEV Libras Junior</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .ok { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .warning { color: #ffc107; font-weight: bold; }
    .box { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
</style>";

echo "<div class='box'><h2>1. 🖥️ Informações do Servidor</h2>";
echo "PHP Versão: <span class='ok'>" . PHP_VERSION . "</span><br>";
echo "Servidor: <span class='ok'>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido') . "</span><br>";
echo "Data/Hora: <span class='ok'>" . date('d/m/Y H:i:s') . "</span><br>";
echo "</div>";

echo "<div class='box'><h2>2. 📦 Extensões PHP Necessárias</h2>";
$extensoes = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];
foreach ($extensoes as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: <span class='ok'>OK</span><br>";
    } else {
        echo "❌ $ext: <span class='error'>NÃO ENCONTRADA</span><br>";
    }
}
echo "</div>";

echo "<div class='box'><h2>3. 🗄️ Teste de Conexão com Banco</h2>";
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    echo "✅ Conexão com MySQL: <span class='ok'>OK</span><br>";

    // Verificar se o banco existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Banco '$dbname': <span class='ok'>EXISTE</span><br>";

        // Conectar ao banco específico
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

        // Verificar se a tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'mensagens'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela 'mensagens': <span class='ok'>EXISTE</span><br>";

            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM mensagens");
            $total = $stmt->fetch()['total'];
            echo "📊 Total de mensagens: <span class='ok'>$total</span><br>";
        } else {
            echo "❌ Tabela 'mensagens': <span class='error'>NÃO EXISTE</span><br>";
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>⚠️ SOLUÇÃO:</strong> Execute o script SQL no phpMyAdmin:<br>";
            echo "<code>http://localhost/phpmyadmin</code>";
            echo "</div>";
        }
    } else {
        echo "❌ Banco '$dbname': <span class='error'>NÃO EXISTE</span><br>";
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ SOLUÇÃO:</strong> Crie o banco 'portfolio_db' no phpMyAdmin";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "❌ Erro de conexão: <span class='error'>" . $e->getMessage() . "</span><br>";
}
echo "</div>";

echo "<div class='box'><h2>4. 📧 PHPMailer</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ PHPMailer: <span class='ok'>ENCONTRADO</span><br>";

    require_once 'vendor/autoload.php';

    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "✅ Classe PHPMailer: <span class='ok'>CARREGADA</span><br>";
    } else {
        echo "❌ Classe PHPMailer: <span class='error'>NÃO CARREGADA</span><br>";
    }
} else {
    echo "❌ PHPMailer: <span class='error'>NÃO ENCONTRADO</span><br>";
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>⚠️ SOLUÇÃO:</strong> Instale o PHPMailer:<br>";
    echo "1. Baixe do GitHub: https://github.com/PHPMailer/PHPMailer<br>";
    echo "2. Extraia na pasta 'vendor'<br>";
    echo "3. Ou use Composer: <code>composer require phpmailer/phpmailer</code>";
    echo "</div>";
}
echo "</div>";

echo "<div class='box'><h2>5. 📝 Teste do Formulário</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Dados Recebidos:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";

    $nome = $_POST['test_name'] ?? '';
    $email = $_POST['test_email'] ?? '';
    $mensagem = $_POST['test_message'] ?? '';

    if ($nome && $email && $mensagem) {
        // Tentar salvar no banco
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $stmt = $pdo->prepare("INSERT INTO mensagens (nome, email, assunto, mensagem) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, 'Teste do Sistema', $mensagem]);

            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>SUCESSO!</strong> Mensagem salva no banco com ID: " . $pdo->lastInsertId();
            echo "</div>";
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>ERRO:</strong> " . $e->getMessage();
            echo "</div>";
        }
    }
} else {
    echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
    echo "<h3>Teste Rápido do Formulário:</h3>";
    echo "Nome: <input type='text' name='test_name' value='Teste Usuario' style='width: 100%; padding: 8px; margin: 5px 0;'><br>";
    echo "Email: <input type='email' name='test_email' value='teste@email.com' style='width: 100%; padding: 8px; margin: 5px 0;'><br>";
    echo "Mensagem: <textarea name='test_message' style='width: 100%; padding: 8px; margin: 5px 0; height: 60px;'>Esta é uma mensagem de teste.</textarea><br>";
    echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;'>🧪 Testar</button>";
    echo "</form>";
}
echo "</div>";

echo "<div class='box'><h2>6. 🔗 Links Úteis</h2>";
echo "<a href='http://localhost/phpmyadmin' target='_blank'>📊 phpMyAdmin</a><br>";
echo "<a href='http://localhost/dashboard' target='_blank'>🖥️ XAMPP Dashboard</a><br>";
echo "<a href='http://localhost/portfolio' target='_blank'>🌐 Seu Portfolio</a><br>";
echo "</div>";

echo "<hr>";
echo "<p style='text-align: center; color: #6c757d;'>";
echo "🤟 DEV Libras Junior - Diagnóstico do Sistema | " . date('d/m/Y H:i:s');
echo "</p>";

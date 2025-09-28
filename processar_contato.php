<?php

/**
 * Processamento de Formulário de Contato - DEV Libras Junior
 * Integrado com o portfolio existente
 * Versão melhorada com segurança e boas práticas
 */

// Configurações do Banco de Dados (XAMPP)
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = ''; // XAMPP padrão sem senha

// Configurações do E-mail (SMTP Gmail)
$email_destino = 'devlibrasjunior@gmail.com';
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'devlibrasjunior@gmail.com';
$smtp_password = 'ilml ckqs yugu zyqs';

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Certifique-se de que o PHPMailer está instalado

// Função para sanitizar dados de entrada
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Função para validar e limpar dados
function validateAndCleanData($data)
{
    $cleaned = [];
    $errors = [];

    // Nome
    $cleaned['nome'] = sanitizeInput($data['name'] ?? '');
    if (empty($cleaned['nome'])) {
        $errors[] = 'Nome é obrigatório.';
    } elseif (strlen($cleaned['nome']) > 100) {
        $errors[] = 'Nome deve ter no máximo 100 caracteres.';
    }

    // Email
    $cleaned['email'] = sanitizeInput($data['email'] ?? '');
    if (empty($cleaned['email']) || !filter_var($cleaned['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail válido é obrigatório.';
    } elseif (strlen($cleaned['email']) > 255) {
        $errors[] = 'E-mail muito longo.';
    }

    // Assunto
    $cleaned['assunto'] = sanitizeInput($data['subject'] ?? '');
    if (strlen($cleaned['assunto']) > 200) {
        $errors[] = 'Assunto deve ter no máximo 200 caracteres.';
    }

    // Mensagem
    $cleaned['mensagem'] = sanitizeInput($data['message'] ?? '');
    if (empty($cleaned['mensagem'])) {
        $errors[] = 'Mensagem é obrigatória.';
    } elseif (strlen($cleaned['mensagem']) > 2000) {
        $errors[] = 'Mensagem deve ter no máximo 2000 caracteres.';
    }

    return ['data' => $cleaned, 'errors' => $errors];
}

// Função para enviar resposta JSON
function sendJsonResponse($success, $message, $httpCode = 200)
{
    header('Content-Type: application/json; charset=utf-8');
    if ($httpCode !== 200) {
        http_response_code($httpCode);
    }
    echo json_encode([
        'sucesso' => $success,
        'mensagem' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método não permitido.', 405);
}

// Verificar Content-Type para formulários
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (
    strpos($contentType, 'application/x-www-form-urlencoded') === false &&
    strpos($contentType, 'multipart/form-data') === false
) {
    sendJsonResponse(false, 'Tipo de conteúdo não suportado.', 415);
}

// Validar e limpar dados
$validation = validateAndCleanData($_POST);
if (!empty($validation['errors'])) {
    sendJsonResponse(false, implode('<br>', $validation['errors']), 400);
}

$dados = $validation['data'];

// Verificar taxa de limite simples (opcional)
$userIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
// Aqui você poderia implementar um sistema de rate limiting mais robusto

try {
    // Conectar ao MySQL/MariaDB
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false
        ]
    );

    // Inserir dados no banco
    $stmt = $pdo->prepare("
        INSERT INTO mensagens (nome, email, assunto, mensagem, ip_origem, data_criacao)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $dados['nome'],
        $dados['email'],
        $dados['assunto'],
        $dados['mensagem'],
        $userIP
    ]);

    $mensagemId = $pdo->lastInsertId();

    // Configurar e enviar e-mail
    $mail = new PHPMailer(true);

    // Configurações do servidor SMTP
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp_port;
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // Configurações do e-mail
    $mail->setFrom($smtp_username, 'Portfolio - Formulário de Contato');
    $mail->addAddress($email_destino, 'Libras Junior');
    $mail->addReplyTo($dados['email'], $dados['nome']);

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = !empty($dados['assunto']) ?
        'Portfolio: ' . $dados['assunto'] :
        'Nova mensagem de contato do portfolio';

    $mail->Body = "
    <!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Nova Mensagem de Contato</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                background-color: #f4f4f4;
            }
            .container {
                background-color: white;
                margin: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                padding: 30px 20px;
            }
            .field {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            .field:last-child {
                border-bottom: none;
            }
            .label {
                font-weight: bold;
                color: #555;
                display: block;
                margin-bottom: 5px;
            }
            .value {
                color: #333;
                background-color: #f8f9fa;
                padding: 10px;
                border-radius: 4px;
                border-left: 4px solid #667eea;
            }
            .message-content {
                white-space: pre-line;
                font-size: 16px;
                line-height: 1.6;
            }
            .footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                font-size: 12px;
                color: #666;
                border-top: 1px solid #eee;
            }
            .footer p {
                margin: 5px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 Nova Mensagem de Contato</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>👤 Nome:</span>
                    <div class='value'>{$dados['nome']}</div>
                </div>
                <div class='field'>
                    <span class='label'>📧 E-mail:</span>
                    <div class='value'>{$dados['email']}</div>
                </div>
                <div class='field'>
                    <span class='label'>📋 Assunto:</span>
                    <div class='value'>" . (!empty($dados['assunto']) ? $dados['assunto'] : 'Sem assunto especificado') . "</div>
                </div>
                <div class='field'>
                    <span class='label'>💬 Mensagem:</span>
                    <div class='value message-content'>" . nl2br($dados['mensagem']) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p><strong>📅 Data:</strong> " . date('d/m/Y H:i:s') . "</p>
                <p><strong>🌐 IP:</strong> $userIP</p>
                <p><strong>🆔 ID da Mensagem:</strong> #$mensagemId</p>
                <hr style='border: none; border-top: 1px solid #ddd; margin: 15px 0;'>
                <p style='font-style: italic;'>Esta mensagem foi enviada através do formulário de contato do seu portfolio.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Versão em texto simples
    $mail->AltBody = "
    Nova Mensagem de Contato
    ========================

    Nome: {$dados['nome']}
    E-mail: {$dados['email']}
    Assunto: " . (!empty($dados['assunto']) ? $dados['assunto'] : 'Sem assunto') . "

    Mensagem:
    {$dados['mensagem']}

    ---
    Enviado em: " . date('d/m/Y H:i:s') . "
    IP: $userIP
    ID: #$mensagemId
    ";

    // Enviar e-mail
    $mail->send();

    // Resposta de sucesso
    sendJsonResponse(true, 'Mensagem enviada com sucesso! Obrigado pelo contato. Retornaremos em breve.');
} catch (PDOException $e) {
    // Log do erro (não expor detalhes ao usuário)
    error_log('Erro de banco de dados: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor. Tente novamente em alguns minutos.', 500);
} catch (Exception $e) {
    // Log do erro do PHPMailer
    error_log('Erro ao enviar e-mail: ' . $e->getMessage());

    // Mesmo com erro no e-mail, dados foram salvos
    sendJsonResponse(
        false,
        'Sua mensagem foi salva, mas houve um problema no envio do e-mail. Entre em contato diretamente: devlibrasjunior@gmail.com',
        500
    );
} catch (Throwable $e) {
    // Captura qualquer outro erro inesperado
    error_log('Erro inesperado: ' . $e->getMessage());
    sendJsonResponse(false, 'Erro interno do servidor. Tente novamente mais tarde.', 500);
}

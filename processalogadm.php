<?php
session_start();
require_once 'conexao.php';

// Limpa mensagens de erro antigas
unset($_SESSION['erro_login']);

// Verifica se o formulário foi enviado
if (isset($_POST['email'], $_POST['password'])) {
    
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['password'];

    try {
        // Busca o usuário pelo email
        $sql = "SELECT idadmin, nome, senha FROM tb_administradores WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        
        $admin = $stmt->fetch();

        if ($admin && $senha === $admin['senha']) {
            // Login bem-sucedido
            $_SESSION['admin'] = [
                'idadmin' => $admin['idadmin'],
                'nome' => $admin['nome']
            ];
            header('Location: paineladministrativo.php');
            exit;
        } else {
            // Login falhou
            $_SESSION['erro_login'] = "Email ou senha inválidos";
            header('Location: logadm.php');
            exit;
        }
        
    } catch (PDOException $e) {
        $_SESSION['erro_login'] = "Erro ao processar login";
        header('Location: logadm.php');
        exit;
    }
} else {
    $_SESSION['erro_login'] = "Por favor, preencha todos os campos";
    header('Location: logadm.php');
    exit;
}

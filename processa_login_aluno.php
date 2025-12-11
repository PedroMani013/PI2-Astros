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
        $sql = "SELECT idaluno, nome, senha FROM tb_alunos WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        
        $aluno = $stmt->fetch();

        if ($aluno && $senha === $aluno['senha']) {
            // Login bem-sucedido
            $_SESSION['aluno'] = [
                'idaluno' => $aluno['idaluno'],
                'nome' => $aluno['nome']
            ];
            header('Location: eleicoes_aluno.php');
            exit;
        } else {
            // Login falhou
            $_SESSION['erro_login'] = "Email ou senha inválidos";
            header('Location: login_aluno.php');
            exit;
        }
        
    } catch (PDOException $e) {
        $_SESSION['erro_login'] = "Erro ao processar login";
        header('Location: login_aluno.php');
        exit;
    }
} else {
    $_SESSION['erro_login'] = "Por favor, preencha todos os campos";
    header('Location: login_aluno.php');
    exit;
}

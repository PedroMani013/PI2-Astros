# cadastro.php
<?php
 if (isset($_SESSION['erro_cadastro'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?php 
        echo $_SESSION['erro_cadastro'];
        unset($_SESSION['erro_cadastro']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['sucesso_cadastro'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?php 
        echo $_SESSION['sucesso_cadastro'];
        unset($_SESSION['sucesso_cadastro']);
        ?>
    </div>
<?php endif; 
session_start();
require_once 'conexao.php';

// Gera token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<form action="processa_cadastro.php" method="POST" class="space-y-4">
    <input type="hidden" name="csrf_token" 
           value="<?php echo $_SESSION['csrf_token']; ?>">

    <div>
        <label for="nome" class="block text-sm font-medium text-gray-700">
            Nome Completo
        </label>
        <input type="text" name="nome" id="nome" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
               minlength="3" maxlength="100">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
            E-mail
        </label>
        <input type="email" name="email" id="email" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>

    <div>
        <label for="senha" class="block text-sm font-medium text-gray-700">
            Senha
        </label>
        <input type="password" name="senha" id="senha" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
               minlength="8">
        <p class="mt-1 text-sm text-gray-500">
            Mínimo 8 caracteres, incluindo letras e números
        </p>
    </div>

    <div>
        <label for="confirma_senha" class="block text-sm font-medium text-gray-700">
            Confirme a Senha
        </label>
        <input type="password" name="confirma_senha" id="confirma_senha" required
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
    </div>

    <button type="submit" 
            class="w-full bg-indigo-600 text-white rounded-md py-2 hover:bg-indigo-700">
        Cadastrar
    </button>
</form> 
<?php
session_start();
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Login</h2>
    <form action="login.php" method="post">
        <label>E-mail:</label><br>
        <input type="text" name="email"><br>
        <label>Senha:</label><br>
        <input type="password" name="senha"><br><br>
        <input type="submit" value="Entrar">
    </form>
    <br>
    <a href="cadastro.php">Cadastrar-se</a> | <a href="filmes.php">Ver Filmes</a>
    <hr>
    <?php
    if (isset($_POST['email']) && isset($_POST['senha'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $senha_hash = hash("sha256", $senha);

        $servidor = "localhost";
        $usuario = "root";
        $senha_db = "";
        $banco = "barrefertas";

        $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

        $comando = "SELECT `id`, `nome`, `nascimento` FROM `usuarios` WHERE `email` = '$email' AND `senha` = '$senha_hash'";
        $stm = $conexao->prepare($comando);
        $stm->execute();

        if ($resultado = $stm->fetch(PDO::FETCH_ASSOC)) {
            $partes = explode("-", $resultado['nascimento']);
            $ano_nascimento = $partes[0];
            $idade = 2026 - $ano_nascimento;

            $_SESSION['id_usuario'] = $resultado['id'];
            $_SESSION['nome'] = $resultado['nome'];
            $_SESSION['idade'] = $idade;

            echo "Olá, " . $_SESSION['nome'] . " (" . $_SESSION['idade'] . " anos)! Login realizado.<br>";
            echo "<a href='filmes.php'>Ver filmes</a> | <a href='novo_filme.php'>Cadastrar filme</a> | <a href='amigos.php'>Amigos</a>";
        } else {
            echo "E-mail ou senha incorretos!";
        }

        $conexao = null;
    }
    ?>
</body>
</html>
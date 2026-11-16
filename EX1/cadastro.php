<!DOCTYPE html>
<html>
<body>
    <h2>Cadastro de Usuário</h2>
    <form action="cadastro.php" method="post">
        <label>Nome:</label><br>
        <input type="text" name="nome"><br>
        <label>E-mail:</label><br>
        <input type="text" name="email"><br>
        <label>Data de Nascimento:</label><br>
        <input type="date" name="nascimento"><br>
        <label>Senha:</label><br>
        <input type="password" name="senha"><br><br>
        <input type="submit" value="Cadastrar">
    </form>
    <br>
    <a href="login.php">Ir para Login</a> | <a href="filmes.php">Ver Filmes</a>
    <hr>
    <?php
    if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['nascimento']) && isset($_POST['senha'])) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $nascimento = $_POST['nascimento'];
        $senha = $_POST['senha'];

        $senha_hash = hash("sha256", $senha);

        $servidor = "localhost";
        $usuario = "root";
        $senha_db = "";
        $banco = "barrefertas";

        $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

        $comando = "INSERT INTO `usuarios` (`id`, `nome`, `email`, `nascimento`, `senha`) VALUES (NULL, '$nome', '$email', '$nascimento', '$senha_hash')";
        $linhas = $conexao->exec($comando);

        if ($linhas > 0) {
            echo "Usuário cadastrado com sucesso!";
        } else {
            echo "Erro ao cadastrar usuário!";
        }

        $conexao = null;
    }
    ?>
</body>
</html>
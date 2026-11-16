<?php
session_start();
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Cadastrar Novo Filme</h2>
    <?php
    if (!isset($_SESSION['id_usuario'])) {
        echo "Você precisa estar logado para cadastrar um filme.<br>";
        echo "<a href='login.php'>Ir para login</a>";
    } else {
    ?>
        <p>Logado como: <?php echo $_SESSION['nome']; ?> (Idade: <?php echo $_SESSION['idade']; ?> anos) | <a href="filmes.php">Ver Filmes</a> | <a href="sair.php">Sair</a></p>
        <form action="novo_filme.php" method="post" enctype="multipart/form-data">
            <label>Nome do filme:</label><br>
            <input type="text" name="nome"><br>
            <label>Descrição:</label><br>
            <input type="text" name="descricao"><br>
            <label>Foto:</label><br>
            <input type="file" name="foto"><br>
            <label>Link:</label><br>
            <input type="text" name="link"><br>
            <label>Classificação indicativa (digite 0 para L):</label><br>
            <input type="number" name="classificacao" min="0" value="0"><br>
            <label>Nota do usuário (0 a 10):</label><br>
            <input type="number" name="nota_usuario" min="0" max="10"><br><br>
            <input type="submit" value="Salvar Filme">
        </form>
        <hr>
    <?php
        if (isset($_POST['nome']) && isset($_FILES['foto'])) {
            $id_usuario = $_SESSION['id_usuario'];
            $idade_usuario = $_SESSION['idade'];
            $nome = $_POST['nome'];
            $descricao = $_POST['descricao'];
            $link = $_POST['link'];
            $classificacao = $_POST['classificacao'];
            $nota_usuario = $_POST['nota_usuario'];

            if ($classificacao > $idade_usuario) {
                echo "Cadastro não permitido: a classificação do filme ($classificacao anos) é maior que a sua idade ($idade_usuario anos)!";
            } else {
                $caminho_foto = "imagens/" . basename($_FILES['foto']['name']);
                move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_foto);

                $servidor = "localhost";
                $usuario = "root";
                $senha_db = "";
                $banco = "barrefertas";

                $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

                $comando = "INSERT INTO `filmes` (`id`, `id_usuario`, `nome`, `descricao`, `foto`, `link`, `classificacao`, `nota_usuario`) VALUES (NULL, $id_usuario, '$nome', '$descricao', '$caminho_foto', '$link', $classificacao, $nota_usuario)";
                $linhas = $conexao->exec($comando);

                if ($linhas > 0) {
                    echo "Filme cadastrado com sucesso!";
                } else {
                    echo "Erro ao cadastrar filme!";
                }

                $conexao = null;
            }
        }
    }
    ?>
</body>
</html>
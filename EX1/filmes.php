<?php
session_start();
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Sugestões de Filmes</h2>
    <?php
    if (isset($_SESSION['nome'])) {
        echo "Olá, " . $_SESSION['nome'] . "! | <a href='novo_filme.php'>Cadastrar Filme</a> | <a href='amigos.php'>Amigos</a> | <a href='sair.php'>Sair</a>";
    } else {
        echo "<a href='login.php'>Login</a> | <a href='cadastro.php'>Cadastro</a>";
    }
    ?>
    <hr>
    <?php
    $servidor = "localhost";
    $usuario = "root";
    $senha_db = "";
    $banco = "barrefertas";

    $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

    // Registrar curtida
    if (isset($_GET['curtir']) && isset($_SESSION['id_usuario'])) {
        $id_filme_curtir = $_GET['curtir'];
        $id_usuario_logado = $_SESSION['id_usuario'];

        $stm_ver = $conexao->prepare("SELECT `id` FROM `curtidas` WHERE `id_usuario` = $id_usuario_logado AND `id_filme` = $id_filme_curtir");
        $stm_ver->execute();

        if (!$stm_ver->fetch(PDO::FETCH_ASSOC)) {
            $conexao->exec("INSERT INTO `curtidas` (`id`, `id_usuario`, `id_filme`) VALUES (NULL, $id_usuario_logado, $id_filme_curtir)");
        }
    }

    // Listar filmes cadastrados
    $comando = "SELECT `id`, `nome`, `foto`, `classificacao`, `nota_usuario` FROM `filmes` ORDER BY `id` DESC";
    $stm = $conexao->prepare($comando);
    $stm->execute();

    while ($filme = $stm->fetch(PDO::FETCH_ASSOC)) {
        $id = $filme['id'];
        $nome = $filme['nome'];
        $foto = $filme['foto'];
        $classificacao = ($filme['classificacao'] == 0) ? "L" : $filme['classificacao'] . " anos";
        $nota = $filme['nota_usuario'];

        // Quantidade de curtidas do filme
        $stm_curtidas = $conexao->prepare("SELECT `id` FROM `curtidas` WHERE `id_filme` = $id");
        $stm_curtidas->execute();
        $lista_curtidas = $stm_curtidas->fetchAll(PDO::FETCH_ASSOC);
        $qtd_curtidas = count($lista_curtidas);

        echo "<h3>$nome</h3>";
        echo "<a href='filme.php?id=$id'><img src='$foto' width='200'></a><br>";
        echo "Classificação: $classificacao | Nota: $nota/10<br>";
        echo "Curtidas: $qtd_curtidas ";

        if (isset($_SESSION['id_usuario'])) {
            echo "<a href='filmes.php?curtir=$id'>[Curtir]</a>";
        }
        echo "<br><hr>";
    }

    $conexao = null;
    ?>
</body>
</html>
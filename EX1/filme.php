<?php
session_start();
?>
<!DOCTYPE html>
<html>
<body>
    <a href="filmes.php">Voltar para Filmes</a>
    <hr>
    <?php
    $servidor = "localhost";
    $usuario = "root";
    $senha_db = "";
    $banco = "barrefertas";

    $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

    $id_filme = $_GET['id'] ?? $_POST['id_filme'] ?? 0;

    // Registrar curtida na página de detalhes
    if (isset($_GET['curtir']) && isset($_SESSION['id_usuario'])) {
        $id_usuario_logado = $_SESSION['id_usuario'];

        $stm_ver = $conexao->prepare("SELECT `id` FROM `curtidas` WHERE `id_usuario` = $id_usuario_logado AND `id_filme` = $id_filme");
        $stm_ver->execute();

        if (!$stm_ver->fetch(PDO::FETCH_ASSOC)) {
            $conexao->exec("INSERT INTO `curtidas` (`id`, `id_usuario`, `id_filme`) VALUES (NULL, $id_usuario_logado, $id_filme)");
        }
    }

    // Buscar dados do filme
    $comando = "SELECT `id`, `id_usuario`, `nome`, `descricao`, `foto`, `link`, `classificacao`, `nota_usuario` FROM `filmes` WHERE `id` = $id_filme";
    $stm = $conexao->prepare($comando);
    $stm->execute();

    if ($filme = $stm->fetch(PDO::FETCH_ASSOC)) {
        $id_dono_filme = $filme['id_usuario'];
        $classificacao = ($filme['classificacao'] == 0) ? "L" : $filme['classificacao'] . " anos";

        // Contar curtidas
        $stm_curtidas = $conexao->prepare("SELECT `id` FROM `curtidas` WHERE `id_filme` = $id_filme");
        $stm_curtidas->execute();
        $qtd_curtidas = count($stm_curtidas->fetchAll(PDO::FETCH_ASSOC));

        echo "<h2>" . $filme['nome'] . "</h2>";
        echo "<img src='" . $filme['foto'] . "' width='300'><br>";
        echo "<p>Descrição: " . $filme['descricao'] . "</p>";
        echo "<p>Classificação: $classificacao</p>";
        echo "<p>Nota do usuário: " . $filme['nota_usuario'] . "/10</p>";
        echo "<p>Link: <a href='" . $filme['link'] . "'>" . $filme['link'] . "</a></p>";
        echo "<p>Curtidas: $qtd_curtidas ";

        if (isset($_SESSION['id_usuario'])) {
            echo "<a href='filme.php?id=$id_filme&curtir=1'>[Curtir]</a>";
        }
        echo "</p><hr>";

        // Verificar se o usuário logado é amigo de quem cadastrou o filme
        $eh_amigo = false;
        if (isset($_SESSION['id_usuario'])) {
            $id_logado = $_SESSION['id_usuario'];
            $stm_amizade = $conexao->prepare("SELECT `id` FROM `amigos` WHERE (`id_usuario` = $id_logado AND `id_amigo` = $id_dono_filme) OR (`id_usuario` = $id_dono_filme AND `id_amigo` = $id_logado)");
            $stm_amizade->execute();

            if ($stm_amizade->fetch(PDO::FETCH_ASSOC)) {
                $eh_amigo = true;
            }
        }

        echo "<h3>Comentários</h3>";
        if ($eh_amigo) {
            // Salvar comentário (apenas amigos)
            if (isset($_POST['comentario'])) {
                $id_logado = $_SESSION['id_usuario'];
                $comentario = $_POST['comentario'];

                $comando_com = "INSERT INTO `comentarios` (`id`, `id_usuario`, `id_filme`, `comentario`) VALUES (NULL, $id_logado, $id_filme, '$comentario')";
                $linhas = $conexao->exec($comando_com);

                if ($linhas > 0) {
                    echo "Comentário adicionado!<br><br>";
                }
            }
            ?>
            <form action="filme.php?id=<?php echo $id_filme; ?>" method="post">
                <input type="hidden" name="id_filme" value="<?php echo $id_filme; ?>">
                <label>Novo comentário:</label><br>
                <input type="text" name="comentario">
                <input type="submit" value="Comentar">
            </form>
            <br>
            <?php
            // Mostrar comentários em filmes de amigos (últimos primeiro)
            $stm_lista = $conexao->prepare("SELECT `id_usuario`, `comentario` FROM `comentarios` WHERE `id_filme` = $id_filme ORDER BY `id` DESC");
            $stm_lista->execute();

            while ($com = $stm_lista->fetch(PDO::FETCH_ASSOC)) {
                $id_autor = $com['id_usuario'];
                $texto_com = $com['comentario'];

                $stm_user = $conexao->prepare("SELECT `nome` FROM `usuarios` WHERE `id` = $id_autor");
                $stm_user->execute();
                $autor = $stm_user->fetch(PDO::FETCH_ASSOC);
                $nome_autor = $autor['nome'] ?? "Usuário";

                echo "<p><b>$nome_autor:</b> $texto_com</p>";
            }
        } else {
            echo "<p>Apenas amigos de quem cadastrou este filme podem ver e enviar comentários.</p>";
        }
    } else {
        echo "Filme não encontrado.";
    }

    $conexao = null;
    ?>
</body>
</html>
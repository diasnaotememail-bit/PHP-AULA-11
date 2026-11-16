<?php
session_start();
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Sistema de Amizades</h2>
    <a href="filmes.php">Voltar para Filmes</a>
    <hr>
    <?php
    if (!isset($_SESSION['id_usuario'])) {
        echo "Faça <a href='login.php'>login</a> para gerenciar suas amizades.";
    } else {
        $id_logado = $_SESSION['id_usuario'];

        $servidor = "localhost";
        $usuario = "root";
        $senha_db = "";
        $banco = "barrefertas";

        $conexao = new PDO("mysql:host=$servidor;dbname=$banco", $usuario, $senha_db);

        // Enviar solicitação de amizade (tabela pendente)
        if (isset($_GET['solicitar'])) {
            $id_destino = $_GET['solicitar'];

            $stm_check = $conexao->prepare("SELECT `id` FROM `pendente` WHERE `id_usuario` = $id_logado AND `id_amigo` = $id_destino");
            $stm_check->execute();

            $stm_amigo = $conexao->prepare("SELECT `id` FROM `amigos` WHERE (`id_usuario` = $id_logado AND `id_amigo` = $id_destino) OR (`id_usuario` = $id_destino AND `id_amigo` = $id_logado)");
            $stm_amigo->execute();

            if (!$stm_check->fetch(PDO::FETCH_ASSOC) && !$stm_amigo->fetch(PDO::FETCH_ASSOC)) {
                $comando_sol = "INSERT INTO `pendente` (`id`, `id_usuario`, `id_amigo`) VALUES (NULL, $id_logado, $id_destino)";
                $conexao->exec($comando_sol);
                echo "Solicitação de amizade enviada!<br><hr>";
            } else {
                echo "Solicitação já enviada ou vocês já são amigos!<br><hr>";
            }
        }

        // Aprovar solicitação de amizade (salva na tabela amigos e remove de pendente)
        if (isset($_GET['aprovar']) && isset($_GET['solicitante'])) {
            $id_pendente = $_GET['aprovar'];
            $id_solicitante = $_GET['solicitante'];

            $comando_aprov = "INSERT INTO `amigos` (`id`, `id_usuario`, `id_amigo`) VALUES (NULL, $id_solicitante, $id_logado)";
            $linhas = $conexao->exec($comando_aprov);

            if ($linhas > 0) {
                $conexao->exec("DELETE FROM `pendente` WHERE `id` = $id_pendente");
                echo "Amizade aprovada!<br><hr>";
            }
        }
        ?>

        <h3>Solicitações Pendentes Recebidas</h3>
        <?php
        $stm_pend = $conexao->prepare("SELECT `id`, `id_usuario` FROM `pendente` WHERE `id_amigo` = $id_logado");
        $stm_pend->execute();

        while ($pend = $stm_pend->fetch(PDO::FETCH_ASSOC)) {
            $id_pend = $pend['id'];
            $id_sol = $pend['id_usuario'];

            $stm_u = $conexao->prepare("SELECT `nome` FROM `usuarios` WHERE `id` = $id_sol");
            $stm_u->execute();
            $u = $stm_u->fetch(PDO::FETCH_ASSOC);
            $nome_sol = $u['nome'] ?? "Usuário";

            echo "$nome_sol quer ser seu amigo. <a href='amigos.php?aprovar=$id_pend&solicitante=$id_sol'>[Aprovar]</a><br>";
        }
        ?>
        <hr>

        <h3>Meus Amigos</h3>
        <?php
        $stm_amigos = $conexao->prepare("SELECT `id_usuario`, `id_amigo` FROM `amigos` WHERE `id_usuario` = $id_logado OR `id_amigo` = $id_logado");
        $stm_amigos->execute();

        while ($am = $stm_amigos->fetch(PDO::FETCH_ASSOC)) {
            $id_outro = ($am['id_usuario'] == $id_logado) ? $am['id_amigo'] : $am['id_usuario'];

            $stm_u = $conexao->prepare("SELECT `nome` FROM `usuarios` WHERE `id` = $id_outro");
            $stm_u->execute();
            $u = $stm_u->fetch(PDO::FETCH_ASSOC);
            echo "- " . ($u['nome'] ?? "Usuário") . "<br>";
        }
        ?>
        <hr>

        <h3>Outros Usuários</h3>
        <?php
        $stm_todos = $conexao->prepare("SELECT `id`, `nome`, `email` FROM `usuarios` WHERE `id` != $id_logado");
        $stm_todos->execute();

        while ($outro = $stm_todos->fetch(PDO::FETCH_ASSOC)) {
            $id_outro = $outro['id'];
            $nome_outro = $outro['nome'];
            echo "$nome_outro <a href='amigos.php?solicitar=$id_outro'>[Solicitar Amizade]</a><br>";
        }

        $conexao = null;
    }
    ?>
</body>
</html>
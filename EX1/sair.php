<?php
session_start();
session_unset();
session_destroy();
echo "Sessão encerrada!<br>";
echo "<a href='login.php'>Fazer login novamente</a> | <a href='filmes.php'>Ver filmes</a>";
?>
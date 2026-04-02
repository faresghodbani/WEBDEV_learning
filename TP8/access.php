&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Accès&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;?php
    session_start();
    include 'users.inc';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = $_POST['login'] ?? '';
        $mdp = $_POST['mdp'] ?? '';
        if (loginOk($login, $mdp)) {
            $_SESSION['login'] = $login;
            header("Location: guess.php");
            exit();
        } else {
            header("Location: login.php?error=Login ou mot de passe incorrect.");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
    ?&gt;
&lt;/body&gt;
&lt;/html&gt;
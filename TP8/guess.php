&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Devinette&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Devinette&lt;/h1&gt;
    &lt;?php
    session_start();
    include 'users.inc';

    if (!isset($_SESSION['number'])) {
        $_SESSION['number'] = rand(0, 99);
        $_SESSION['tries'] = 0;
    }

    $message = '';
    $won = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['reset'])) {
            unset($_SESSION['number']);
            unset($_SESSION['tries']);
            header("Location: guess.php");
            exit();
        } elseif (isset($_POST['guess'])) {
            $guess = (int)$_POST['guess'];
            $_SESSION['tries']++;
            if ($guess < $_SESSION['number']) {
                $message = "Trop petit !";
            } elseif ($guess > $_SESSION['number']) {
                $message = "Trop grand !";
            } else {
                $message = "Exact ! Nombre d'essais : " . $_SESSION['tries'];
                $won = true;
                if (isset($_SESSION['login'])) {
                    $current = score($_SESSION['login']);
                    if ($current === null || $_SESSION['tries'] < $current) {
                        update($_SESSION['login'], $_SESSION['tries']);
                    }
                }
            }
        }
    }
    ?&gt;
    &lt;p&gt;&lt;?php echo $message; ?&gt;&lt;/p&gt;
    &lt;?php if (!$won): ?&gt;
        &lt;form action="guess.php" method="post"&gt;
            &lt;label for="guess"&gt;Saisissez un nombre entre 0 et 99 :&lt;/label&gt;
            &lt;input type="number" id="guess" name="guess" min="0" max="99" required&gt;
            &lt;input type="submit" value="Deviner"&gt;
        &lt;/form&gt;
    &lt;?php else: ?&gt;
        &lt;form action="guess.php" method="post"&gt;
            &lt;input type="hidden" name="reset" value="1"&gt;
            &lt;input type="submit" value="Rejouer"&gt;
        &lt;/form&gt;
    &lt;?php endif; ?&gt;
    &lt;p&gt;&lt;a href="logout.php"&gt;Déconnexion&lt;/a&gt;&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
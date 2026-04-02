&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Connexion&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Connexion&lt;/h1&gt;
    &lt;?php if (isset($_GET['error'])): ?&gt;
        &lt;p style="color: red;"&gt;&lt;?php echo htmlspecialchars($_GET['error']); ?&gt;&lt;/p&gt;
    &lt;?php elseif (isset($_GET['message'])): ?&gt;
        &lt;p style="color: green;"&gt;&lt;?php echo htmlspecialchars($_GET['message']); ?&gt;&lt;/p&gt;
    &lt;?php endif; ?&gt;
    &lt;form action="access.php" method="post"&gt;
        &lt;label for="login"&gt;Nom d'utilisateur :&lt;/label&gt;
        &lt;input type="text" id="login" name="login" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="mdp"&gt;Mot de passe :&lt;/label&gt;
        &lt;input type="password" id="mdp" name="mdp" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;input type="submit" value="Se connecter"&gt;
    &lt;/form&gt;
    &lt;p&gt;Pas de compte ? &lt;a href="register.php"&gt;S'inscrire&lt;/a&gt;&lt;/p&gt;
    &lt;h2&gt;Top 10 Meilleurs Scores&lt;/h2&gt;
    &lt;table border="1"&gt;
        &lt;tr&gt;
            &lt;th&gt;Rang&lt;/th&gt;
            &lt;th&gt;Utilisateur&lt;/th&gt;
            &lt;th&gt;Score&lt;/th&gt;
        &lt;/tr&gt;
        &lt;?php
        include 'users.inc';
        $topScores = top();
        $rank = 1;
        foreach ($topScores as $user => $scr) {
            echo "&lt;tr&gt;&lt;td&gt;$rank&lt;/td&gt;&lt;td&gt;" . htmlspecialchars($user) . "&lt;/td&gt;&lt;td&gt;$scr&lt;/td&gt;&lt;/tr&gt;";
            $rank++;
        }
        ?&gt;
    &lt;/table&gt;
&lt;/body&gt;
&lt;/html&gt;
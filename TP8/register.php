&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Inscription&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Inscription&lt;/h1&gt;
    &lt;?php
    include 'users.inc';
    $errors = [];
    $success = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = trim($_POST['login'] ?? '');
        $mdp = $_POST['mdp'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $email = trim($_POST['email'] ?? '');

        if (empty($login)) {
            $errors[] = "Le nom d'utilisateur est requis.";
        } elseif (exist($login)) {
            $errors[] = "Ce nom d'utilisateur existe déjà.";
        }
        if (empty($mdp)) {
            $errors[] = "Le mot de passe est requis.";
        } elseif ($mdp !== $confirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Adresse e-mail invalide.";
        }

        if (empty($errors)) {
            if (addUser($login, $mdp)) {
                header("Location: login.php?message=Inscription réussie. Vous pouvez maintenant vous connecter.");
                exit();
            } else {
                $errors[] = "Erreur lors de l'ajout de l'utilisateur.";
            }
        }
    }
    if (!empty($errors)): ?&gt;
        &lt;ul style="color: red;"&gt;
            &lt;?php foreach ($errors as $error): ?&gt;
                &lt;li&gt;&lt;?php echo htmlspecialchars($error); ?&gt;&lt;/li&gt;
            &lt;?php endforeach; ?&gt;
        &lt;/ul&gt;
    &lt;?php endif; ?&gt;
    &lt;form action="register.php" method="post"&gt;
        &lt;label for="login"&gt;Nom d'utilisateur :&lt;/label&gt;
        &lt;input type="text" id="login" name="login" value="&lt;?php echo htmlspecialchars($login ?? ''); ?&gt;" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="mdp"&gt;Mot de passe :&lt;/label&gt;
        &lt;input type="password" id="mdp" name="mdp" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="confirm"&gt;Confirmer le mot de passe :&lt;/label&gt;
        &lt;input type="password" id="confirm" name="confirm" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="email"&gt;Adresse e-mail :&lt;/label&gt;
        &lt;input type="email" id="email" name="email" value="&lt;?php echo htmlspecialchars($email ?? ''); ?&gt;" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;input type="submit" value="S'inscrire"&gt;
    &lt;/form&gt;
    &lt;p&gt;Déjà un compte ? &lt;a href="login.php"&gt;Se connecter&lt;/a&gt;&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;
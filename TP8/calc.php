&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Calculatrice - Résultat&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Calculatrice&lt;/h1&gt;
    &lt;?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $num1 = $_POST['num1'] ?? '';
        $num2 = $_POST['num2'] ?? '';
        $op = $_POST['op'] ?? '';
        
        $errors = [];
        
        if (!is_numeric($num1)) {
            $errors[] = "Le premier nombre n'est pas valide.";
        }
        if (!is_numeric($num2)) {
            $errors[] = "Le deuxième nombre n'est pas valide.";
        }
        if (!in_array($op, ['+', '-', '*', '/'])) {
            $errors[] = "Opérateur invalide.";
        }
        if ($op == '/' && $num2 == 0) {
            $errors[] = "Division par zéro impossible.";
        }
        
        if (empty($errors)) {
            $result = 0;
            switch ($op) {
                case '+':
                    $result = $num1 + $num2;
                    break;
                case '-':
                    $result = $num1 - $num2;
                    break;
                case '*':
                    $result = $num1 * $num2;
                    break;
                case '/':
                    $result = $num1 / $num2;
                    break;
            }
            echo "&lt;h2&gt;Résultat : $num1 $op $num2 = $result&lt;/h2&gt;";
        } else {
            echo "&lt;h2&gt;Erreurs :&lt;/h2&gt;&lt;ul&gt;";
            foreach ($errors as $error) {
                echo "&lt;li&gt;$error&lt;/li&gt;";
            }
            echo "&lt;/ul&gt;";
        }
    } else {
        echo "&lt;h2&gt;Accès invalide. Veuillez utiliser le formulaire.&lt;/h2&gt;";
    }
    ?&gt;
    &lt;a href="prompt.php"&gt;Retour au formulaire&lt;/a&gt;
&lt;/body&gt;
&lt;/html&gt;
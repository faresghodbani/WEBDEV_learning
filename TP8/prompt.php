&lt;!DOCTYPE html&gt;
&lt;html lang="fr"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Calculatrice - Saisie&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Calculatrice&lt;/h1&gt;
    &lt;form action="calc.php" method="post"&gt;
        &lt;label for="num1"&gt;Premier nombre :&lt;/label&gt;
        &lt;input type="text" id="num1" name="num1" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="op"&gt;Opérateur :&lt;/label&gt;
        &lt;select id="op" name="op" required&gt;
            &lt;option value="+"&gt;+&lt;/option&gt;
            &lt;option value="-"&gt;-&lt;/option&gt;
            &lt;option value="*"&gt;*&lt;/option&gt;
            &lt;option value="/"&gt;/&lt;/option&gt;
        &lt;/select&gt;&lt;br&gt;&lt;br&gt;

        &lt;label for="num2"&gt;Deuxième nombre :&lt;/label&gt;
        &lt;input type="text" id="num2" name="num2" required&gt;&lt;br&gt;&lt;br&gt;

        &lt;input type="submit" value="Calculer"&gt;
    &lt;/form&gt;
&lt;/body&gt;
&lt;/html&gt;
&lt;?php
session_start();

$mots = ["ordinateur", "programmation", "javascript", "php", "html", "css", "mysql", "apache", "linux", "windows"];

if (!isset($_SESSION['mot'])) {
    $_SESSION['mot'] = $mots[array_rand($mots)];
    $_SESSION['motAffiche'] = str_repeat('_', strlen($_SESSION['mot']));
    $_SESSION['coups'] = 6;
    $_SESSION['fini'] = false;
}

$lettre = strtolower($_GET['lettre'] ?? '');

if (empty($lettre)) {
    echo json_encode(["message" => "", "fini" => $_SESSION['fini'], "motAffiche" => $_SESSION['motAffiche'], "coups" => $_SESSION['coups']]);
    exit();
}

if (!preg_match('/^[a-z]$/', $lettre)) {
    echo json_encode(["message" => "Lettre invalide", "fini" => $_SESSION['fini'], "motAffiche" => $_SESSION['motAffiche'], "coups" => $_SESSION['coups']]);
    exit();
}

if ($_SESSION['fini']) {
    echo json_encode(["message" => "Partie terminée", "fini" => true, "motAffiche" => $_SESSION['motAffiche'], "coups" => $_SESSION['coups']]);
    exit();
}

$message = "";
$present = false;
for ($i = 0; $i < strlen($_SESSION['mot']); $i++) {
    if ($_SESSION['mot'][$i] == $lettre) {
        $_SESSION['motAffiche'][$i] = $lettre;
        $present = true;
    }
}

if (!$present) {
    $_SESSION['coups']--;
    $message = "Lettre absente";
} else {
    $message = "Lettre présente";
}

if ($_SESSION['coups'] == 0) {
    $_SESSION['fini'] = true;
    $message = "Vous avez perdu. Le mot était : " . $_SESSION['mot'];
} elseif ($_SESSION['motAffiche'] == $_SESSION['mot']) {
    $_SESSION['fini'] = true;
    $message = "Vous avez gagné !";
}

echo json_encode(["message" => $message, "fini" => $_SESSION['fini'], "motAffiche" => $_SESSION['motAffiche'], "coups" => $_SESSION['coups']]);
?&gt;
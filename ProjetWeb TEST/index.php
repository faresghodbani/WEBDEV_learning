<?php
session_start();

// Si déjà connecté → aller à home
if (!empty($_SESSION["username"])) {
    header("Location: home.php");
    exit();
}

// Quand on envoie le formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $avatar = $_POST["avatar"];

    $file = "data.json";

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    } else {
        $data = [];
    }

    if (!$data) {
        $data = [];
    }

    if (!isset($data["online_users"])) {
        $data["online_users"] = [];
    }

    $already_online = false;

    // PSEUDO UNIQUE
    foreach ($data["online_users"] as $user) {
        if ($user["username"] === $username) {
            $already_online = true;
            break;
        }
    }

    // MESSAGE PROPRE AU LIEU DE PAGE BLANCHE
    if ($already_online) {
        $_SESSION["error"] = "Ce pseudo est déjà utilisé !";
        header("Location: index.php");
        exit();
    }

    // Ajouter session SEULEMENT si le pseudo est accepté
    $_SESSION["username"] = $username;
    $_SESSION["avatar"] = $avatar;

    // Ajouter utilisateur
    $data["online_users"][] = [
        "username" => $username,
        "avatar" => $avatar
    ];

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>WhoRelate</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<h1>WhoRelate</h1>

<?php if (!empty($_SESSION["error"])): ?>
    <p class="error"><?= htmlspecialchars($_SESSION["error"]) ?></p>
    <?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<form method="POST">
    <input type="text" name="username" placeholder="Ton pseudo" required>
    <br>

    <label>Choisis ton avatar</label>

    <div class="avatar-grid">

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🐺" checked>
            <div class="avatar-circle">🐺</div>
        </label>

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🦊">
            <div class="avatar-circle">🦊</div>
        </label>

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🐼">
            <div class="avatar-circle">🐼</div>
        </label>

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🦁">
            <div class="avatar-circle">🦁</div>
        </label>

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🐸">
            <div class="avatar-circle">🐸</div>
        </label>

        <label class="avatar-option">
            <input type="radio" name="avatar" value="🐙">
            <div class="avatar-circle">🐙</div>
        </label>

    </div>

    <br>

    <button type="submit">Entrer</button>
</form>

</body>
</html>
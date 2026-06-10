<?php
session_start();

$file = "data.json";

$room_id = $_SESSION["room_id"];

$data = json_decode(file_get_contents($file), true);

// vérifier room
if (isset($data[$room_id])) {

    // passer en mode sélection
    $data[$room_id]["state"] = "mode_selection";

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

header("Location: game.php");
exit();
?>
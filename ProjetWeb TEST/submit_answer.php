<?php
session_start();

if (empty($_SESSION["username"]) || empty($_SESSION["room_id"])) {
    header("Location: index.php");
    exit();
}

$file = "data.json";

if (!file_exists($file)) {
    header("Location: home.php");
    exit();
}

$data = json_decode(file_get_contents($file), true);

if (!$data) {
    header("Location: home.php");
    exit();
}

$room_id = $_SESSION["room_id"];
$username = $_SESSION["username"];

if (!isset($data[$room_id])) {
    header("Location: home.php");
    exit();
}

if (($data[$room_id]["state"] ?? "") !== "question") {
    header("Location: game.php");
    exit();
}

$answer_text = trim($_POST["answer_text"] ?? "");
$answer_type = $_POST["answer_type"] ?? "";

if ($answer_text === "" || !in_array($answer_type, ["VRAI", "BLUFF"])) {
    header("Location: game.php");
    exit();
}

if (!isset($data[$room_id]["answers"]) || !is_array($data[$room_id]["answers"])) {
    $data[$room_id]["answers"] = [];
}

$data[$room_id]["answers"][$username] = [
    "text" => $answer_text,
    "type" => $answer_type,
    "submitted_at" => time()
];

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: game.php");
exit();
?>
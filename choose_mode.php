<?php
session_start();

$file = "data.json";

if (empty($_SESSION["room_id"])) {
    header("Location: home.php");
    exit();
}

$room_id = $_SESSION["room_id"];
$mode = $_POST["mode"] ?? "";

if (!file_exists($file)) {
    header("Location: home.php");
    exit();
}

$data = json_decode(file_get_contents($file), true);

if (!$data || !isset($data[$room_id])) {
    header("Location: home.php");
    exit();
}

$players = $data[$room_id]["players"] ?? [];
$player_count = count($players);

$allowed_modes = ["sport", "internet", "histoiregeo", "celebrite", "musique", "amis"];
if (!in_array($mode, $allowed_modes, true)) {
    header("Location: game.php");
    exit();
}

$story_modes = ["sport", "internet", "histoiregeo", "celebrite", "musique", "amis"];

$sport_questions = [
    "Raconte l’histoire d’un sportif à son prime de carrière.",
    "Raconte le plus beau moment de l’histoire du sport.",
    "Raconte un exploit sportif qui a marqué les esprits.",
    "Raconte une finale de sport légendaire.",
    "Raconte le moment où un athlète est devenu une légende.",
    "Raconte un comeback incroyable dans le sport.",
    "Raconte une performance sportive que personne n’oubliera.",
    "Raconte un moment de sport qui a fait vibrer tout un pays.",
    "Raconte un duel mythique dans le sport.",
    "Raconte un exploit individuel exceptionnel dans l’histoire du sport.",
    "Raconte le jour où un outsider a choqué tout le monde dans le sport.",
    "Raconte une victoire improbable face à un favori.",
    "Raconte un moment de pression extrême dans une grande compétition.",
    "Raconte une scène de sport que tout le monde devrait connaître.",
    "Raconte une légende du sport au sommet de sa domination.",
    "Raconte un record qui semblait impossible à battre.",
    "Raconte une performance héroïque malgré la fatigue ou la douleur.",
    "Raconte un grand moment des Jeux Olympiques.",
    "Raconte une histoire sportive qui a inspiré des millions de personnes.",
    "Raconte un geste technique devenu mythique.",
    "Raconte une action sportive entrée dans la légende.",
    "Raconte un exploit collectif inoubliable dans le sport.",
    "Raconte l’ambiance d’un stade pendant un moment historique.",
    "Raconte un moment de rédemption dans une carrière sportive.",
    "Raconte une revanche sportive devenue célèbre.",
    "Raconte une histoire de sport où le mental a tout changé.",
    "Raconte un match ou une course qui a basculé au dernier moment.",
    "Raconte un grand champion dans le moment le plus fort de sa carrière.",
    "Raconte une surprise incroyable dans une grande compétition.",
    "Raconte un scénario de sport tellement fou qu’il paraît inventé."
];

$internet_questions = [
    "Raconte un moment historique d’Internet.",
    "Raconte un mème, buzz ou trend devenu légendaire sur Internet.",
    "Raconte une histoire d’Internet que tout le monde croit connaître.",
    "Raconte un moment où Internet a explosé pour une vidéo ou une photo.",
    "Raconte un créateur, streamer ou youtubeur devenu une icône.",
    "Raconte une polémique internet devenue célèbre.",
    "Raconte un site, une appli ou une plateforme qui a changé Internet.",
    "Raconte un moment où un inconnu est devenu viral du jour au lendemain.",
    "Raconte un événement du web qui a marqué toute une génération.",
    "Raconte une histoire folle née sur les réseaux sociaux.",
    "Raconte un mème raconté comme une histoire épique.",
    "Raconte un moment où Internet a réagi de façon massive à quelque chose.",
    "Raconte une légende urbaine du web ou d’Internet.",
    "Raconte une histoire liée aux débuts de YouTube, Twitch, TikTok ou Instagram.",
    "Raconte un phénomène Internet que presque tout le monde a vu.",
    "Raconte un moment où un post a changé la vie de quelqu’un.",
    "Raconte une histoire célèbre de forum, réseau social ou communauté en ligne.",
    "Raconte un moment drôle ou absurde qui n’aurait pu exister que sur Internet.",
    "Raconte un grand moment des jeux en ligne ou du streaming.",
    "Raconte une histoire internet qui paraît fausse mais qui est vraie."
];

$histoiregeo_questions = [
    "Raconte un événement historique devenu légendaire.",
    "Raconte une bataille ou guerre qui a changé le monde.",
    "Raconte un personnage historique raconté comme un héros ou un anti-héros.",
    "Raconte un moment clé de l’histoire de France ou du monde.",
    "Raconte une découverte géographique qui a marqué l’histoire.",
    "Raconte la chute ou la montée d’un empire.",
    "Raconte un grand explorateur ou voyageur célèbre.",
    "Raconte un événement historique que tout le monde devrait connaître.",
    "Raconte une révolution, un soulèvement ou un tournant politique célèbre.",
    "Raconte une histoire géographique liée à un pays, une frontière ou un territoire.",
    "Raconte une catastrophe historique ou naturelle qui a changé une région.",
    "Raconte un mystère historique célèbre.",
    "Raconte une époque historique comme si tu y étais.",
    "Raconte un grand moment d’histoire raconté comme un film.",
    "Raconte une ville, un monument ou un lieu géographique mythique.",
    "Raconte un événement historique plein de suspense.",
    "Raconte l’histoire d’une civilisation fascinante.",
    "Raconte une expédition ou une traversée devenue célèbre.",
    "Raconte un moment où la géographie a changé le destin d’un peuple.",
    "Raconte une histoire d’histoire-géo qui paraît incroyable mais vraie."
];

$celebrite_questions = [
    "Raconte l’histoire d’une célébrité devenue une icône.",
    "Raconte un moment légendaire dans la vie d’une célébrité.",
    "Raconte une célébrité au sommet de sa gloire.",
    "Raconte un scandale célèbre impliquant une star.",
    "Raconte un moment où une célébrité a surpris tout le monde.",
    "Raconte une ascension incroyable vers la célébrité.",
    "Raconte une rivalité célèbre entre deux stars.",
    "Raconte une anecdote de célébrité que beaucoup connaissent.",
    "Raconte le jour où une célébrité est devenue mondialement connue.",
    "Raconte une histoire de célébrité qui ressemble à un film.",
    "Raconte un moment de tapis rouge, interview ou apparition devenu culte.",
    "Raconte un acteur, une actrice ou une star raconté(e) comme une légende.",
    "Raconte une célébrité connue pour son talent autant que pour sa personnalité.",
    "Raconte un moment où une célébrité a fait taire tous ses critiques.",
    "Raconte une histoire vraie sur une star qui paraît inventée.",
    "Raconte une célébrité et un moment qui a choqué Internet ou les médias.",
    "Raconte une histoire de gloire, chute ou retour spectaculaire.",
    "Raconte un moment culte de la pop culture lié à une célébrité.",
    "Raconte une histoire de célébrité pleine de buzz ou de suspense.",
    "Raconte une star devenue mythique dans son domaine."
];

$musique_questions = [
    "Raconte une chanson ou un album devenu légendaire.",
    "Raconte un artiste ou groupe au sommet de sa carrière.",
    "Raconte un concert ou festival devenu mythique.",
    "Raconte un moment historique dans la musique.",
    "Raconte une performance musicale que personne n’oubliera.",
    "Raconte un album qui a marqué toute une génération.",
    "Raconte l’histoire d’un chanteur ou musicien devenu une légende.",
    "Raconte une rivalité ou opposition célèbre dans la musique.",
    "Raconte un moment où un artiste a explosé du jour au lendemain.",
    "Raconte une chanson racontée comme une vraie histoire.",
    "Raconte un live ou une scène musicale devenue culte.",
    "Raconte un moment où la musique a uni énormément de gens.",
    "Raconte une histoire musicale qui paraît fausse mais vraie.",
    "Raconte une récompense, victoire ou exploit dans la musique.",
    "Raconte un artiste qui a changé son époque.",
    "Raconte un morceau, clip ou refrain devenu iconique.",
    "Raconte une époque musicale précise comme si c’était une légende.",
    "Raconte un grand moment de la musique française ou internationale.",
    "Raconte une chanson connue de tous sans dire son titre au début.",
    "Raconte un artiste qui a fait taire tout le monde avec un chef-d’œuvre."
];

$amis_questions = [
    "Raconte une histoire gênante lors d’un rencard.",
    "Raconte une histoire gênante en public.",
    "Raconte un moment de honte absolue que tu n’oublieras jamais.",
    "Raconte un énorme malentendu que tu as vécu.",
    "Raconte un moment où tu as voulu disparaître sur place.",
    "Raconte une anecdote drôle arrivée en soirée.",
    "Raconte un moment très gênant devant des inconnus.",
    "Raconte une histoire embarrassante à l’école, à la fac ou au travail.",
    "Raconte une fois où tu as dit quelque chose au très mauvais moment.",
    "Raconte une histoire gênante liée à un crush ou à une relation.",
    "Raconte un moment où tout le monde a ri sauf toi.",
    "Raconte une situation absurde que tu as réellement vécue.",
    "Raconte un souvenir personnel qui paraît inventé.",
    "Raconte un moment où tu t’es complètement affiché(e).",
    "Raconte une anecdote drôle arrivée en voyage ou en vacances.",
    "Raconte une histoire de famille un peu honteuse mais drôle.",
    "Raconte une bourde que tu as faite devant plusieurs personnes.",
    "Raconte une histoire où tu as paniqué pour rien.",
    "Raconte une situation où tu t’es retrouvé(e) coincé(e) dans un mensonge.",
    "Raconte un moment où tu as regretté immédiatement quelque chose que tu venais de faire.",
    "Raconte une anecdote personnelle tellement folle qu’elle semble fausse.",
    "Raconte un moment social vraiment awkward.",
    "Raconte une fois où tu t’es trompé(e) de personne, de message ou de contexte.",
    "Raconte un souvenir personnel drôle mais un peu humiliant.",
    "Raconte une situation où ton stress t’a trahi."
];

$questions_by_mode = [
    "sport" => $sport_questions,
    "internet" => $internet_questions,
    "histoiregeo" => $histoiregeo_questions,
    "celebrite" => $celebrite_questions,
    "musique" => $musique_questions,
    "amis" => $amis_questions
];

foreach ($questions_by_mode as $key => $list) {
    shuffle($list);
    $questions_by_mode[$key] = $list;
}

$data[$room_id]["mode"] = $mode;

if (in_array($mode, $story_modes, true)) {
    $questions = $questions_by_mode[$mode];
    $questions_key = $mode . "_questions";
    $total_questions = min($player_count, count($questions));

    $scores = [];
    foreach ($players as $p) {
        $scores[$p["username"]] = 0;
    }

    $data[$room_id]["state"] = "question";
    $data[$room_id][$questions_key] = array_slice($questions, 0, $total_questions);
    $data[$room_id]["current_question_index"] = 0;
    $data[$room_id]["total_questions"] = $total_questions;
    $data[$room_id]["question_text"] = $data[$room_id][$questions_key][0];
    $data[$room_id]["question_end_time"] = time() + 60;
    $data[$room_id]["answers"] = [];
    $data[$room_id]["round_answers"] = [];
    $data[$room_id]["displayed_responses"] = [];
    $data[$room_id]["current_display_index"] = 0;

    $data[$room_id]["current_author_votes"] = [];
    $data[$room_id]["author_vote_end_time"] = 0;

    $data[$room_id]["current_votes"] = [];
    $data[$room_id]["vote_end_time"] = 0;

    $data[$room_id]["result_end_time"] = 0;
    $data[$room_id]["last_result"] = null;
    $data[$room_id]["vote_history"] = [];
    $data[$room_id]["scores"] = $scores;
} else {
    $data[$room_id]["state"] = "mode_selection";
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: game.php");
exit();
?>
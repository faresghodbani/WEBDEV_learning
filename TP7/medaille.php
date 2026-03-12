<?php

$pays_medailles = array(
    "Chine" => array("or" => 38, "argent" => 32, "bronze" => 18),
    "Japon" => array("or" => 27, "argent" => 14, "bronze" => 17),
    "Grande-Bretagne" => array("or" => 22, "argent" => 21, "bronze" => 22),
    "Australie" => array("or" => 17, "argent" => 7, "bronze" => 23),
    "ROC" => array("or" => 16, "argent" => 22, "bronze" => 20),
    "Italie" => array("or" => 10, "argent" => 10, "bronze" => 20),
    "France" => array("or" => 10, "argent" => 12, "bronze" => 11),
    "Allemagne" => array("or" => 10, "argent" => 11, "bronze" => 16),
    "Canada" => array("or" => 7, "argent" => 6, "bronze" => 11)
);

$pays_max_bronze = '';
$max_bronze = 0;

foreach ($pays_medailles as $pays => $medailles) {
    if ($medailles['bronze'] > $max_bronze) {
        $max_bronze = $medailles['bronze'];
        $pays_max_bronze = $pays;
    }
}

echo "<p>Le pays avec le plus de médailles de bronze est <strong>$pays_max_bronze</strong> avec $max_bronze médailles de bronze.</p>";

echo "<ul>";
foreach ($pays_medailles as $pays => $medailles) {
    $total = $medailles['or'] + $medailles['argent'] + $medailles['bronze'];
    echo "<li>$pays : $total</li>";
}
echo "</ul>";

?>
<?php

$jourNaissance = 24;
$moisNaissance = 1;

$jourActuel = intval(date("d"));
$moisActuel = intval(date("m"));
$anneeActuelle = intval(date("Y"));

if ($moisActuel > $moisNaissance || ($moisActuel == $moisNaissance && $jourActuel > $jourNaissance)) {
$anneeAnniversaire = $anneeActuelle + 1;
} else {
$anneeAnniversaire = $anneeActuelle;
}

$joursActuels = $anneeActuelle * 360 + $moisActuel * 30 + $jourActuel;
$joursAnniversaire = $anneeAnniversaire * 360 + $moisNaissance * 30 + $jourNaissance;

$difference = $joursAnniversaire - $joursActuels;

$mois = intval($difference / 30);
$reste = $difference % 30;
$semaines = intval($reste / 7);
$jours = $reste % 7;

echo "Date actuelle : " . date("d/m/Y") . "\n";
echo "Temps jusqu'au prochain anniversaire : ";

echo $mois . " mois";

echo ", " . $semaines . " semaine";
if ($semaines > 1) {
echo "s";
}

echo " et " . $jours . " jour";
if ($jours > 1) {
echo "s";
}

?>
<?php
#
#
// Recuperation des donnees historiques à J jours du peripherique PERIPH_ID
// Usage typique : creer un capteur HTTP en passant l'URL .../script?historique.php&periph_id=xxxx&jours=jjj
// Voir détail de la documentation dans le fichier READ.md joint à la livraison
// Produit par G. SIMON v1 mai 2016
// Version 1.0
// 1.0 : premiere version

// Fonction de trace
function sdk_trace ($texte) {
	global $debug;
	if ($debug == 1) echo "<trace>".str_replace("\n","",$texte)."</trace>\n";
}

// Debut du script

sdk_header('text/xml');
echo "<root>\n";

$periph_id = getArg("periph_id"); // numero du peripherique dont on cherche l'historique
$jours = getArg("jours"); // Ex : 66, a récuperer sur le site maree.info apres choix du port dans la liste
$api_user = getArg("api_user");
$api_secret = getArg("api_secret");
$delta_max = getArg("delta_max",false,30)/2;	// Parametre facultatif indiquant l'intervalles max à prendre en compte dans l'historique. On divise par 2 pour chercher sur [date-delta,date+delta]
$debug = getArg("debug",false,0);	// Passer le parametre facultatif &debug=1 pour afficher des traces

$avant=strtotime('-'.$jours.' day');	// Date pour laquelle on cherche l'historique

// On fera 4 passes, reparties entre 1 et $delta_max minutes
$tab_delta = array(1, round($delta_max/3), round($delta_max/2), $delta_max);
sdk_trace( "Deltas : $tab_delta[0] $tab_delta[1] $tab_delta[2] $tab_delta[3]");
$nb_noeuds=0 ; // Nombre de noeuds d'historique trouve dans le retour de l'API

for ( $i=0; $i<4; $i++) {	// On  parcourt le tableau des deltas qui augmentent
	$avant_inf=date("Y-m-d H:i:s",strtotime('-'.$tab_delta[$i].' minutes',$avant));	// Borne inferieure de recherche
	$avant_sup=date("Y-m-d H:i:s",strtotime('+'.$tab_delta[$i].' minutes',$avant));	// Borne superieure de recherche
	sdk_trace("De ".$avant_inf." à ".$avant_sup);

	// Doc de l'API : https://api.eedomus.com/get?action=periph.history&periph_id=XXXX&start_date=YYYY-MM-DD HH:MM:SS&end_date=YYYY-MM-DD HH:MM:SS&api_user=XXXX&api_secret=XXXX
	$url = "https://api.eedomus.com/get?action=periph.history&periph_id=$periph_id&start_date=".str_replace(" ","%20",$avant_inf)."&end_date=".str_replace(" ","%20",$avant_sup)."&api_user=$api_user&api_secret=$api_secret&format=xml";
	sdk_trace("URL : ".$url);

	$result = httpQuery($url, 'GET');	// Appel de l'API
	$result = substr($result,39);		/* On enlève l'entete de 39 caracteres <?xml version="1.0" encoding="UTF-8"?> qui peut perturber l'affichage des traces */

	$nb_noeuds = xpath($result,"count(/root/body/history/history/history)");
	sdk_trace("$tab_delta[$i] mn -> $nb_noeuds noeuds (".str_replace("\n","",$result)."\n");
	if ($nb_noeuds != 0) break;

	// Il est necessaire d'attendre quelques secondes avant de relancer une autre requete API
	// sinon "error_code": "26", "error_msg": "Please wait that previous API history query has finished.", "debug": "Last query was 0.4 sec. ago"
	// ou "error_code": "27", "error_msg": "API history spam detected, please slow down your queries.", "debug": "Last query for periph_id=nnnnnn was 3 sec. ago"
	usleep(6000000);
}

$valeur = xpath($result,"/root/body/history/history[1]/history[1]");
$date =  xpath($result,"/root/body/history/history[1]/history[2]");


sdk_trace(" <from>".$avant_inf."</from>\n");
echo " <avant>".date("c",$avant)."</avant>\n";
sdk_trace(" <to>".$avant_sup."</to>\n");
echo " <valeur>".$valeur."</valeur>\n";
echo " <date>".$date."</date>\n";
sdk_trace("<retourAPI>".$result."</retourAPI>");
echo "</root>";

?>
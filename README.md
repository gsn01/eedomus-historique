# eedomus-historique
## Description
Ce script php permet de récupérer des informations sur l'historique d'un périphérique pour usage avec la box domotique *eedomus*.

## Installation
1. Télécharger le projet sur GitHub : [github.com/gsn01/eedomus-historique](https://github.com/gsn01/eedomus-historique/archive/master.zip)
1. Uploader le fichier *historique.php* sur la box ([doc eedomus scripts](http://doc.eedomus.com/view/Scripts#Script_HTTP_sur_la_box_eedomus))

## Principe de fonctionnement (technique)

L'API *eedomus* de consultation de l'historique d'un périphérique est appelée en passant en paramètre l'intervalle de temps dans lequel effectuer la recherche.
Il est possible qu'aucune valeur n'ait existé dans cet intervalle. Le script utilise donc des intervalles de plus en plus grand pour tenter de trouver la valeur la plus proche de la date recherchée.
L'intervalle de recherche par défaut est de 30 minutes.

## Appel et Test
Ce script peut ensuite être appelé et testé au travers du lien suivant dans le navigateur

	http://[ip_de_votre_box]/script/?exec=historique.php&periph_id=iiiiii&jours=jj&api_user=uuuuu&api_secret=sssssssssssss&delta_max=dd

où il faut remplacer
- *[ip_de_votre_box]* par l'IP de votre Box *eedomus*
- *iiiiii* par le numéro du périphérique dont on veut lire l'historique (à récupérer dans les paramètres Expert du périphérique)
- *jj* par le nombre de jours à déduire à la date actuelle pour chercher l'historique (exemple : 1 pour hier, 365 pour un an)
- *uuuuu* et *sssssssssss* par les codes api à récupérer dans *configuration / Mon Compte / Paramètres / Consulter mes identifiants*

Exemple :	http://192.168.1.2/script/?exec=historique.php&periph_id=123456&jours=365&api_user=1234&api_secret=hf76hdsq6tr

## Résultat
Le résultat est au format XML.
Si l'un des paramètres est erroné, une erreur est renvoyée.

Exemple de résultat
```xml
<root>
 <avant>2016-05-05T10:01:54+02:00</avant>
 <valeur>9.9</valeur>
 <date>2016-05-05 09:51:16</date>
</root>
```

## Correspondance XPATH

Les différentes informations possibles retournées par les Xpath suivants :

- ```avant```: date autour de laquelle rechercher l'historique
- ```valeur```: valeur historique
- ```date```: date de la valeur historique


## Un exemple d'exploitation avec l'eedomus

Pour connaître et suivre la température extérieure d'il y a un an, trouver l'ID de votre périphérique de température extérieure, puis créer un nouveau périphérique de type **Capteur HTTP**, nommé "Temp Ext A-1".

Renseigner les paramètres suivants pour récupérer la valeur :

- Type de données : ```Nombre décimal```
- URL de la requête : ```http://localhost/script/?exec=historique.php&jours=365&...```
- Chemin XPATH : ```//valeur```
- Fréquence de la requête : ```60```

Le résultat :
![historique](historiqueResultat.png "Historique")


Si vous connaissez d'autres cas d'usage, n'hésitez pas à me les signaler pour enrichir ces explications
---
### Enjoy


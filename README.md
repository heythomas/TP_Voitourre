## Romain Savaete - Thomas Bellon
# TP_Voitourre
### La voitourre est lancée !
 
 Utilisation :
 - Aller sur le lien du serveur applicatif
   - https://app-server-php.herokuapp.com/
 - Sélectionner une voiture
 - Cliquer sur le lieu de destination
 - Le chemin de ce lieu vers le campus se calcule et les informations s'affichent à l'écran :
   - Temps de trajet
   - Etapes a parcourir
   - Lieux de recharge prévus
   - Km à parcourir

 Liens vers les solutions :
 - Serveur applicatif PHP
   - https://app-server-php.herokuapp.com/
 - Serveur de sélection de voiture SOAP (Python - rpclib)
   - Page par défaut
     - https://soap-car-selector.herokuapp.com/
   - Page appelée pour obtenir les voitures
     - https://soap-car-selector.herokuapp.com/?wsdl
 - Serveur de calcul de temps de trajet REST (Python - Flask)
   - Page par défaut
     - https://rest-calcul-temps-trajet.herokuapp.com/calcultempstrajet
   - Page avec paramètres d'exemple donnés
     - https://rest-calcul-temps-trajet.herokuapp.com/calcultempstrajet?voiture=Tesla%20Model%20S&autonomie=652&tps_recharge=60&long_trajet=20000

 Remarques :
 - La totalité des services créés sont hébergés sur Heroku
 - Nous utilisons l'api suivante pour obtenir des coordonnées de borne de recharge
   - https://opendata.reseaux-energies.fr/explore/dataset/bornes-irve/api/?disjunctive.region
   - Le paramètre sort est mis a ```-dist``` afin d'obtenir la borne la plus proche
   - Cette API ne possède que peu de données dans la moitié nord de la France, ainsi des escales étranges peuvent apparaître...
 - Pour la carte, le plugin JS Leaflet est utilisé
   - Pour tracer le chemin et obtenir les étapes de navigation le plugin ```routing machine``` (extension de Leaflet) à également été ajouté.
   - JQuery est également installé

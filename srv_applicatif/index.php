<!DOCTYPE html>
<html>
<head>
  <title>A Meaningful Page Title</title>
  <!-- JQUERY -->
  <script src="./jquery.js"></script>
  <!-- LEAFLET -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
   integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
   crossorigin=""/>
   <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
   integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
   crossorigin=""></script>
   
   <!-- ROUTING LEAFLET -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.2.0/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet@1.2.0/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <!-- GEOMETRY UTIL -->
    <script src="./leaflet.geometryutil.js"></script>
</head>
    <body>

    <?php
    try{
        // CONNECT TO SOAP PROVIDER 
        $clientSOAP = new SoapClient("http://127.0.0.1:8000/?wsdl");
        $resultats = $clientSOAP->get_cars();
        
        // DISPLAY RESULTS IN A LIST
        foreach ($resultats as $res) {
            echo("<select name='cars' id='car_selector'>");
            echo("<option value=''>Veuillez sélectionner une voiture...</option><br/>");
            foreach($res->string as $el){
                echo("<option value=''>".$el."</option><br/>");
            }
            echo("</select>");
        }
    }catch(SoapFault $e){
        // IN CASE OF PROBLEM WITH SOAP
        echo("No voitoure avélableu <br/>");
    }

    ?>

    <div id="result">

    </div>
    <button id="test">Calculer</button>
    <div id="map"></div>
    <style>
        #map { height: 30vw; }
    </style>
    <script>
        // SET AJAX TO FALSE
        jQuery.ajaxSetup({async:false}); 
        // ROUTE VARIABLES
        var routeControl;
        window.time;
        window.distance;
        window.last_refuel;

        // GET TRAVEL TIME
        var regex = /[^0-9]*([0-9]*)[^0-9]*([0-9]*)/;

        document.getElementById("test").onclick = function(){
            traceRoute();
        };

        function get_travel_time(dist){
            el = document.getElementById("car_selector").options[document.getElementById("car_selector").selectedIndex]
            textBrut = el.text
            reg = textBrut.match(regex)
            voiture = "null"
            autonomie = reg[1];
            tps_recharge = reg[2];
            vitesse = 100;

            $.get( "http://127.0.0.1:5000//calcultempstrajet", { voiture: voiture, autonomie: autonomie, tps_recharge: tps_recharge, long_trajet: dist } )
            .done(function( data ) {
                // alert( "Le temps de parcours sera de : " + (parseInt(parseInt(data["temps"])/60)) + "h" + (parseInt(parseInt(data["temps"])%60)<10?'0':'') + parseInt(parseInt(data["temps"])%60)  + "min");
                window.time = data["temps"]
                window.distance = dist
            });
        }

        // GET NEAREST REFUEL POINT
        function find_refuel(coo){
            // Block async problems
            window.last_refuel = false;
            
            tosearch = coo[0]+", "+coo[1]+", 50000"
            console.log(tosearch)
            $.get( "https://opendata.reseaux-energies.fr/api/records/1.0/search/", { dataset:"bornes-irve", q:"", rows:"1", sort:"-dist", facet:"region", "geofilter.distance":tosearch } )
            .done(function( data ) {
                // alert(data["records"][0]["geometry"]["coordinates"])
                window.last_refuel =  data["records"][0]["geometry"]["coordinates"];
                window.last_refuel_update = window.last_refuel;
            });

            return true;
        }

        // MAP DISPLAY - Inverser latitude et longitude ici (dans setview)
        var map = L.map('map').setView([45.64036370303461, 5.871814725687728], 13);
        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            maxZoom: 18,
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1,
            accessToken: 'pk.eyJ1Ijoic2F2cm8iLCJhIjoiY2t6NnRyNGJvMGZsZTJ1bWc5M2p4aXJkYiJ9.ZwhOn-wg1gScvkDLt9jHPA'
        }).addTo(map);
        
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright%22%3EOpenStreetMap</a> contributors'
        }).addTo(map);

        // ADD LOCATION + (EDITABLE) DESTINATION
        marker = L.marker([47.654288, -2.752075]).addTo(map);
        marker2 = L.marker([45.64036370303461, 5.871814725687728]).addTo(map);

        map.on('click', function(e) {
            marker.setLatLng(e.latlng).update();
            // console.log("Lat, Lon : " + marker.getLatLng().lat + ", " + marker.getLatLng().lng)
        });

        function traceRoute(){

            //STEP ONE, GET NAIVE ROUTE KM
            routeControl = L.Routing.control({
                waypoints: [
                    L.latLng(marker.getLatLng().lat, marker.getLatLng().lng),
                    L.latLng(marker2.getLatLng().lat, marker2.getLatLng().lng)
                ]
            }).addTo(map);
            
            routeControl.on('routesfound', function (e) {
                distance = e.routes[0].summary.totalDistance / 1000;
                // console.log(distance);
                // STEP TWO, GET NAIVE TIME FOR THIS ROUTE
                get_travel_time(distance);
            });

            routeControl.on('routeselected', function (e) {
                addRefuel();
            });
        }

        // Function nb_km avant recharge
        function get_nbkmRecharge(autonomie){
            recharge = autonomie * 0.9;
            if(recharge < 0){
                return 10000000000000000000000;
            }
            return recharge
        }

        function get_CoordPointInter(){
            //Je récupére la voiture courante
            el = document.getElementById("car_selector").options[document.getElementById("car_selector").selectedIndex]
            textBrut = el.text
            reg = textBrut.match(regex)
            voiture = "null"
            autonomie = parseInt(reg[1]);
            distance = window.distance;

            nb_kmRecharge = get_nbkmRecharge(autonomie);
            waypointCoord = [];

            while(nb_kmRecharge < distance) {
                //Récupérer les coordonnées des points d'arret
                coordPointArret = get_coordinate_at(nb_kmRecharge);
                console.log(nb_kmRecharge)

                //Scanner pour trouver la borne la plus proche
                find_refuel(coordPointArret);
                coordBorneClose = [window.last_refuel[1], window.last_refuel[0]];
                
                //Ajouter les coordonnées de la borne dans waypointCoord
                waypointCoord.push(coordBorneClose);
                nb_kmRecharge += nb_kmRecharge;

            }
            // console.log(waypointCoord)
            return waypointCoord;
        }

        function get_coordinate_at(km){
            var km_parcouru = 0;
            var list = routeControl._line._route.instructions;
            for(i = 0; i < list.length; i++){
                if(list[i]["distance"]/1000 + km_parcouru > km){
                    // alert([routeControl._line._route.coordinates[list[i-1]["index"]]["lat"], routeControl._line._route.coordinates[list[i-1]["index"]]["lng"]])
                    return [routeControl._line._route.coordinates[list[i-1]["index"]]["lat"], routeControl._line._route.coordinates[list[i-1]["index"]]["lng"]];
                }
                else{
                    km_parcouru += list[i]["distance"]/1000;
                }
            }
            alert("ERREUR");
            return null;
        }

        function addRefuel(){
            tab = get_CoordPointInter();
            // alert(tab)
            routeControl.remove();
            routeControl = null;

            // console.log(tab)

            toTrace = [];
            toTrace.push(L.latLng(marker.getLatLng().lat, marker.getLatLng().lng));

            for(i = 0; i < tab.length; i++){
                toTrace.push(L.latLng(tab[i][0], tab[i][1]));
            }

            toTrace.push(L.latLng(marker2.getLatLng().lat, marker2.getLatLng().lng));

            //STEP ONE, GET NAIVE ROUTE KM
            routeControl = L.Routing.control({
                waypoints: toTrace
            }).addTo(map)

            // RECALCULATE TIME AND DISTANCE TO DISPLAY IT
            routeControl.on('routesfound', function (e) {
                distance = e.routes[0].summary.totalDistance / 1000;
                get_travel_time(distance);
            });

            routeControl.on('routeselected', function (e) {
                $(".leaflet-routing-alt>h3").html("<b>Distance (km) :</b>" + window.distance + "<br/><b>Durée (h) :</b>" + window.time/60);
            });

            // CAM FIT TO ROUTE
            bounds = L.latLngBounds(toTrace);
            map.fitBounds(bounds);
        }
    </script>
    </body>
</html>
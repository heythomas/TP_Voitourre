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
        echo("Fait de tete stp merci <br/>");
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
        // GET TRAVEL TIME
        var regex = /[^0-9]*([0-9]*)[^0-9]*([0-9]*)/;

        document.getElementById("test").onclick = function(){
            el = document.getElementById("car_selector").options[document.getElementById("car_selector").selectedIndex]
            textBrut = el.text
            reg = textBrut.match(regex)
            voiture = "null"
            autonomie = reg[1];
            tps_recharge = reg[2];
            vitesse = 130;

            $.get( "http://127.0.0.1:5000//calcultempstrajet", { voiture: voiture, autonomie: autonomie, tps_recharge: tps_recharge, long_trajet:1200 } )
            .done(function( data ) {
                alert( "Le temps de parcours sera de : " + (parseInt(parseInt(data["temps"])/60)) + "h" + (parseInt(parseInt(data["temps"])%60)<10?'0':'') + parseInt(parseInt(data["temps"])%60)  + "min");
            });
            
        };

        // GET NEAREST REFUEL POINT
        function find_refuel(coo){
            $.get( "https://opendata.reseaux-energies.fr/api/records/1.0/search/", { dataset:"bornes-irve", q:"", rows:"1", sort:"-dist", facet:"region", "geofilter.distance":"45.6389834801384, 5.876346600707621, 5000" } )
            .done(function( data ) {
                alert(data["records"][0]["geometry"]["coordinates"]);
            });
        }

        // MAP DISPLAY - Inverser latitude et longitude ici (dans setview)
        var map = L.map('map').setView([46.496541, 1.997267], 13);
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
        marker = L.marker(map.getCenter()).addTo(map);
        marker2 = L.marker([45.64036370303461, 5.871814725687728]).addTo(map);
        
        // ROUTE VARIABLES
        var routeControl;
        var time;

        map.on('click', function(e) {
            marker.setLatLng(e.latlng).update();
            console.log("Lat, Lon : " + marker.getLatLng().lat + ", " + marker.getLatLng().lng)
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
                distance = e.routes[0].summary.totalDistance;
                console.log('routing distance: ' + distance);
            });

            // STEP TWO, GET NAIVE TIME FOR THIS ROUTE
        }
    </script>
    </body>
</html>
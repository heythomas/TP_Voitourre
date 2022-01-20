<?php
try{
    // Connexion 
    $clientSOAP = new SoapClient("http://127.0.0.1:8000/?wsdl");
    $resultats = $clientSOAP->get_cars();
    
    // Print des résultats obtenus dans un menu déroulant
    foreach ($resultats as $res) {
        echo("<select name='cars' id='car_selector'>");
        echo("<option value=''>Veuillez sélectionner une voiture...</option><br/>");
        foreach($res->string as $el){
            echo("<option value=''>".$el."</option><br/>");
        }
        echo("</select>");
    }
}catch(SoapFault $e){
    // Cas ou l'import SOAP à échoué
    echo("Fait de tete stp merci <br/>");
}

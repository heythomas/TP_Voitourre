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

?>

<div id="result">

</div>
<button id="test">Calculer</button>
<script src="./jquery.js"></script>
<script>
    function ajaxReq() {
        if (window.XMLHttpRequest) {
            return new XMLHttpRequest();
        } else if (window.ActiveXObject) {
            return new ActiveXObject("Microsoft.XMLHTTP");
        } else {
            alert("Browser does not support XMLHTTP.");
            return false;
        }
    }

    var regex = /[^0-9]*([0-9]*)[^0-9]*([0-9]*)/;

    document.getElementById("test").onclick = function(){
        el = document.getElementById("car_selector").options[document.getElementById("car_selector").selectedIndex]
        textBrut = el.text
        reg = textBrut.match(regex)
        voiture = "null"
        autonomie = reg[1];
        tps_recharge = reg[2];
        vitesse = 130

        // fetch('http://127.0.0.1:5000/calcultempstrajet?voiture='+voiture+'&autonomie='+autonomie+'&tps_recharge='+tps_recharge+'&vitesse='+vitesse)
        // .then(response => {
        //     console.log(response)
        // });
        
        $.ajax({
            type: 'POST',
            url: 'http://127.0.0.1:5000/calcultempstrajet?voiture='+voiture+'&autonomie='+autonomie+'&tps_recharge='+tps_recharge+'&vitesse='+vitesse,
            dataType: 'text/html',
            success: function() { alert("Success"); },
            error: function() { alert("Error"); }
        });
        
    };
</script>
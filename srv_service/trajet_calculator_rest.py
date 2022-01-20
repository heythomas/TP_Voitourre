from flask import Flask
from flask_restful import Resource, Api, reqparse
import ast

app = Flask(__name__)
api = Api(app)

class CalculTempsTrajet(Resource):
    def post(self):
        # Récupération des données de l'URL
        parser = reqparse.RequestParser()
        parser.add_argument('voiture', required=True)
        parser.add_argument('autonomie', required=True)
        parser.add_argument('tps_recharge', required=True)
        parser.add_argument('long_trajet', required=True)
        
        # Compilation dans un dictionnaire
        args = parser.parse_args()

        # Data
        vitesse = 130/60
        long_trajet = float(args["long_trajet"])
        autonomie = float(args["autonomie"])
        voiture = args["voiture"]
        tps_recharge = float(args["tps_recharge"])

        # Calcul du temps de trajet
        trajetFini = False
        trajet_restant = long_trajet
        autonomie_restante = autonomie
        temps = 0.0

        # Calcul
        while(not trajetFini):
            if(trajet_restant <= autonomie_restante):
                temps += trajet_restant//vitesse
                trajetFini = True
            else:
                temps += autonomie_restante/vitesse
                trajet_restant -= autonomie_restante
                autonomie_restante = 0
                if(trajet_restant <= autonomie*0.8):
                    temps += (autonomie/trajet_restant * tps_recharge)*1.2
                    autonomie_restante = trajet_restant
                else:
                    temps += tps_recharge
        
        return {'temps': temps}, 200



api.add_resource(CalculTempsTrajet, '/calcultempstrajet')

if __name__ == '__main__':
    app.run()  # run our Flask app
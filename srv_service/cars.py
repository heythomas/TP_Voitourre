from spyne import Iterable
from spyne.protocol.soap import Soap11
import lxml
from spyne import Application, rpc, ServiceBase, \
    Integer, Unicode
import spyne
from wsgiref.simple_server import WSGIServer
from wsgiref.simple_server import make_server
from spyne.server.wsgi import WsgiApplication

# Liste des véhicules disponibles
cars = [["Tesla Model S", 652, 60], ["Renault Zoe", 377, 180], ["Citroën Ë-C4", 323, 120], ["Ford Mustang Mach-E", 500, 55]]

# Service
class My_Cars(ServiceBase):

    @rpc(_returns=Iterable(Unicode))
    def get_cars(ctx):
        for i in range(len(cars)):
            yield cars[i][0] + " - Autonomie(km): " + str(cars[i][1]) + " - Temps de Recharge (min): " + str(cars[i][2])
    
application = Application([My_Cars], 'spyne.examples.hello.soap', in_protocol=Soap11(validator='lxml'), out_protocol=Soap11())
wsgi_application = WsgiApplication(application)

if __name__ == "__main__":
    server = make_server('127.0.0.1', 8000, wsgi_application)
    server.serve_forever()
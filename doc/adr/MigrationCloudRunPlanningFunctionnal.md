# Plan de migration DNS
1. Contexte

Dans le cadre de la migration app engine vers cloud run nous devons migrer nos DNS :
* Migration des crons
* Migration de back.tediber.com
* Migration de sav.tediber.com pro.tediber.com phenix.tediber.com
* Migration de api.tediber.com
  Afin de vérifier les migrations et de pouvoir suivre les erreurs, nous devrons mettre en place la strategie de log ainsi que sentry et backfire avant de migrer.

1. Les phases de migrations :
   **A. Migration des crons vers la nouvelle infra**
* Création des nouveaux crons sur la nouvelle infra
* configuration de leur planification
* test de connectivité avec le serveur destinataire
* désactivation des crons sur l'ancienne infra
* activation des crons sur la nouvelle infrastructure
* Verification que les crons s'executent bien.

**Mise en place de cette solution 1 jours, puis verification 3 jours**

**B. Migration du site back.tediber.com**
* Deploiement de l'app sur la nouvelle infra cloud run
* Résolution du DNS pour la nouvelle infrastructure
* Désactiver DNS sur ancienne infra
* test
    * passage de commande
        * verification du calendrier de livraison
        * paiement
    * retour
    * envoi des commandes à odoo
    * envoi des produits de odoo vers sylius


**Mise en place de cette solution 1 jours, puis VSR(Vérification service régulier) 5 jours, à la fin de la vérification de l'étape A**

**C. Migration du site de sav.tediber.com pro.tediber.com phenix.tediber.com**

* Deploiement de l'app sur la nouvelle infra cloud run
* Résolution du DNS pour la nouvelle infrastructure
* Désactiver DNS sur ancienne infra
* Test
    * passage de commande
    * envoi de l'information à sylius

**Mise en place de cette solution 1 jours, puis VSR(Vérification service régulier) 5 jours, à la fin de la VSR de l'étape B**

**C. Migration api.tediber.com**

* Deploiement de l'app sur la nouvelle infra cloud run
* Résolution du DNS pour la nouvelle infrastructure
* Désactiver DNS sur ancienne infra
* Test
    * passage de commande
    * envoi de l'information à sylius
    * paiement

**Mise en place de cette solution 1 jours, puis VSR(Vérification service régulier) 5 jours, à la fin de la VSR de l'étape B**


# Planning
Jour 0 : Situation actuelle
Jour 1 : Migration cron
Jour 2-4 : VSR CRON
Jour 5 (si different de vendredi) : Migration site back.tediber.com
Jour 5 à 11 : VSR back.tediber.com ( verification des bonnes remontées de commandes, des bonnes communication entre spidy/lokad/odoo/prestataire paiement/calendrier transporteur)
Jours 12 : Migration du site de sav.tediber.com pro.tediber.com phenix.tediber.com
Jour 12 à 17 : VSR back.tediber.com ( verification des bonnes remontées de commandes, des bonnes communication entre spidy/lokad/odoo/prestataire paiement/calendrier transporteur)
Jour 18: Migration api.tediber.com
Jour 18 à 23 : VSR back.tediber.com ( verification des bonnes remontées de commandes, des bonnes communication entre spidy/lokad/odoo/prestataire paiement/calendrier transporteur)

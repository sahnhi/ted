# Migration Cloud Run : la base de donnée 

* Date: 02-12-2022

## Context and Problem Statement

Transition sans perte de donnée ni indisponibilité

## Decision Drivers

* aucune perte de donnée
* aucune indispo
* go prod asap
* recette / beta-test via le SAV
* monitoring lors de la livraison
* on veut protéger la DB finale (si mauvaise écriture suite au passage à PHP7.4 ou alors des scenarii un peu complexe avec de la logique exec en asynchrone genre nouvelle commande qui envoie un email en asynchrone ou qui genere une facture dans un cloud storage)

## Considered Options

* option A : 2 DBs en ecriture replicas master-master dans chaque environnement
* option B : les nouveaux services écrivent sur l'ancienne DB
* option C : 2 DBs master-slave donc le nvo projet écrit dans la slave

## Pros and Cons of the Options

### Option A : 2 DBs en ecriture replicas master-master

<!-- ajouter une descritpion détaillée de la solution ? -->

* avantage
  * ca repond au besoin d'avoir une migration partielle des services en theorie ;)

* inconvenient :
  * investigation sur la faisabilité dans **cloud sql** sachant qu'on est assez pessimiste
  * si le nouveau projet est buggé on risque d'écrire des données qu'on ne veut dans la "vraie" DB
  * l'experience semble nous indiquer que c'est l'usine mais pas tout le temps :
    * une experience positive avec MongoDB

### Option B : les nouveaux services écrivent sur l'ancienne DB

<!-- ajouter une descritpion détaillée de la solution ? -->

* avantage
  * simplicité (solution peu couteuse)
  * ca repond au besoin d'avoir une migration partielle des services en theorie ;)

* inconv
  * si le nouveau projet est buggé on risque d'écrire des données qu'on ne veut dans la "vraie" DB
  * on repousse le travail de migration de la DB meme si on "gagne" du temps avant le go prod
  * le rollback est plus lent et il faut le préparer **avant le Go PROD**

EDIT: Concernant cette option, on a évoqué égalemnent l'utilité d'activer les logs sur la DB le temps de valider la "santé" de la nouvelle app. Ça pourrait nous être utile en cas de scenario désastre car on pourrait s'en servir pour scripter une commande de data recovery.

### Option C : 2 DBs master-slave

<!-- ajouter une descritpion détaillée de la solution ? -->

* avantage : 
  * pas d'interruption de service
  * **rollback facile**
  * si cloud run est buggé et tente d'écrire des donnée erronées alors on protege quand meme DB master (cool pour en phase recette i.e. avant la phase de beta test)
  * si cloud run est OK alors la slave devient la nouvelle DB et on a toutes les données donc tout va bien il n'y a plus rien à faire

* inconvenient : 
  * si migration partielle / Beta test, si on persist une entree l'id sera repercuté dans un sens mais pas dans l'autre
    * reflexion sous-jacente : ca implique que si on revert alors il faudra demander aux beta-testeurs de recommencer leurs opérations sur la prod app engin
    * reflexion sous-jacente bis : risque d'erreurs si un environnement écrit dans un replica slave sur une table partagée = si on a un INSERT coté slave alors l'id de la séquence de la slave est incrémenté mais pas celui de la master auquel cas au prochain insert coté master ca cassera >> **nécessiter de trouver une solution pour ce cas de figure avant de le valider**
  * cette solution apporte de la complexité avant go live sans apporter vraiment de benefice alors qu'on cherche à livrer asap
  * il faudrait quand meme POCer/investiguer si 2 INSERT en meme temps est ce que ca passe pour la sequence ?

## Outcome: meeting 05-12-2022

L'option C est seduisante mais il faut POC les scenarii :
* insert coté slave puis insert coté master : qu'est ce qu'il se passe dans ce cas ? si erreur, est ce qu'on peut corriger ? comment ? sequences differentes entre la slave et la master ? Lock partagé entre les DBs ou pas ?
* terminer une commande des 2 cotés et voir si ca se passe bien (on peut retrouver la donnée dans la DB repliquée)

TODO: Configurer un Load Balancer pour gérer le "switch DNS"
TODO: POC gsutil cp "rsync"

### Resultats des POCs

* écritures slave MySQL : le POC est "successful" car le slave peut bel et bien avoir des séquences distinctes de la naster en ce qui concerne les PK. On n'a pas vérifié pour les FKs parce qu'on a finalement choisi de drop l'option car la DB contient pas mal de tables complexes (sylius entre autres) et la solution semble complexe dans son ensemble et l'on risquerait d'introduire plus de problemes/difficultés que de solutions.

* concernant les buckets, le POC est terminé également. Nous en avons testé plusieurs : 1/ le job asynchrone schédulable qui synchronise tous les buckets. 2/ la cloud function fait maison 3/ un nouveau service GCP encore en beta i.e. "transfert job event-drive". Chaque solution présente des avantages et des inconv
    1. job asynchrone schedulable
        * av     = simplicité
        * inconv = délai entre les executions qui peut engendrer des 404 si le fichier est introuvable
    2. cloud function
        * av     = on maitrise et on peut gérer les comportements comme on veut (meme si à priori notre besoin est completement classique)
        * inconv = complexité
    3. service google
        * av     = on profite d'une maintenance google peut etre ??
        * inconv = il ne gère pas les cas complexes comme le renommage ou le déplacement mais plus embetant encore il ne gère pas les suppressions... + pas gratuit en terme de complexité (1 pubsub pour chaque bucket et chaque environnement)

* POC cloud run écrit dans la slave = Impossible (du moins en service managé) car GCP verrouille les replicas en lecture seule. À noter que nous n'avons pas insisté à chercher comment faire sauter ce verrouillage. Ainsi, dans cette solution on aura un peu d'indisponibilité. Mais de toute façon c'est une indisponibilté qu'on aura tot ou tard quand on devra migrer la DB sur le nouveau projet

## Plan de migration

### Si double run / beta test

* augmenter la frequence des backup DB (TODO? POC pour evaluer la charge CPU?)
* activer les logs dans la DB
* creer un user DB dédié à cloud run `tediber-cloudrun`
* donner les droits d'acces au serviceAccount:tediber@tediber-368210.iam.gserviceaccount.com pour les buckets de prod
* déployer cloud run en branchant à l'ancienne DB
    * PI les utilisateurs seront tous déco
* ajouter le sous-domaine pour la resolution DNS
<!-- une fois la beta validée -->
* ajouter les autres sous-domaines et couper app engine

**en cas de scenario desastre:**
* supprimer le sous-domaine DNS
* retrouver toutes les queries passées avec le user tediber-cloudrun
* restore le dernier backup de la DB 
* rejouer les queries tediber-cloudrun qu'on veut garder via une commande/migration de data recovery
* traiter les cas plus complexes via le SAV


### Si pas de double run + migration DB en // de la migration app engine

* préparer la migration Cloud SQL avec son replica en lecture
* activer le transfert de buckets selon la methode choisie
* deployer un Load Balancer qui route 100% du trafic sur app engine
* basculer la résolution DNS sur le LB
* déployer cloud run en le branchant sur le replica (pas d'écriture possible pour l'instant donc meme pas de login)
    * PI les utilisateurs seront tous déco
<!-- la suite doit durer le moins longtemps possible car le site sera indisponible -->
* "couper" le LB (ou bien servir une page de maintenance - hébergée sur GCS peut etre??) ou bien encore préparer une jolie erreur au login en cas d'échec d'écriture
* promouvoir le replica
* [rétablir/basculer le LB si nécessaire en fonction de l'option choisie]
* couper app engine

**en cas de scenario desastre:**
* basculer le LB sur app engine
* retrouver toutes les queries passées sur la nouvelle DB (diff sur les tables importantes)
* insert les commandes et autres données critiques via une commande/migration de data recovery ou manuellement via le back office


### Si pas de double run et pas de migration DB

* activer les logs dans la DB
* creer un user DB dédié à cloud run `tediber-cloudrun`
* donner les droits d'acces au serviceAccount:tediber@tediber-368210.iam.gserviceaccount.com pour les buckets de prod
* déployer cloud run en branchant à l'ancienne DB
    * PI les utilisateurs seront tous déco
* deployer un Load Balancer qui route 100% du trafic sur app engine
* basculer la résolution DNS sur le LB
* router 100% du trafic du LB sur cloud run

**en cas de scenario desastre:**
* basculer le LB sur app engine
* retrouver toutes les queries passées avec le user `tediber-cloudrun`
* traiter au cas par cas


## Meeting du 24 janv 2023

Personnes présentes : Maissane, Saheme, Sebastien, Anthony

Décisions : 
* on drop le double run à cause de la complexité technique qu'il induit
* on demande un feature freeze d'une semaine @Saheme
* on hesite encore entre la solution B (cloud run branché sur l'ancienne DB) et C' (cloud run branché sur la nouvelle DB en lecture seule puis on migre tout d'un coup)

## Decision Outcome

<!-- Chosen option: "[option 1]", because [justification. e.g., only option, which meets k.o. criterion decision driver | which resolves force force | … | comes out best (see below)]. -->

### Positive Consequences

<!-- * [e.g., improvement of quality attribute satisfaction, follow-up decisions required, …] -->

### Negative Consequences

<!-- * [e.g., compromising quality attribute, follow-up decisions required, …] -->

## Links

* ...

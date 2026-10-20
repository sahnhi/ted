Technical Migration Planning
===========

Ceci est un planning technique de la migration du site Tediber d'App Engine vers Cloud Run.

Avec ce planning nous souhaitons définir les étapes nécessaires à la migration ainsi que leurs responsables et les solutions éventuelles en cas d'erreurs.

## Before migration

- J-15 : POC connexion à la DB de démo (Anthony)
- J-13 : POC copie des anciens buckets vers les nouveaux (Anthony)
- J-12 : POC connexion Sentry et Blackfire, potentiellement créer des nouveaux environnements (Anthony, Guillaume et Sébastien)
- J-10 : revoir les configurations des CRONs pour s'assurer qu'elles matchent avec les anciennes. Si besoin revoir les besoins métiers pour adapter. (Anthony et Sahème)
- J-7 : version CloudRun identique le plus possible à la prod actuelle (Anthony et Sébastien)
- J-7 -> J0 : tests fcontionnels et techniques complets de la nouvelle application (Equipe IT, Sahème, Céline et Guillaume) 

## Migration

- J0 : connexion à la DB de prod (Anthony et Sébastien)
- J0 : copie des données des anciens buckets de prod vers les nouveaux, étape à répéter si besoin (Anthony et Sébastien)
- J0 : migration DNS de back.tediber.com vers la nouvelle infra (Guillaume)
- J0 -> J7 : recette back.tediber.com (Equipe IT)
- J1 : arrêt des anciens CRONs ? => que se passe t'il pour les autres DNS qui normalement doivent encore utiliser les anciens CRONs
- J1 : activation des nouvelles CRONs (Anthony et Sébastien)
  - Si erreurs, rollback vers les anciennes CRONs
- J7 : migration DNS de sav.tediber.com, pro.tediber.com et phenix.tediber.com (Guillaume)
- J7 -> J14 : recette complète de ces 3 DNS (Equipe IT)
- J14 : migration du DNS api.tediber.com (Guillaume)
- J14 -> J21 : recette complète de api.tediber.com (Equipe IT)

## After migration

Peut être fait en parallèle de la migration prod et des tests

- J+0 : préparation des environnements staging et demo (Anthony et Sébastien)
  - Création des projets infra
    - DB
    - MemoryStore
    - Cloud Run services
    - GCS buckets
    - Gitlab-CI
    - Service accounts
- J+2 : copie des anciennes DBs vers les nouvelles infra (Anthony et Sébastien)
- J+3 : copie des anciens buckets vers les nouveaux (Anthony et Sébastien)
- J+4 : recette complète de ces environnments (Equipe IT)

Doit être fait après la migration de la prod

- J+X : migration de l'ancienne DB prod vers la nouvelle infra
- J+X : Création d'un projet de gestion
  - images docker
  - service account déploiement
  - ...

## A définir

- Que se passe-t-il en cas de mauvaises écritures sur la DB ?
- Comment gérons nous les actions des CRONs sur les commandes d'api.tediber.com si les CRONs ne sont plus activées ?
- CVS/flow-git pendant le double run : dans la CI ? dans les equipes de dev ?
- ...

# Planning au 30/01/2023

Suite aux Pocs, les décisions suivantes ont été prises :

- Buckets : la nouvelle prod pointera vers les anciens buckets
- DB : la nouvelle prod pointera vers l'ancienne DB
- DNS : tous les DNS seront migrés en même temps
- Recette : une phase de recette complète est lancée le 30/01/2023

## Reste à faire avant migration

### Buckets

- Poc connection entre les buckets de différents projets => Anthony ou Sébastien

### DB

- Poc connection vers la DB d'un autre projet => done by Anthony

### DNS

- Poc / Mise en place d'un LB pour le switch DNS plus rapide
- Le LB doit pouvoir switch le trafic entre l'ancienne et la nouvelle prod (deux projets)
- Si non concluant, trouver une solution via CloudFlare
- Voir avec Guillaume pour tout ce qui concerne CloudFlare 

### Recette

- Lancement de la phase de recette => Maissane

### Création des envs de test

- Environnement demo
- Environnement staging

## Test migration au 07/02/2023

- LB créé entre la demo et la nouvelle prod
- Connection du nouveau projet aux buckets demo
  - Donner les droits Storage Admin sur le projet ancienne prod => done
  - S'assurer que les buckets sont bien configurés dans .env.ops
- Connection du nouveau projet à la DB de démo
  - Donner les droits CLoud Sql Client sur le projet ancienne prod => done
  - Modifier les vars d'env MYSQL
- Activation de la sortie du LB vers nouvelle prod

## LB

- LB créé sur la nouvelle infra
- LB pointe sur l'ancienne prod
- Migration DNS sav.tediber.com vers LB
- Migration de tous les DNS
- Ajouter la sortie vers la nouvelle prod
- Suite aux tests, on abandonne la solution avec le LB

## Migration de la prod au 14/02/2023

Dans la matinée

- Backup de la DB de prod => done
- Connection de la nouvelle prod aux buckets de l'ancienne prod 
  - Donner les droits Storage Admin sur le projet ancienne prod => done
  - S'assurer que les buckets sont bien configurés dans .env.prod => done
- Connection de la nouvelle prod à la DB de l'ancienne prod
  - Donner les droits CLoud Sql Client sur le projet ancienne prod => done
  - Modifier les vars d'env MYSQL => done
- Création du tag plus déploiement des nouveaux services

Directement à la suite

- Désactivation des anciennes CRONs
- Activation des nouvelles CRONs
- Migration progressive des DNS
  - phenix.tediber.com => done
  - back.tediber.com
  - pro.tediber.com
  - sav.tediber.com
  - retail.tediber.com => done
  - api.tediber.com
- Modification hostname dans le channel boutique

## Post migration

- Remettre les vars d'env de Blackfire en mode prod
- Migration des buckets vers la nouvelle prod
- Migration de la DB vers la nouvelle prod
- Migration de certains aspects vers le projet de gestion pour tout ce qui est commun
  - Credentials Gitlab
  - Docker Images
  - GitlabRunners => done
- Décommission phenix.tediber.com
- Décommission ancien projet : model-magnet
- Clean les DNS records

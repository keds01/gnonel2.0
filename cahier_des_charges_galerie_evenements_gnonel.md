# Cahier des Charges Fonctionnel & Technique

## Projet : Application Web gnonel
## Module : Galerie d'Événements (Gestion & Affichage)
## Date : 7 Septembre 2026
## Statut : En attente de validation
## Réalisation : Ekoué KOUVAHE
## Délai de livraison : 10 jours à compter du OK

---

## 1. Présentation du Projet

L'objectif principal du projet est d'ajouter un module de Galerie d'Événements à l'application web gnonel.

Ce module permettra :
- de publier et d'organiser des photos d'événements ;
- de donner une description détaillée aux événements et aux images ;
- d'importer des images individuellement ou par lots ;
- d'offrir aux visiteurs la possibilité d'aimer des photos et/ou événements ;
- de garantir une gestion complète côté administration via les opérations CRUD.

Le module sera pensé pour répondre à la fois à un besoin de valorisation des activités de l'entreprise et à un besoin de mise en avant visuelle des événements passés et actuels.

---

## 2. Objectifs & Périmètre

### 2.1 Objectifs

- Valoriser les activités : permettre la consultation visuelle des événements passés et présents.
- Encourager l'engagement utilisateur : offrir la possibilité de liker les photos et/ou événements.
- Optimiser le gain de temps administratif : permettre des imports de fichiers en lot.
- Assurer une gestion complète : créer, lire, modifier et supprimer les albums, événements et photos.

### 2.2 Périmètre fonctionnel

Le système comprendra deux grandes zones :

1. Back-office / administration
   - gestion des événements / albums ;
   - gestion des photos en import unitaire et groupé ;
   - modification, activation, masquage, suppression.

2. Front-office / visiteur
   - consultation des galeries ;
   - filtres et recherche ;
   - affichage détaillé d'un événement ;
   - visionneuse photo ;
   - système de likes.

### 2.3 Acteurs & Droits

#### Administrateur (Back-Office)
L'administrateur dispose d'un accès complet sur les fonctionnalités suivantes :
- création d'un événement ;
- ajout, modification et suppression de photos ;
- gestion des couvertures ;
- réorganisation des images ;
- gestion des descriptions, légendes et paramètres de visibilité.

#### Utilisateur / Visiteur (Front-Office)
Le visiteur peut :
- parcourir les albums et événements ;
- consulter les détails d'un événement ;
- filtrer par année, catégorie ou mot-clé ;
- ouvrir une photothèque en plein écran ;
- liker les photos et/ou événements.

---

## 3. Spécifications Fonctionnelles

### 3.1 Côté Administration (Back-Office)

#### A. Gestion des Événements / Albums

##### Création d'un événement
Chaque événement doit contenir au minimum :
- Titre de l'événement (obligatoire)
- Date de l'événement (obligatoire)
- Catégorie / type d'événement (optionnel)
- Description globale (court ou enrichi)
- Image de couverture (obligatoire ou recommandée)

##### Modification & Suppression
L'administrateur doit pouvoir :
- modifier le titre ;
- modifier la date ;
- éditer la description ;
- changer l'image de couverture ;
- réorganiser ou supprimer des photos ;
- supprimer un événement entier avec confirmation.

##### Règles métier
- Un événement peut contenir plusieurs photos.
- Une image de couverture doit être associée à un événement.
- Une suppression d'événement doit demander confirmation explicite avant exécution.

#### B. Gestion des Photos (Ajout unitaire et en lot)

##### Import en lot (Bulk Upload)
Le système doit permettre :
- glisser-déposer des fichiers dans une zone dédiée ;
- sélection multiple de fichiers via bouton de navigation ;
- affichage d'une barre de progression pendant le chargement ;
- validation des formats acceptés : .png, .jpg, .jpeg, .webp ;
- limite de taille par fichier (ex. 5 Mo max) ;
- optimisation ou compression automatique des images.

##### Ajout unitaire / édition détaillée
Pour chaque photo, l'administrateur doit pouvoir :
- ajouter une légende ou description spécifique ;
- définir si la photo est masquée ou visible ;
- mettre en avant une image ;
- supprimer une photo individuelle dans le cadre d'un événement.

##### Règles métier
- Les fichiers non conformes doivent être rejetés avec un message clair.
- Le système doit conserver une image d'origine (si nécessaire) et générer des versions optimisées.
- Les photos doivent être associées à un événement existant.

---

### 3.2 Côté Utilisateur (Front-Office)

#### A. Page Principale de la Galerie

La page principale doit afficher :
- une grille responsive d'albums ou de photos ;
- des cartes visuelles avec couvertures ;
- titre de l'événement ;
- date ;
- nombre de photos ;
- compteur de likes.

##### Filtres
Le visiteur doit pouvoir filtrer par :
- année ;
- catégorie ;
- mot-clé ou recherche libre.

##### Contrôle visuel
Chaque carte doit afficher au minimum :
- image de couverture ;
- titre ;
- date ;
- nombre de photos ;
- total de likes ;
- bouton d'accès rapide à l'événement.

#### B. Vue Détaillée d'un Événement & Interactivité

La vue détaillée doit présenter :
- description globale de l'événement ;
- galerie de photos associées ;
- compteur global de likes ;
- possibilité de liker la photo et/ou l'événement.

##### Fonctionnement du Like
- bouton “Like” avec icône cœur ou pouce ;
- incrémentation dynamique du compteur sans rechargement complet de page ;
- utilisation d'AJAX / Fetch pour une meilleure expérience utilisateur ;
- système anti-multi-vote simple basé sur session locale, cookie ou identification utilisateur ;
- feedback visuel immédiat lors du clic.

##### Visionneuse (Lightbox)
Le visiteur doit pouvoir :
- cliquer sur une photo pour l'ouvrir en plein écran ;
- naviguer entre les images via boutons précédent / suivant ;
- utiliser le clavier pour avancer ou reculer ;
- consulter la légende ou description associée ;
- liker directement depuis la visionneuse.

---

## 4. Spécifications Techniques & Exigences

### 4.1 Stockage & Performance

Le module devra stocker les médias sur le serveur de production, à savoir le serveur LWS ou un stockage équivalent.

Les exigences principales sont les suivantes :
- stockage des images sur le serveur web ou en FTP/SFTP via le système de fichiers du projet ;
- génération automatique de vignettes (thumbnails) pour réduire la charge de rendu ;
- optimisation des images pour économiser l'espace disque et la bande passante ;
- support de formats modernes comme WebP ;
- pagination ou chargement progressif des données pour améliorer la performance ;
- lazy loading pour les images longues ou volumineuses.

### 4.2 Formats & Compression

Le système doit :
- accepter uniquement les types d'images supportés ;
- transformer les images vers un format optimisé (.webp) si nécessaire ;
- limiter la taille des fichiers pour éviter la saturation du serveur ;
- conserver une qualité visuelle acceptable pour les photos de galerie.

### 4.3 Ergonomie & UX/UI

Le module doit proposer :
- design responsive pour mobile, tablette et desktop ;
- animations de feedback sur le clic Like ;
- modales de confirmation pour toute suppression définitive ;
- messages d'erreur explicites si le format ou la taille est incorrect ;
- barre de progression claire pendant les uploads multiples ;
- navigation claire et fluide entre albums, photos et lightbox.

---

## 5. Règles de Gestion des Données

### Entités proposées

#### Événement / Album
- id
- title
- date_event
- category
- description
- cover_image
- status
- created_at
- updated_at

#### Photo
- id
- event_id
- title
- description
- file_name
- file_path
- thumbnail_path
- is_featured
- is_hidden
- mime_type
- size
- created_at
- updated_at

#### Like
- id
- event_id ou photo_id
- user_identifier
- session_id
- created_at

### Règles métier
- Un événement peut avoir plusieurs photos.
- Une photo appartient à un seul événement.
- Un visiteur ne peut liker qu'une seule fois par photo ou événement selon la règle anti-multi-vote.
- La suppression d'un événement peut supprimer ou garder les photos selon la politique définie.

---

## 6. Cas d'Utilisation Principaux

### 6.1 Administrateur
- Créer un événement.
- Uploader plusieurs photos.
- Ajouter des légendes.
- Modifier une photo ou un événement.
- Supprimer une photo ou un événement.
- Définir une couverture.

### 6.2 Visiteur
- Consulter la galerie.
- Filtrer les événements.
- Ouvrir les détails.
- Vérifier les likes.
- Visionner les photos en plein écran.
- Liker une image ou un événement.

---

## 7. Critères d’Acceptation

Le module sera validé si les conditions suivantes sont respectées :

1. L’administrateur peut créer, modifier et supprimer un événement.
2. L’administrateur peut ajouter des photos individuellement et en lot.
3. Les images sont contrôlées en fonction du format et de la taille.
4. Le front-office affiche la galerie de manière responsive.
5. Les filtres de recherche fonctionnent correctement.
6. Les likes augmentent immédiatement et sans rechargement complet.
7. La lightbox permet la consultation des images en plein écran.
8. L’ensemble est compatible avec les exigences de performance et de stockage du serveur LWS.

---

## 8. Contraintes & Dépendances

- Le projet doit être compatible avec l’architecture actuelle de gnonel.
- La solution doit utiliser les technologies déjà mises en place dans l’application, ou des composants compatibles.
- Les fichiers multimédias doivent être gérés avec une structure de stockage claire et sécurisée.
- Le système doit rester robuste face aux erreurs de téléchargement, aux fichiers lourds et aux formats non supportés.

---

## 9. Livrables Attendus

- Module de galerie fonctionnel côté back-office ;
- Module de consultation côté front-office ;
- Gestion des imports et optimisations d’images ;
- Système de likes avec restriction de vote ;
- Lightbox / visionneuse interactive ;
- Documentation d’utilisation rapide pour l’administration.

---

## 10. Conclusion

Le module Galerie d'Événements apporte à gnonel une dimension plus visuelle, plus interactive et plus engageante. Il répond à des besoins de valorisation des événements, de communication marketing, de gestion administrative simplifiée et de centralisation des contenus multimédias.

Le projet est bien cadré, réalisable en 10 jours, et son périmètre couvre à la fois la gestion administrative et l’expérience utilisateur de consultation et d’interaction.

---

## Signature / Validation

- Projet : gnonel
- Module : Galerie d'Événements
- Responsable / Réalisation : Ekoué KOUVAHE
- Date de soumission : 7 Septembre 2026
- Statut : En attente de validation

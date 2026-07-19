---
title: "Procédure de recette client V1"
subtitle: "Inventaire des artistes neuchâtelois·es"
lang: fr-FR
date: 2026-07-15
---

## Objectif

Ce document permet de confirmer la livraison de la V1 sur l'environnement de staging.

Le périmètre ci-dessous a été resserré pour ne couvrir que les fonctionnalités explicitement incluses dans la V1 livrée. Cette version est centrée sur l'entrée en service du parcours artiste : demande de référencement, traitement gestionnaire, réception des e-mails et accès à l'espace artiste pour commencer à renseigner les données. Les fonctionnalités de consultation publique, de recherche et d'exploration de l'annuaire ne font pas partie de cette recette.

Environnement testé : <https://artistes-staging.ne.ch>

Back-office gestionnaire : <https://artistes-staging.ne.ch/admin>

La recette doit se concentrer sur les parcours métier visibles et sur les points bloquants. Pour chaque cas, noter l'un des statuts suivants :

- OK : comportement conforme
- KO : anomalie bloquante ou écart fonctionnel
- N/A : cas non testable sur l'environnement fourni

## Prérequis

- Disposer d'un accès à l'environnement de staging.
- Disposer d'au moins deux boîtes e-mail de test différentes (une pour le scénario nominal et une pour les scénarios d'erreur ou de relance). Une autre possibilité est d'utiliser le "Plus Addressing" d'email <https://blog.netim.com/fr/e-mail/plus-addressing-14518/>.
- Disposer d'un compte gestionnaire pour le back-office.
- Préparer à l'avance un jeu de données de test : une identité complète valide, une image de test (si applicable) et des valeurs volontairement invalides (e-mail malformé, champs vides, fichier trop lourd) pour les cas d'erreur.
- Si possible, tester une fois sur mobile et une fois sur desktop.
- Noter l'heure de chaque action pour pouvoir comparer avec l'horodatage des e-mails reçus.
- Utiliser une fenêtre de navigation privée distincte pour chaque rôle testé (artiste, gestionnaire), ou à défaut deux navigateurs différents (par exemple Chrome pour le gestionnaire et Firefox pour l'artiste). Cela évite qu'une session reste connectée à la place d'une autre et fausse les résultats, notamment lors des allers-retours entre l'espace artiste et le back-office.
- Fermer la fenêtre de navigation privée entre deux scénarios (par exemple entre le test nominal et le test de refus) pour repartir sur une session totalement propre.

## Parcours complet artiste / gestionnaire (scénario nominal de bout en bout)

Ce parcours doit être exécuté une première fois intégralement, sans interruption, pour valider que le circuit d'inscription fonctionne réellement de bout en bout. Les cas détaillés plus bas (section « Cas de test V1 ») permettent ensuite de rejouer chaque étape individuellement, y compris les cas d'erreur.

Recommandation : garder deux fenêtres ouvertes en parallèle, l'une en navigation privée pour le rôle artiste, l'autre pour le rôle gestionnaire, afin de basculer facilement de l'une à l'autre sans se déconnecter ni mélanger les sessions.

1. **Artiste** : se rendre sur `Devenir artiste` et remplir la demande de référencement avec des données complètes et valides (identité, coordonnées, activité, documents si demandés). Noter l'adresse e-mail utilisée et l'heure d'envoi.
2. **Système** : vérifier qu'un message de confirmation de dépôt s'affiche immédiatement à l'écran (l'envoi d'un e-mail à ce stade n'est pas forcément attendu, voir le cas V1-03 pour le détail).
3. **Gestionnaire** : se connecter au back-office, retrouver la demande dans la liste des demandes de référencement en attente, l'ouvrir et vérifier que toutes les données saisies par l'artiste sont visibles et lisibles.
4. **Gestionnaire** : approuver la demande.
5. **Système** : vérifier qu'un e-mail est envoyé à l'artiste suite à l'approbation, et qu'un second e-mail (ou le même selon l'implémentation) contient le magic link permettant de finaliser le profil. Noter le délai de réception.
6. **Artiste** : ouvrir l'e-mail reçu, cliquer sur le magic link et vérifier l'ouverture directe de l'espace artiste sans demande de mot de passe.
7. **Artiste** : dans l'espace artiste, renseigner les informations de profil proposées (description, domaine, activités, liens, etc. selon ce qui est actuellement exposé) et enregistrer.
8. **Système** : vérifier que l'enregistrement aboutit sans erreur silencieuse (message de confirmation visible, aucune perte de données en rechargeant la page).
9. **Gestionnaire** : dans le back-office, retrouver l'artiste et confirmer que les données saisies à l'étape 7 sont bien remontées côté administration.
10. **Vérification croisée** : reproduire le même parcours une seconde fois avec un scénario de refus (l'étape 4 est alors remplacée par un rejet) pour vérifier que l'artiste reçoit bien un e-mail de refus et qu'aucun magic link ne lui est envoyé dans ce cas.

Ce parcours complet doit être rejoué au moins une fois sans aucune anomalie avant de considérer la V1 comme livrable.

## Cas de test V1

### V1-01 : Navigation vers les parcours artiste

Étapes : ouvrir l'accueil, puis visiter `Devenir artiste` et `Espace artiste`.

Résultat attendu : les pages s'affichent sans erreur et permettent d'accéder clairement aux démarches artiste.

Statut :  
Commentaires :  

### V1-02 : Demande de référencement (scénario nominal)

Étapes : depuis `Devenir artiste`, soumettre une nouvelle demande complète et valide avec une adresse e-mail de test réelle.

Résultat attendu : le formulaire se complète sans erreur bloquante, les étapes sont compréhensibles et un message de confirmation s'affiche après envoi (à l'écran, en remplacement du formulaire).

Statut :  
Commentaires :  

### V1-03 : Demande de référencement avec champs obligatoires manquants

Étapes : soumettre le formulaire de demande en laissant vide au moins un champ obligatoire (par exemple nom complet, e-mail ou domaine principal), puis tenter de valider.

Résultat attendu : la soumission est bloquée, un message d'erreur explicite et localisé s'affiche près du ou des champs concernés, et aucune demande n'est créée côté back-office.

Statut :  
Commentaires :  

### V1-04 : Demande de référencement avec données invalides

Étapes : tester successivement une adresse e-mail malformée (par exemple `test@@test`), un numéro de téléphone invalide, puis si applicable un document dans un format non accepté ou dépassant la taille maximale.

Résultat attendu : chaque cas est rejeté avec un message d'erreur clair et spécifique au type d'erreur (format, taille). Aucune donnée invalide n'est enregistrée. Le message ne doit pas être une erreur technique brute (par exemple une trace d'erreur PHP) exposée à l'utilisateur.

Statut :  
Commentaires :  

### V1-05 : Soumission concurrente ou double clic

Étapes : sur la dernière étape du formulaire de demande, cliquer plusieurs fois rapidement sur le bouton de soumission, ou soumettre deux fois via le retour arrière du navigateur.

Résultat attendu : une seule demande est créée côté back-office, sans doublon ni erreur visible pour l'artiste.

Statut :  
Commentaires :  

### V1-06 : Validation gestionnaire, approbation

Étapes : dans le back-office, retrouver la demande nominale (V1-02), l'ouvrir, vérifier l'exhaustivité des données affichées par rapport à la saisie initiale, puis l'approuver.

Résultat attendu : le gestionnaire visualise toutes les données soumises sans troncature ni champ manquant. L'action d'approbation est prise en compte immédiatement et la demande change de statut.

Statut :  
Commentaires :  

### V1-07 : Validation gestionnaire, refus

Étapes : soumettre une seconde demande de test, puis la refuser depuis le back-office.

Résultat attendu : le gestionnaire peut refuser la demande. Le statut de la demande est mis à jour et un e-mail de refus est envoyé à l'artiste (voir V1-09), sans génération de magic link.

Statut :  
Commentaires :  

### V1-08 : Tentative d'action gestionnaire sur une demande déjà traitée

Étapes : une fois une demande déjà approuvée ou refusée (V1-06 ou V1-07), tenter de l'approuver ou de la refuser à nouveau depuis le back-office.

Résultat attendu : le système empêche un double traitement incohérent, par exemple en masquant les actions devenues invalides ou en affichant un message clair, sans générer de doublon d'e-mail ni d'erreur technique visible.

Statut :  
Commentaires :  

### V1-09 : Notifications e-mail suite à une décision gestionnaire

Étapes : vérifier la boîte e-mail de test associée à la demande approuvée (V1-06) et celle associée à la demande refusée (V1-07).

Résultat attendu : l'artiste approuvé reçoit un e-mail l'informant de la décision, ainsi que le magic link permettant de finaliser son profil. L'artiste refusé reçoit un e-mail de refus, sans magic link. Le contenu des e-mails est compréhensible et sans données techniques inutiles.

Statut :  
Commentaires :  

### V1-10 : Accès à l'espace artiste via magic link

Étapes : depuis l'e-mail reçu suite à l'approbation, cliquer sur le magic link.

Résultat attendu : l'artiste accède directement à son espace de finalisation de profil, sans authentification classique (aucun mot de passe demandé), et les informations déjà connues (nom, e-mail) sont correctement pré-remplies ou affichées en lecture seule.

Statut :  
Commentaires :  

### V1-11 : Magic link invalide ou déjà utilisé

Étapes : consommer un magic link une première fois (connexion réussie), puis réutiliser ce même lien une seconde fois ; séparément, modifier légèrement l'URL d'un lien reçu pour la rendre invalide, puis tenter d'y accéder.

Résultat attendu : les liens de connexion sont désormais à usage unique — réutiliser un lien déjà consommé, tout comme altérer sa signature, affiche un message dédié (« Lien de connexion invalide ») directement sur la page `Espace artistes`, avec un accès immédiat au formulaire de redemande de lien juste en dessous. Aucune erreur technique brute ni accès non autorisé à un autre profil.

Statut :  
Commentaires :  

### V1-12 : Saisie initiale des données artiste (scénario nominal)

Étapes : depuis l'espace artiste ouvert par magic link, renseigner l'ensemble des informations actuellement proposées (description, domaine, activités, liens, etc.), puis enregistrer.

Résultat attendu : tous les champs proposés sont accessibles et utilisables. L'enregistrement aboutit avec un message de confirmation visible. Recharger la page confirme que les données ont bien été persistées, sans perte silencieuse.

Statut :  
Commentaires :  

### V1-13 : Saisie avec champ obligatoire manquant

Étapes : dans l'espace artiste, tenter d'enregistrer le profil en laissant vide le champ obligatoire connu (description de l'activité).

Résultat attendu : l'enregistrement est bloqué avec un message d'erreur explicite indiquant le champ manquant, sans perte des autres données déjà saisies dans le formulaire.

Statut :  
Commentaires :  

### V1-14 : Cohérence des données entre artiste et back-office

Étapes : après l'enregistrement du profil en V1-12, retourner dans le back-office et rouvrir la fiche de l'artiste correspondant.

Résultat attendu : toutes les données saisies par l'artiste sont visibles et identiques côté gestionnaire, sans décalage ni champ manquant.

Statut :  
Commentaires :  

### V1-15 : Régénération d'un lien de connexion

Étapes : depuis `Espace artiste`, demander un nouveau lien avec l'adresse e-mail d'un artiste déjà enregistré, puis avec une adresse e-mail qui n'existe pas dans le système.

Résultat attendu : pour une adresse connue, un nouveau lien est reçu par e-mail. Pour une adresse inconnue, le système ne doit ni confirmer ni infirmer explicitement l'existence du compte (message neutre), afin d'éviter une fuite d'information, tout en restant compréhensible pour l'utilisateur.

Statut :  
Commentaires :  

### V1-16 : Demande de modification de profil

Étapes : depuis `Espace artiste`, demander un lien de modification pour un profil déjà finalisé, puis modifier une information et vérifier son traitement dans le back-office.

Résultat attendu : le parcours de demande de modification est accessible. La demande de modification est visible pour le gestionnaire, distincte de la demande de référencement initiale, et son approbation met à jour les données réelles de l'artiste.

Statut :  
Commentaires :  

### V1-17 : Sécurité de publication et confidentialité

Étapes : créer ou identifier une demande non encore validée par un gestionnaire (ou un profil en attente de finalisation), puis vérifier qu'aucune de ces données n'est accessible publiquement ou via une URL devinée.

Résultat attendu : aucune fiche ou donnée non approuvée n'est accessible publiquement avant validation explicite d'un gestionnaire. Les champs non destinés à la publication (date de naissance, téléphone) ne doivent jamais apparaître côté public.

Statut :  
Commentaires :  

### V1-18 : Comportement en cas d'erreur serveur ou de coupure

Étapes : si possible, simuler une perte de connexion ou rafraîchir la page en plein milieu d'une étape du formulaire (artiste ou gestionnaire), puis reprendre la navigation.

Résultat attendu : aucune donnée n'est corrompue de manière silencieuse, et l'utilisateur reçoit une indication claire s'il doit recommencer une étape, plutôt qu'un écran blanc ou une erreur technique illisible.

Statut :  
Commentaires :  

### V1-19 : Upload de documents — validation et confidentialité

Étapes : depuis `Devenir artiste`, à l'étape « Documents », tester successivement : un fichier image et un PDF valides (ex. fichiers `test_image.jpg` / `test_document.pdf`), un fichier dépassant 5 Mo (`oversized-document.pdf`), puis un fichier d'un type non autorisé (`disallowed-file.exe`). Une fois une demande avec document valide approuvée par le gestionnaire, tenter de retrouver et d'ouvrir le lien direct du document déposé (par ex. via les outils réseau du navigateur) depuis une fenêtre de navigation privée non connectée.

Résultat attendu : dès le dépôt (glisser-déposer ou parcourir), un fichier trop volumineux ou d'un format non autorisé affiche immédiatement un message d'erreur sous la zone de dépôt, avant même la soumission du formulaire. À la soumission, les fichiers valides sont acceptés. Le fichier trop volumineux est rejeté avec un message clair (« 5 Mo maximum »), sans erreur technique. Le fichier de type non autorisé est rejeté avec un message clair indiquant les formats acceptés (PDF, JPG, PNG). Le document déposé par l'artiste n'est accessible par aucun lien direct sans authentification — il ne doit jamais être exposé publiquement, avant ou après validation par le gestionnaire.

Statut :  
Commentaires :  

## Points d'attention

- Toute erreur bloquante sur le parcours complet (demande, validation, notification, magic link, saisie des données) doit être considérée comme prioritaire.
- Les cas d'erreur (V1-03, V1-04, V1-05, V1-08, V1-11, V1-13, V1-15, V1-18, V1-19) sont aussi importants que les cas nominaux. Un mécanisme de validation manquant ou un message d'erreur illisible constitue une anomalie à documenter, même si le cas nominal fonctionne.
- Si un e-mail attendu n'est pas reçu, vérifier le dossier courrier indésirable avant de conclure à une anomalie.
- Si un cas n'est pas testable faute de droits, de données ou de fonctionnalité ouverte sur staging, le marquer `N/A` et préciser la raison.
- Toujours tester avec des sessions séparées pour chaque rôle. Se connecter au back-office et à l'espace artiste dans le même onglet, ou avec le même navigateur, peut provoquer des déconnexions inattendues ou faire apparaître de fausses anomalies qui n'existent pas réellement dans l'application.

Fonctionnalités volontairement hors périmètre de cette recette V1 :

- l'annuaire public, la recherche, les filtres et le tri.
- le partage sur les réseaux sociaux.
- le recadrage et la gestion avancée de l'image artiste.
- la recette de consultation détaillée des fiches publiques.
- les rappels semestriels automatiques.

## Validation finale

La livraison V1 peut être considérée comme confirmée si les conditions suivantes sont réunies.

- Le parcours complet artiste et gestionnaire (section dédiée en début de document) a été rejoué au moins une fois intégralement sans anomalie bloquante.
- Tous les cas nominaux (V1-01, V1-02, V1-06, V1-07, V1-09, V1-10, V1-12, V1-14, V1-15, V1-16, V1-17) sont en `OK`.
- Les cas d'erreur (V1-03, V1-04, V1-05, V1-08, V1-11, V1-13, V1-18, V1-19) montrent un comportement maîtrisé, même si le message exact peut être ajusté ultérieurement.
- Les anomalies restantes sont mineures, documentées et acceptées explicitement par le client.

## Export Pandoc

Le fichier est compatible avec Pandoc. Exemples de conversion :

```bash
cd docs/recette
pandoc RECETTE_CLIENT_V1.md -o RECETTE_CLIENT_V1.docx
pandoc RECETTE_CLIENT_V1.md -o RECETTE_CLIENT_V1.pdf
# ou, sans dépendance à une installation Pandoc locale :
python md_to_pdf.py RECETTE_CLIENT_V1.md RECETTE_CLIENT_V1.pdf
```

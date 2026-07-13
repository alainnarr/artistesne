1. Personas
1.1 Artiste

Artiste neuchâtelois souhaitant être visible dans un répertoire officiel afin de promouvoir son activité, ses collaborations et ses espaces personnels.

1.2 Internaute

Citoyen, organisme, média ou institution souhaitant mandater ou découvrir des artistes neuchâtelois via une recherche exploratoire ou ciblée.

1.3 Gestionnaire SCNE

Gestionnaire chargé de modérer les inscriptions, garantir la qualité des données et administrer la plateforme.

2. Backlog
EPIC 1 — Consultation et découverte des artistes
Feature 1.1 — Navigation générale du site
Description

Permettre à l’utilisateur de naviguer facilement dans la plateforme.

User Stories
En tant qu’utilisateur, je veux accéder facilement aux principales sections du site afin de naviguer intuitivement.
En tant qu’utilisateur, je veux revenir à l’accueil en cliquant sur le logo afin de simplifier mon parcours.
Critères d’acceptation
Le header est présent sur toutes les pages.
Le header contient :
le logo ou titre du site ;
le lien « À propos » ;
le lien « Espace artiste » ;
le lien de la page consultée est actif.
Le logo est cliquable et redirige vers la page d’accueil.
Le footer est présent sur toutes les pages.
Le footer contient :
le logo ne.ch en noir et blanc ;
le copyright ;
le lien vers la politique des cookies ;
le lien vers la politique de confidentialité ;
le lien vers la page de contact ;
les liens vers Facebook et Instagram de l'État.
Une bannière indiquant la politique des cookies est présente et s'affiche sur le site si le visiteur ne l'a pas déjà masquée.
Feature 1.2 — Page d’accueil
Description

Présenter le concept de la plateforme et permettre une recherche exploratoire.

User Stories
En tant qu’internaute, je veux découvrir des artistes directement depuis la page d’accueil.
En tant qu’internaute, je veux consulter une sélection d’artistes afin d’explorer la plateforme.
En tant qu’internaute, je veux accéder facilement à l’espace artiste.
Critères d’acceptation
La page d’accueil contient :
un titre avec le dernier élément en couleur et en italique;
un texte introductif ;
un champ de recherche ;
un bouton d’ouverture des filtres ;
un titre pour la liste des cards ;
une liste de cards artistes ;
un tri aléatoire par défaut ;
le nombre d’artistes référencés ;
un bouton « Afficher plus ».
Un bloc promotionnel « Espace artiste » est affiché en bas de page.
Le bloc « Espace artiste » peut contenir :
titre ;
texte ;
bouton CTA ;
la mention "Rejoignez les X artistes" ou X est un nombre dynamique correspondant aux nombres d'artistes référencés.
Le bouton « Afficher plus » charge des cards supplémentaires sans rechargement complet de page.
La version mobile adapte correctement les composants.
Par défaut 9 cards sont affichées dans la liste, puis 12 à partir de 1920 pixels de résolution.
Feature 1.3 — Recherche textuelle
Description

Permettre la recherche d’artistes via un moteur de recherche textuel.

User Stories
En tant qu’internaute, je veux rechercher un artiste par nom ou discipline afin de trouver rapidement une personne spécifique.
En tant qu’internaute, je veux bénéficier de suggestions automatiques afin de faciliter ma recherche.
En tant qu’internaute, je veux pouvoir effectuer une recherche vide afin d’afficher tous les artistes.
Critères d’acceptation
Le champ de recherche est présent sur :
la page d’accueil ;
la page des résultats de recherche.
Le label "Rechercher dans l'annuaire" accompagne le champ de recherche.
Une icône de loupe est affichée dans le champ.
Le placeholder du champ de recherche disparaît lorsque l'utilisateur entre dans le champ.
Lorsque l'utilisateur entre dans le champ, l'icône de la loupe devient une croix qui permet de vider le champ.
La liste de suggestions apparaît après 3 caractères saisis dans le champ.
La liste des suggestions contient 10 entrées avec un scroll vertical si nécessaire en fonction de la résolution d'écran.
La sélection d’une suggestion lance automatiquement la recherche.
La liste de suggestions contient le lien "Voir les résultats (N)", où N représente le nombre de résultats.
L’appui sur Enter lance la recherche.
Une recherche vide affiche tous les artistes.
Si aucun résultat n’est trouvé, un message approprié est affiché.
La recherche index tous les champs d'un profil d'artiste, hormis le champ "Description" et les liens.
Le moteur de recherche peut s'appuyer sur des synonymes paramétrés par un administrateur.
Feature 1.4 — Résultats de recherche
Description

Afficher les artistes correspondant aux critères de recherche.

User Stories
En tant qu’internaute, je veux consulter une liste structurée de résultats afin de comparer plusieurs artistes.
En tant qu’internaute, je veux voir le nombre de résultats obtenus.
Critères d’acceptation
La page de résultats contient :
le champ de recherche ;
le module de recherche est identique à celui de la page d'accueil, mais ne contient pas le label "Recherchez dans l'annuaire";
le bouton filtres ;
le titre de la recherche ;
le nombre de résultats ;
les options de tri ;
les cards artistes.
Les résultats s’actualisent dynamiquement.
Le bouton « Afficher plus » charge davantage de résultats.
Les résultats sont affichés sous forme de cards.
Le label indiquant le nombre de résultats de recherche et de filtres sélectionnés s'accorde en nombre.
Une pastille contenant le nombre de filtre sélectionnés s'affiche sur le bouton des filtres dès lors qu'un filtre est sélectionné.
Les paramètres de la recherche actuelle sont conservés dans l'URL.
Feature 1.5 — Filtres de recherche
Description

Permettre l’exploration des artistes via différents critères.

User Stories
En tant qu’internaute, je veux filtrer les artistes afin d’affiner mes résultats.
En tant qu’internaute, je veux combiner plusieurs filtres.
En tant qu’internaute, je veux réinitialiser facilement mes filtres.
Critères d’acceptation
Les filtres sont affichés dans une modal.
Les filtres sont organisés dans des accordéons.
Il est possible de filtrer par :
domaine principal ;
activité principale (associée au domaine principal) ;
domaine secondaire ;
activité secondaire ;
régions de résidence ;
la sélection d'une option régionale liée au canton permet de sélectionner une commune du canton de Neuchâtel dans la section "Commune de résidence" ;
la sélection du filtre "Hors canton" permet de sélectionner une commune hors du canton de Neuchâtel inscrite dans le système dans la section "Commune de résidence" - Attention à veiller à la consistance dans le nommage des communes.
Les filtres actifs modifient dynamiquement les résultats.
Le bouton de validation affiche le nombre de résultats obtenus en temps réel.
Les filtres pouvant produire zéro résultat ne sont pas affichés.
Un lien permet de réinitialiser les filtres.
Les filtres sont accessibles sur mobile.
Le footer de la modal des filtres est fixe.
La hauteur de la modal est proportionnelle à la résolution de l'écran et reste fixe lors de la navigation.
Un scroll permet de faire défiler l'entièreté des filtres.
Un badge apparaît à côté du titre de section d'un filtre, indiquant le nombre de filtres sélectionnés dans la section correspondante.
Une icône "vu" apparaît à côté du label d'un élément sélectionné.
Feature 1.6 — Tri des résultats
Description

Permettre à l’utilisateur d’ordonner les résultats.

User Stories
En tant qu’internaute, je veux trier les artistes afin de faciliter ma consultation.
Critères d’acceptation
Tri aléatoire par défaut sur la page d'accueil.
Tri par pertinence par défaut sur la page des résultats de recherche.
Le tri A-Z est disponible.
Le tri Z-A est disponible.
Le tri "Plus récent" est disponible, où "Plus récent" correspond aux artistes le plus récemment inscrits.
Le tri "Aléatoire" est disponible sur la page d'accueil.
Le tri "Pertinence" est disponible sur la page de recherche.
Le tri met immédiatement à jour la liste.
Feature 1.7 — Cards artistes
Description

Afficher les artistes sous une forme compacte et lisible.

User Stories
En tant qu’internaute, je veux voir les informations essentielles d’un artiste en un coup d’œil.
En tant qu’internaute, je veux accéder au profil complet d’un artiste.
Critères d’acceptation
Une card contient dans l’ordre :
nom d'artiste par défaut et si pas renseigné nom complet ;
commune ;
tag domaine principal ;
tag domaine secondaire ;
tags activités principales, au maximum de 4 ;
un CTA "Voir profil" redirigeant vers la page de l'artiste.
Les cards sont responsives.
Feature 1.8 — Profil artiste
Description

Afficher la fiche détaillée d’un artiste.

User Stories
En tant qu’internaute, je veux consulter un profil détaillé afin de découvrir un artiste.
En tant qu’internaute, je veux contacter un artiste.
En tant qu’internaute, je veux partager un profil.
Critères d’acceptation
La page artiste contient :
un lien retour (retour à la page précédente) ;
un bouton de partage vers Facebook, LinkedIn, Pinterest, E-mail, WhatsApp et "Copier le lien" avec le label "Partager mon profil" ;
un bouton mailTo, si l'artiste a choisi d'afficher son contact, avec le label "Me contacter" ;
nom d'artiste si référencé ou nom complet ;
commune ;
domaine principal ;
domaine secondaire ;
activités principales ;
une photo ;
description ;
liens personnels dans la rubrique "Espaces personnels" ;
liens de collaborations dans la rubrique "Collaborations" ;
activités secondaires dans la rubrique "Activités secondaires" ;
mots-clés dans la rubrique "Mots-clés" ;
date de mise à jour.
Si une rubrique ne contient pas d'informations renseignées par l'artiste, celle-ci ne s'affiche pas.
Les liens personnels affichent :
icône relative au lien ;
URL complète sans le protocole.
Le bouton de contact ouvre un mailTo.
Le bouton de partage permet le partage sur réseaux sociaux.
Lors du partage, les éléments repris pour l'affichage sur les réseaux sont : Nom d'artiste ou nom complet, photo de l'artiste, description.
L'URL de profil de l'artiste respecte le format https://artistes.ne.ch/artistes/nom-complet et https://artistes.ne.ch/artistes/nom-artiste. En cas de doublon, un numéro d'incrément est ajouté à la fin de l'adresse. Exemple : https://artistes.ne.ch/artistes/nom-complet-2. 
EPIC 2 — Référencement et gestion du profil artiste
Feature 2.1 — Page Espace artiste
Description

Permettre aux artistes d’initier leurs démarches.

User Stories
En tant qu’artiste, je veux accéder à un espace dédié afin de gérer mon référencement.
En tant qu’artiste, je veux demander un nouveau lien de connexion afin de poursuivre ma démarche.
En tant qu'artiste, je veux pouvoir réactiver mon profil s'il a été désactivé suite à la non-confirmation semestrielle.
Critères d’acceptation
La page contient :
un bloc introductif ;
un bloc « Demande de référencement » ;
un bloc « Régénérer un lien » ;
un bloc « Demande de modification ou de suppression » ;
un bloc « Demande de réactivation » ; 
Chaque bloc peut contenir :
titre ;
texte ;
bouton CTA.
Feature 2.2 — Demande de référencement
Description

Permettre à un artiste d’initier une demande d’inscription.

User Stories
En tant qu’artiste, je veux demander mon référencement afin d’être visible sur la plateforme.
En tant qu’artiste, je veux comprendre les champs obligatoires.
Critères d’acceptation
Un étapier est présent au-dessus du formulaire.
Chaque étape peut être dans l'un ou l'autre des états suivants : actif, terminé, désactivé.
Les étapes ne sont pas cliquables ; l'étapier est informationnel. 
Le formulaire est organisé en trois étapes : identité, activités, documents
Les champs obligatoires sont marqués par un astérisque.
Un captcha est présent dans la dernière étape.
Le formulaire permet la saisie des informations générales.
L'artiste peut choisir d'afficher son nom d'artiste sur la page à la place de son nom complet ; ce choix est définitif.
Un message indiquant l'impact sur le format de l'URL du profil selon le choix d'affichage du nom complet ou du nom d'artiste est affiché. Le message précise également que le choix est structurant et ne peut être modifié.
Le champ téléphone est par défaut paramétré pour un numéro suisse.
La sélection "Hors canton" dans le champ "Résidence" affiche deux champs supplémentaires:, "Commune de résidence" et "Description de mon activité", les deux en texte libre, ainsi qu'un texte explicatif d'accompagnement - Attention à la consistance du champ "Commune de résidence" dans un tel cas.
Le champ "Autre activité principale" affiche un champ libre supplémentaire.
Le champ "Activité principale" est désactivé tant que le champ "Domaine principal" n'a pas été renseigné.
L'artiste peut choisir jusqu'à quatre activités principales.
Le formulaire permet l’ajout de liens multiples.
Le formulaire permet l’upload de documents.
Les listes déroulantes ne dépassent pas du cadre de visualisation.
Le champ « Documents » supporte :
multi-upload ;
un document peut être supprimé une fois téléchargé ;
drag and drop ;
les formats acceptés pour les documents sont PDF, JPG et PNG.
L'affichage de la validité du champ rempli ou de l'erreur éventuelle relative s'affiche à la sortie du champ par l'utilisateur.
Les messages d'erreur sont consistants.
À la validation du formulaire, un message de confirmation s'affiche pour l'utilisateur en place du formulaire.
Les champs obligatoires sont : Nom complet, Date de naissance, Email, Téléphone, Lieu de résidence, Domaine principal, Activité principale, Attestation de l'exactitude des données transmises.
Les champs "Date de naissance" et "Téléphone" ne sont pas affichés publiquement.
Feature 2.3 — Workflow magic link
Description

Permettre la poursuite sécurisée d’une inscription ou d’une modification.

User Stories
En tant qu’artiste, je veux recevoir un magic link afin de poursuivre mon inscription.
En tant qu’artiste, je veux régénérer un lien si nécessaire.
En tant qu’artiste, je veux utiliser un lien sécurisé afin d’éviter un compte classique.
Critères d’acceptation
Après soumission de la demande initiale :
un e-mail contenant un magic link est envoyé si la demande est validée par un administrateur du SCNE;
un e-mail de refus de la demande si la demande a été rejetée par un administrateur du SCNE.
Le clic sur le lien ouvre le formulaire de finalisation.
Un artiste peut demander un nouveau lien.
un artiste doit se rendre sur la page "Espace artiste" et cliquer sur le lien permettant de regénérer un lien ;
l'artiste est redirigé vers une page lui demandant de renseigner son adresse e-mail.
Un artiste peut demander un lien pour modifier ou supprimer son profil.
un artiste doit se rendre sur la page "Espace artiste" et cliquer sur le lien permettant de regénérer un lien ;
l'artiste est redirigé vers une page lui demandant de renseigner son adresse e-mail.
La durée de validité du magic link est d'une semaine ; si le lien est échu, l'utilisateur est redirigé vers une page l'informant que le lien n'est plus valide et qu'il est nécessaire d'en redemander un.
Feature 2.4 — Finalisation de l’inscription
Description

Permettre à l’artiste de compléter son profil.

User Stories
En tant qu’artiste, je veux compléter mon profil détaillé afin d’être référencé.
En tant qu’artiste, je veux ajouter des informations complémentaires afin de valoriser mon activité.
Critères d’acceptation
Un étapier est présent au-dessus du formulaire.
Chaque étape peut être dans l'un ou l'autre des états : actif, terminé, désactivé.
Les étapes ne sont pas cliquables ; l'étapier informationnel. 
Le formulaire est organisé en deux étapes : activités, liens
Le formulaire permet de renseigner :
photo ;
description ;
domaines ;
activités ;
liens ;
mots-clés ;
collaborations.
Les champs "e-mail" et "nom complet" sont affichés, mais désactivés, afin de confirmer à l'utilisateur qu'il se trouve sur le bon profil.
L'artiste ne peut plus choisir d'afficher son nom d'artiste sur la page à la place de son nom complet.
L’artiste peut ajouter des liens personnels vers Instagram, YouTube, Vimeo, Bandcamp, SoundCloud, Facebook, LinkedIn, TikTok et un site personnel.
L'artiste peut ajouter plusieurs liens vers ses collaborations.
La photo de l'artiste est passée en noire et blanc.
Un éditeur permettant le cadrage d'image est présent et disponible pour l'utilisateur.
L'image ne doit pas dépasser 5 Mo.
L'image doit être au format JPG ou PNG.
Il n'est possible d'uploader qu'une seule photo.
La photo doit être dans un format minimum de 400x500 pixels.
Le formulaire est responsive.
Des icônes sont associées de façon dynamique aux champs des réseaux sociaux.
Un message de confirmation est affiché après soumission.
Les champs obligatoires sont : Description de mon activité
L'URL de profil de l'artiste respecte le format https://artistes.ne.ch/artistes/nom-complet et https://artistes.ne.ch/artistes/nom-artiste. En cas de doublon, un numéro d'incrément est ajouté à la fin de l'adresse. Exemple : https://artistes.ne.ch/artistes/nom-complet-2. 
Feature 2.5 — Modification du profil
Description

Permettre la mise à jour d’un profil existant.

User Stories
En tant qu’artiste, je veux modifier facilement mon profil afin qu’il reste à jour.
En tant qu’artiste, je veux demander des modifications sans devoir créer de compte.
Critères d’acceptation
Une demande de modification peut être initiée depuis l’espace artiste.
Un magic link de modification est envoyé par e-mail.
Tant qu’une modification n’est pas validée, elle n’est pas visible publiquement.
Un message de confirmation est envoyé après soumission.
Le formulaire est identique à "Finalisation de l'inscription".
Un artiste ne peut pas modifier l'affichage du nom complet ou de son nom d'artiste.
EPIC 3 — Modération et administration SCNE
Feature 3.1 — Authentification gestionnaire
Description

Permettre l’accès sécurisé à l’interface de gestion.

User Stories
En tant que gestionnaire, je veux me connecter via ADFS afin d’accéder à la console de gestion.
Critères d’acceptation
L’authentification utilise ADFS.
Les utilisateurs non autorisés ne peuvent pas accéder à la console.
Feature 3.2 — Tableau de bord gestionnaire
Description

Afficher les informations principales de gestion.

User Stories
En tant que gestionnaire, je veux voir les indicateurs principaux afin de suivre l’activité de la plateforme.
Critères d’acceptation
Le tableau de bord affiche :
nombre total d’artistes ;
demandes de référencement en attente ;
demandes de modification en attente.
Feature 3.3 — Gestion des artistes
Description

Permettre l’administration des profils.

User Stories
En tant que gestionnaire, je veux consulter tous les artistes.
En tant que gestionnaire, je veux afficher un artiste.
En tant que gestionnaire, je veux masquer un artiste.
En tant que gestionnaire, je veux supprimer un artiste.
En tant que gestionnaire, je veux consulter et éditer les détails d’un artiste.
En tant que gestionnaire, je veux réactiver le profil d’un artiste désactivé
En tant que gestionnaire, je veux désactiver le profil d’un artiste
Critères d’acceptation
Une liste des artistes est disponible.
Chaque artiste affiche son statut : actif, inactif, en attente de validation.
Un gestionnaire peut consulter les détails d’un artiste et les éditer si besoin.
Un artiste peut être affiché par un gestionnaire dans la liste publique.
Un artiste peut être masqué par un gestionnaire dans la liste publique.
Un artiste peut être supprimé par un gestionnaire.
Un artiste peut être désactivé par un gestionnaire
Un artiste peut être réactivé par un gestionnaire
Feature 3.4 — Gestion des taxonomies
Description

Permettre l’administration des termes de taxonomies.

User Stories
En tant que gestionnaire, je veux gérer les termes de taxonomies.
Critères d’acceptation
Une liste des taxonomies est disponible.
Un terme de taxonomie peut être créé.
Un terme de taxonomie peut être édité.
Un terme de taxonomie peut être supprimé.
Feature 3.5 — Gestion des demandes
Description

Permettre le traitement des inscriptions et des modifications.

User Stories
En tant que gestionnaire, je veux valider ou refuser une demande.
En tant que gestionnaire, je veux demander des ajustements.
En tant que gestionnaire, je veux pouvoir contacter directement un artiste depuis l'interface de gestion.
Critères d’acceptation
Une vue liste les demandes de référencement.
Une vue liste les demandes de modification.
Un gestionnaire peut :
approuver ;
rejeter ;
envoyer une demande d'ajustement.
Un gestionnaire peut consulter les détails d'une demande.
Un gestionnaire peut directement contacter un artiste via l'interface.
Un e-mail est envoyé à l’artiste après traitement.

Aucun profil n'est accessible publiquement avant validation d'un gestionnaire.

Feature 3.6 — Rappel semestriel
Description

Envoyer un e-mail de rappel semestriel de façon automatique aux artistes.

User Stories
En tant que système, je veux envoyer un message automatiquement aux artistes tous les six mois afin de leur rappeler de mettre à jour leur profil avec un délai de quatre semaines..
Critères d’acceptation
Un e-mail de rappel est envoyé tous les six mois aux artistes.
Un artiste peut confirmer que son profil est à jour et y apporter des modifications si nécessaire.
Le profil d'un artiste qui n'aurait pas confirmé que ses informations sont à jour après quatre semaines est automatiquement désactivé et n'est plus affiché publiquement.
Un message de rappel est envoyé aux artistes qui n'auraient pas effectué les démarches nécessaires J-7 avant le délai.
Le flux suivant est respecté :
Tous les six mois, un e-mail automatique est envoyé aux artistes leur indiquant qu'il est nécessaire de confirmer que leur profil est encore à jour. Le message contient deux options cliquables possibles : « Oui, mon profil est à jour» et « Non, mon profil n'est plus à jour » ;
En cliquant sur « Oui, mon profil est à jour», l'artiste est authentifié et sa confirmation validée. Un e-mail lui indiquant que sa démarche a été prise en compte lui est transmis ;
En cliquant sur « Non, mon profil n'est plus à jour», l'artiste est authentifié et arrive sur le formulaire d'édition de son profil. Après édition, un e-mail lui indiquant que sa démarche a été prise en compte lui est transmis ;
Si l'artiste ne fait aucune action, J-7 avant la désactivation, un e-mail de rappel lui est envoyé avec les mêmes options que dans le message initial ;
À la fin du délai, en cas d'inaction de la part de l'artiste, son profil est désactivé et un message lui est envoyé afin de l'informer de la situation et l'invite à réactiver son profil ;
L'artiste peut réactiver son profil via l'espace artiste.
Tant que l'artiste n'a pas réactivé son profil, celui-ci n'apparaît plus pour le public.
Feature 3.7 — Export des données
Description

Permettre l’exploitation institutionnelle des données.

User Stories
En tant que gestionnaire, je veux exporter les listes afin d’utiliser les données dans d’autres outils.
Critères d’acceptation
Les listes peuvent être exportées :
au format CSV ;
au format Excel.
Feature 3.8 — Gestion des synonymes de la recherche
Description

Permettre la gestion des synonymes de recherche pour les termes de taxonomies..

User Stories
En tant que gestionnaire, je veux pouvoir administrer les synonymes utilisés par le moteur de recherche pour les termes de taxonomies.
Critères d’acceptation
Un synonyme peut être ajouté.
Un synonyme peut être édité.
Un synonyme peut être supprimé.
Feature 3.9 — Statistiques des visites
Description

Permettre la consultation des statistiques des visites.

User Stories
En tant que gestionnaire, je veux voir les statistiques de visites du site.
Critères d’acceptation
Les statistiques de visite du site sont disponibles via l'outil Matomo (stats.ne.ch).
EPIC 4 — Notifications et communication
Feature 4.1 — Notifications pour le gestionnaire
Description

Informer les gestionnaires des actions importantes.

User Stories
En tant que gestionnaire, je veux être informé des nouvelles demandes par e-mail et via le tableau de bord.
Critères d’acceptation
Un e-mail est envoyé au gestionnaire :
pour l'informer d'une nouvelle demande de référencement ;
pour l'informer d'une nouvelle demande de modification.
Une notification s'affiche dans le tableau de bord pour indiquer au gestionnaire :
qu'une nouvelle demande de référencement est été soumise ;
qu'une nouvelle demande de modification est été soumise.
Feature 4.2 — Notifications pour l'artiste
Description

Informer les artistes des actions importantes.

User Stories
En tant qu’artiste, je veux recevoir des confirmations par e-mail.
Critères d’acceptation
Un e-mail est envoyé à l'artiste :
pour confirmer sa demande de référencement en cas d'acceptation ;
pour lui transmettre le magic link après validation de sa demande de référencement ;
pour lui indiquer que sa demande de référencement a été refusée en cas de refus ;
pour lui indiquer qu'il est nécessaire de confirmer que son profil est à jour ou d'entreprendre les démarches nécessaires pour le faire ;
pour lui indiquer que son profil a automatiquement été désactivé si l'artiste n'a pas confirmé sa validité après le rappel semestriel ;
pour lui indiquer que sa demande de modification a bien été réceptionnée ;
pour lui indiquer que sa demande de modification a été acceptée et publiée ;
pour lui indiquer que son profil a été réactivé après désactivation.
3. Éléments nécessitant clarification
Fonctionnalités ou règles encore ambiguës

RAS

4. MVP 

MVP : produit minimum viable

Les fonctionnalités présentes dans la section "Must have" sont celles qui doivent impérativement être présentes lors de la mise en production initiale de la plateforme.

MUST HAVE
Consultation artistes
Recherche textuelle avec suggestions
Filtres principaux
Filtres dynamiques avancés
Page profil artiste
Workflow référencement via magic link
Validation gestionnaire
Partage réseaux sociaux
Système de recadrage d'image
Rappel automatique semestriel
SHOULD HAVE
Tableau de bord gestionnaire
Système de demande d'ajustement
Export CSV/XLSX
COULD HAVE
Analytics avancés
Possibilité de justification des refus de demande.

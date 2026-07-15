<x-layouts.public title="Protection des données et transparence">
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Protection des données et"
            accent="transparence"
        >
            <x-slot:lead-slot>
                <p>
                    La République et Canton de Neuchâtel protège vos données personnelles conformément à la
                    Convention intercantonale relative à la protection des données et à la transparence dans
                    les cantons du Jura et de Neuchâtel (CPDT-JUNE). Cette page explique quelles informations
                    sont collectées lors de l'utilisation du site, à quelles fins et comment exercer vos droits.
                </p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Corps : bloc paper sur fond cream --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper p-6 sm:p-12 lg:p-16">
            <div class="flex flex-col gap-10">

                {{-- Intro --}}
                <section class="flex flex-col gap-6">
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            La République et Canton de Neuchâtel attache une grande importance à la protection de
                            vos données personnelles. Le traitement de ces données est régi par la Convention
                            intercantonale relative à la protection des données et à la transparence dans les
                            cantons du Jura et de Neuchâtel (CPDT-JUNE), du 8 et 9 mai 2012.
                        </p>
                    </div>

                    {{-- Push / CTA dark --}}
                    <div class="flex items-center justify-between gap-8 bg-brand p-10 sm:p-14">
                        <p class="text-lg leading-relaxed text-brand-cream">
                            Plus d'information sur la transparence et la protection des données sur le site du
                            Préposé à la protection des données et à la transparence Jura-Neuchâtel.
                        </p>
                        <a
                            href="https://www.ppdt-june.ch/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="shrink-0"
                        >
                            <x-ds.btn variant="secondary-light" size="md">
                                Visiter le site
                            </x-ds.btn>
                        </a>
                    </div>
                </section>

                {{-- Données collectées lors de votre visite --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Données collectées lors de votre visite
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Lorsque vous visitez notre site Internet, les accès sont enregistrés dans un fichier
                            de protocole par les serveurs du service informatique de la République et Canton de
                            Neuchâtel, qui est conservé pendant une période d'un an. Il enregistre ainsi les
                            données suivantes :
                        </p>
                        <ul class="flex flex-col gap-4">
                            @foreach ([
                                "l'adresse IP de l'ordinateur demandeur,",
                                "la date et l'heure de l'accès,",
                                "le nom et l'URL des données consultées,",
                                "le site Internet à partir duquel il a été accédé à notre domaine,",
                                "le système d'exploitation de votre ordinateur et du navigateur que vous utilisez,",
                                "le pays d'où l'accès à notre site Internet a lieu et le nom de votre fournisseur d'accès à Internet,",
                                "le temps que vous passez sur le site,",
                                "le temps passé sur chaque page visitée,",
                                "ce que vous cliquez dans chaque page visitée,",
                                "les mots clés recherchés sur le site,",
                                "le site de destination quand vous quittez le site.",
                            ] as $item)
                                <li class="flex items-start gap-4">
                                    <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p>
                            La récolte et le traitement de ces données permettent l'utilisation du site Internet
                            (mise en place de la liaison), de garantir durablement la sécurité et la stabilité
                            du système et d'optimiser l'offre Internet. Les données sont en outre utilisées à
                            des fins statistiques. Elles ne sont pas reliées à l'identité de l'utilisateur.
                        </p>
                        <p>
                            Ce n'est qu'en cas d'attaque sur l'infrastructure du réseau ou de suspicion d'une
                            autre utilisation illicite ou abusive du site Internet que l'adresse IP est analysée
                            à des fins de clarification et de défense et, le cas échéant, utilisée dans le cadre
                            de procédures civile et pénale.
                        </p>
                    </div>
                </section>

                {{-- Données collectées dans le cadre de l'inscription --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Données collectées dans le cadre de l'inscription au répertoire des artistes
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            L'inscription au répertoire est réservée aux artistes professionnels. Les données
                            collectées lors de l'inscription sont utilisées afin d'évaluer l'éligibilité de la
                            demande, de gérer le compte utilisateur et d'assurer la publication du profil dans
                            le répertoire.
                        </p>
                        <p>
                            Les informations fournies dans le cadre de l'inscription sont examinées par le
                            Service de la culture de l'État de Neuchâtel (SCNE), qui vérifie la conformité du
                            dossier avant la mise en ligne du profil.
                        </p>
                        <p>
                            Une fois le profil validé, les informations suivantes sont susceptibles d'être
                            publiées et consultables par toute personne visitant le site, y compris sans
                            authentification :
                        </p>
                        <ul class="flex flex-col gap-4">
                            @foreach ([
                                "le nom complet ou, le cas échéant, le nom d'artiste ;",
                                "l'adresse électronique, lorsque l'utilisateur choisit de la rendre visible ;",
                                "le lieu de résidence ;",
                                "le domaine principal et l'activité principale ;",
                                "le ou les domaines secondaires et activités secondaires ;",
                                "les mots-clés décrivant l'activité artistique ;",
                                "la photographie de profil ;",
                                "la description du profil de l'artiste ;",
                                "les liens vers les espaces de présence en ligne (site internet personnel, Instagram, YouTube, Vimeo, Bandcamp, SoundCloud, Facebook, LinkedIn, TikTok, etc.) ;",
                                "les liens vers d'autres collaborations ou projets.",
                            ] as $item)
                                <li class="flex items-start gap-4">
                                    <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p>
                            Ces informations sont publiées dans le seul but de permettre l'identification, la
                            présentation et la mise en valeur des artistes professionnels inscrits dans le
                            répertoire.
                        </p>
                        <p>
                            Les données sont conservées aussi longtemps que le profil demeure actif. L'utilisateur
                            peut à tout moment demander la modification ou la suppression de son profil. En cas de
                            suppression du compte, les données sont supprimées ou anonymisées dans un délai
                            raisonnable, sous réserve des obligations légales de conservation applicables.
                        </p>
                        <p>
                            Les personnes concernées disposent des droits prévus par la législation applicable en
                            matière de protection des données, notamment le droit d'accès, de rectification et,
                            dans les limites prévues par la loi, de suppression des données les concernant.
                        </p>
                        <p>
                            <strong class="font-semibold">Attention</strong> : les informations figurant dans votre
                            profil validé seront publiées dans un répertoire accessible au public et pourront être
                            consultées sans authentification. Veillez à ne fournir que les informations que vous
                            acceptez de rendre publiques.
                        </p>
                    </div>
                </section>

                {{-- Statistiques d'utilisation --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Statistiques d'utilisation
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Afin de pouvoir établir des statistiques d'utilisation du site, l'outil MATOMO est
                            utilisé. Il est hébergé en Suisse sur les infrastructures techniques du service
                            informatique de l'Entité neuchâteloise.
                        </p>
                        <p>
                            Les données recueillies sont anonymisées et nous n'analysons pas les parcours
                            individuels. Nous ne partageons pas les informations recueillies avec d'autres
                            organisations à des fins commerciales, et ne les transmettons pas à d'autres sites.
                        </p>
                    </div>
                </section>

                {{-- Données collectées dans les formulaires de contact --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Données collectées dans le cadre des formulaires de contact
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Les données collectées par l'intermédiaire des formulaires sont utilisées uniquement
                            pour traiter votre demande. Seules les données nécessaires sont demandées.
                        </p>
                        <p>
                            Selon la nature de la demande, certaines informations supplémentaires peuvent être
                            requises et vous être demandées par un autre canal de communication.
                        </p>
                        <p>
                            Les données sont conservées pendant la durée nécessaire au traitement de la demande,
                            puis archivées ou supprimées conformément aux règles en vigueur.
                        </p>
                        <p>
                            Vous pouvez demander l'accès, la rectification ou la suppression de vos données
                            personnelles en vous adressant au service concerné ou, si vous ne savez pas lequel
                            contacter, en utilisant
                            <a href="{{ route('contact') }}" wire:navigate class="underline underline-offset-2 hover:no-underline">notre formulaire de contact</a>.
                        </p>
                    </div>
                </section>

                {{-- Responsable du traitement --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Responsable du traitement
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">
                        Chaque service de l'administration cantonale est responsable du traitement des données
                        personnelles qu'il collecte dans le cadre de ses missions. Le rôle de responsable de
                        traitement est la plupart du temps endossé par le chef ou la cheffe de service.
                    </p>
                </section>

            </div>
        </div>
    </x-ds.section>
</x-layouts.public>

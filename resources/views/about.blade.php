<x-layouts.public title="À propos">
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="À propos de"
            accent="Artistes.ne"
        >
            <x-slot:lead-slot>
                <p>
                    Artistes.ne est l'annuaire des artistes professionnel·les actif·ves dans le canton de Neuchâtel.
                    Une vitrine pour les artistes, un outil de découverte et de mise en contact pour qui souhaite
                    explorer la scène artistique neuchâteloise.
                </p>
                <p class="mt-4">
                    L'annuaire s'inscrit dans la volonté du Canton de Neuchâtel de mieux faire connaître, valoriser
                    et soutenir sa scène artistique professionnelle.
                </p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Corps : bloc paper sur fond cream --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper p-6 sm:p-12 lg:p-16">
            <div class="flex flex-col gap-10">
                {{-- À qui s'adresse l'annuaire --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        À qui s'adresse l'annuaire
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">Artistes.ne s'adresse à deux publics.</p>
                    <ul class="ds-list flex flex-col gap-4">
                        <li class="flex items-start gap-4 text-base leading-relaxed text-brand sm:text-lg">
                            <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                            <p>
                                Aux artistes d'abord : professionnel·les actif·ves dans le canton, elles et ils peuvent
                                s'y référencer et disposer d'une présence numérique stable, réutilisable comme carte de
                                visite.
                            </p>
                        </li>
                        <li class="flex items-start gap-4 text-base leading-relaxed text-brand sm:text-lg">
                            <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                            <p>
                                Aux visiteurs ensuite : particuliers, programmateurs·trices, institutions culturelles,
                                communes, médias ou encore entreprises. Toutes et tous peuvent explorer le tissu
                                artistique neuchâtelois, filtrer par domaine ou activité, et entrer directement en
                                contact avec les artistes.
                            </p>
                        </li>
                    </ul>
                </section>

                {{-- Qui peut figurer --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Qui peut figurer dans l'annuaire
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">
                        L'annuaire est ouvert aux artistes professionnel·les dont la pratique relève des domaines
                        soutenus par la loi cantonale sur l'encouragement des activités culturelles (LEAC) : musique,
                        spectacle vivant, arts visuels, cinéma et audiovisuel, littérature et écriture, arts numériques.
                    </p>

                    <p class="text-lg leading-relaxed text-brand">
                        Pour figurer dans l'annuaire, trois conditions doivent être remplies :
                    </p>

                    <ul class="ds-list flex flex-col gap-4">
                        <li class="flex items-start gap-4 text-base leading-relaxed text-brand sm:text-lg">
                            <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                            <div>
                                <p>
                                    <strong class="font-semibold">Professionnalisme</strong>, l'artiste doit satisfaire
                                    au moins deux des trois critères suivants :
                                </p>
                                <ul class="mt-2 list-inside list-[circle] pl-4 text-brand-muted">
                                    <li>formation artistique reconnue dans le domaine concerné,</li>
                                    <li>expérience qui se traduit par une activité rémunérée régulière dans son domaine,</li>
                                    <li>reconnaissance par le champ (prix, résidences, collaborations, etc.).</li>
                                </ul>
                                <p class="mt-3">
                                    Les artistes émergents ne remplissant qu'un seul de ces critères peuvent néanmoins
                                    soumettre une demande s'ils attestent d'au moins une réalisation artistique dans un
                                    cadre professionnel au cours des trois dernières années.
                                </p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4 text-base leading-relaxed text-brand sm:text-lg">
                            <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                            <p>
                                <strong class="font-semibold">Activité récente</strong> : l'artiste doit justifier d'une
                                activité significative au cours des trois dernières années.
                            </p>
                        </li>
                        <li class="flex items-start gap-4 text-base leading-relaxed text-brand sm:text-lg">
                            <span class="mt-2 size-2.5 shrink-0 bg-brand-mint" aria-hidden="true"></span>
                            <p>
                                <strong class="font-semibold">Lien avec le canton</strong> : l'artiste doit être
                                domicilié·e dans le canton de Neuchâtel ou y exercer une activité artistique régulière
                                et significative (fréquence d'activité, partenariats, collaborations).
                            </p>
                        </li>
                    </ul>

                    <div class="flex flex-col gap-3 text-lg leading-relaxed text-brand">
                        <p>
                            Le référencement est volontaire. Les profils sont actualisés tous les 6 mois : chaque
                            artiste reçoit un mail et est invité·e à confirmer ou mettre à jour ses informations. Les
                            profils non confirmés sont désactivés.
                        </p>
                        <p>
                            Les demandes de référencement sont examinées par le service de la culture, qui se réserve
                            le droit de solliciter des informations complémentaires avant de statuer.
                        </p>
                        <p>Pour soumettre une demande ou gérer votre profil, rendez-vous dans l'espace artiste.</p>
                    </div>
                </section>

                {{-- Qui porte le projet --}}
                <section class="flex flex-col gap-4">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Qui porte le projet
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">
                        Artistes.ne est un projet de l'État de Neuchâtel. Il est piloté par le service de la culture du
                        canton de Neuchâtel (SCNE), avec le soutien technique du service informatique de l'Entité
                        neuchâteloise (SIEN).
                    </p>
                </section>

                {{-- Contact --}}
                <section class="flex flex-col gap-4">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">Contact</h2>
                    <p class="text-lg leading-relaxed text-brand">
                        Pour toute question relative à l'annuaire ou à votre référencement, contactez le service de la
                        culture à l'adresse suivante :
                        <a href="mailto:service.culture@ne.ch" class="underline underline-offset-2 hover:no-underline">
                            service.culture@ne.ch
                        </a>.
                    </p>
                </section>
            </div>
        </div>
    </x-ds.section>
</x-layouts.public>

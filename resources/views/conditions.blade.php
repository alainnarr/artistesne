<x-layouts.public title="Conditions d'utilisation">
    {{-- Hero --}}
    <x-ds.section variant="paper">
        <x-ds.hero
            variant="page"
            title="Conditions d'"
            accent="utilisation"
        >
            <x-slot:lead-slot>
                <p>Sur cette page, vous trouverez les conditions d'utilisation du site internet artistes.ne.ch.</p>
            </x-slot:lead-slot>
        </x-ds.hero>
    </x-ds.section>

    {{-- Corps : bloc paper sur fond cream --}}
    <x-ds.section variant="cream">
        <div class="mx-auto w-full max-w-[928px] bg-brand-paper p-6 sm:p-12 lg:p-16">
            <div class="flex flex-col gap-10">

                {{-- Champ d'application et acceptation --}}
                <section id="champ-application" class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Champ d'application et acceptation
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Les présentes conditions s'appliquent à l'utilisation du site internet
                            (<a href="http://www.artistes.ne.ch" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2 hover:no-underline">www.artistes.ne.ch</a>)
                            et de ses formulaires de contact.
                        </p>
                        <p>En accédant à ce site, vous acceptez sans réserve les présentes conditions.</p>
                    </div>
                </section>

                {{-- Clause de non-responsabilité --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Clause de non-responsabilité
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Malgré le soin apporté à l'exactitude des informations publiées, la République et Canton
                            de Neuchâtel ne garantit pas la fidélité, l'actualité, la fiabilité ou l'exhaustivité des
                            contenus. Elle peut modifier, supprimer ou suspendre tout ou partie du site à tout moment
                            et sans préavis.
                        </p>
                        <p>
                            La République et Canton de Neuchâtel n'assume aucune responsabilité en cas de dommages
                            matériels ou immatériels résultant de l'utilisation ou de la non-utilisation des
                            informations disponibles, d'un usage abusif de la connexion ou de problèmes techniques.
                        </p>
                        <p>
                            La consultation du site et le téléchargement de contenus ne créent en aucun cas un
                            rapport contractuel avec la République et Canton de Neuchâtel.
                        </p>
                    </div>
                </section>

                {{-- Protection des données --}}
                <section id="protection-des-donnees" class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Protection des données
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            La protection de vos données personnelles est régie par la Convention intercantonale
                            relative à la protection des données et à la transparence dans les cantons du Jura et
                            de Neuchâtel (CPDT-JUNE).
                        </p>
                        <p>
                            Des informations détaillées sur la collecte et l'utilisation des données (journaux
                            techniques, statistiques d'utilisation, Matomo, etc.) figurent dans la section
                            <span class="underline underline-offset-2">Protection des données</span>
                            de ce site.
                        </p>
                    </div>
                </section>

                {{-- Droits d'auteur --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Droits d'auteur
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">
                        Tous les contenus (textes, images, documents, vidéos, etc.) du site sont protégés par le
                        droit d'auteur. Leur reproduction, diffusion ou utilisation à d'autres fins nécessite
                        l'autorisation écrite préalable de la République et Canton de Neuchâtel ou du titulaire
                        des droits expressément mentionné.
                    </p>
                </section>

                {{-- Sécurité et utilisation correcte --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Sécurité et utilisation correcte
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Le site est destiné à un usage personnel ou professionnel dans le cadre des demandes
                            liées à l'Espace Artistes. Toute utilisation abusive (intrusion, introduction de virus,
                            exploitation commerciale sans autorisation, etc.) est interdite.
                        </p>
                        <p>
                            La République et Canton de Neuchâtel met en place des mesures de sécurité adaptées,
                            mais ne peut garantir une protection absolue contre les attaques informatiques et décline
                            toute responsabilité. Les usagères et usagers doivent également protéger leurs
                            équipements et accès.
                        </p>
                    </div>
                </section>

                {{-- Contenus et liens externes --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Contenus et liens externes
                    </h2>
                    <p class="text-lg leading-relaxed text-brand">
                        Le site peut contenir des liens ou des contenus externes (par ex. vidéos YouTube). La
                        République et Canton de Neuchâtel n'a aucune influence sur leur contenu et décline toute
                        responsabilité. La consultation de ces services implique l'acceptation de leurs propres
                        conditions d'utilisation et politiques de confidentialité.
                    </p>
                </section>

                {{-- Modification --}}
                <section class="flex flex-col gap-6">
                    <h2 class="font-serif text-3xl font-bold text-brand sm:text-4xl">
                        Modification
                    </h2>
                    <div class="flex flex-col gap-5 text-lg leading-relaxed text-brand">
                        <p>
                            Les présentes conditions peuvent être adaptées à tout moment, sans préavis. La date de
                            la dernière mise à jour figure en bas de page.
                        </p>
                        <p>
                            <strong class="font-semibold">Mise à jour</strong> : janvier 2026
                        </p>
                    </div>
                </section>

            </div>
        </div>
    </x-ds.section>
</x-layouts.public>

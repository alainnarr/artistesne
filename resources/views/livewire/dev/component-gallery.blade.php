{{-- Component Gallery — DS Artistes.ne (local dev only) --}}
<div class="ds-gallery">

{{-- ===================================================================
     FOUNDATIONS — typo + palette
     =================================================================== --}}
<section id="foundations" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Fondations</h2>
    <p class="mb-6 text-sm text-brand-muted">Typographie (Lora + Public Sans) et palette du DS.</p>

    <x-dev.gallery-card label="Typographie — Titres (Lora Bold)">
        <div class="flex flex-col gap-3">
            <p class="font-serif text-[64px] font-bold leading-[70px] text-brand">Heading 3XL — 64/70</p>
            <p class="font-serif text-[40px] font-bold leading-[48px] text-brand">Heading XL — 40/48</p>
            <p class="font-serif text-[28px] font-bold leading-[34px] text-brand">Heading L — 28/34</p>
            <p class="font-serif text-xl font-bold leading-[30px] text-brand">Heading M — 20/30</p>
            <p class="font-serif text-lg font-bold leading-[30px] text-brand">Heading S — 18/30</p>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Typographie — Corps (Public Sans)" class="mt-6">
        <div class="flex flex-col gap-2">
            <p class="text-2xl leading-[34px] text-brand">Body XXL — 24/34 Regular</p>
            <p class="text-lg leading-[30px] text-brand">Body L — 18/30 Regular</p>
            <p class="text-base leading-6 text-brand">Body M — 16/24 Regular</p>
            <p class="text-base leading-5 text-brand">Label M — 16/20 Regular</p>
            <p class="text-xs font-medium leading-[17px] text-brand">Label XS — 12/17 Medium</p>
            <p class="text-xs font-light leading-[17px] text-brand-muted">Label XS Light — 12/17 Light</p>
            <p class="text-xs uppercase tracking-[0.12em] text-brand">Label XS Maj — 12/17 tracking 12</p>
            <p class="text-base font-medium leading-6 text-brand">CTA M — 16/24 Medium (boutons)</p>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Palette" class="mt-6">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                'brand' => '--color-brand',
                'brand-teal' => '--color-brand-teal',
                'brand-teal-light' => '--color-brand-teal-light',
                'brand-mint' => '--color-brand-mint',
                'brand-mint-soft' => '--color-brand-mint-soft',
                'brand-cream' => '--color-brand-cream',
                'brand-paper' => '--color-brand-paper',
                'brand-muted' => '--color-brand-muted',
                'brand-track' => '--color-brand-track',
                'brand-hairline' => '--color-brand-hairline',
                'brand-hover' => '--color-brand-hover',
                'success' => '--color-success',
                'error' => '--color-error',
                'domain-primary' => '--color-domain-primary',
                'domain-secondary' => '--color-domain-secondary',
                'neutral-30' => '--color-neutral-30',
            ] as $name => $cssVariable)
                <div class="flex items-center gap-3">
                    <span class="inline-block size-10 border border-brand-hairline" style="background-color: var({{ $cssVariable }})"></span>
                    <span class="text-xs">
                        <span class="block font-medium text-brand">{{ $name }}</span>
                        <span class="block font-mono text-brand-muted">{{ $cssVariable }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     BUTTONS
     =================================================================== --}}
<section id="buttons" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Buttons</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2274:12498 — 2 variants × 3 sizes × 5 states.</p>

    @foreach (['primary', 'secondary'] as $variant)
        <x-dev.gallery-card :label="ucfirst($variant).' — toutes les tailles & états'" class="mt-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium uppercase tracking-wide text-brand-muted">
                        <th class="pb-3">Size</th>
                        <th class="pb-3">Default</th>
                        <th class="pb-3">Hover (souris)</th>
                        <th class="pb-3">Pressed (clic)</th>
                        <th class="pb-3">Focus (Tab)</th>
                        <th class="pb-3">Disabled</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (['lg' => 'Large (56)', 'md' => 'Medium (42)', 'sm' => 'Small (32)'] as $size => $label)
                        <tr class="border-t border-brand-hairline">
                            <td class="py-3 pr-4 text-xs font-medium text-brand">{{ $label }}</td>
                            <td class="py-3 pr-4"><x-ds.btn :variant="$variant" :size="$size">Button</x-ds.btn></td>
                            <td class="py-3 pr-4"><x-ds.btn :variant="$variant" :size="$size">Hover me</x-ds.btn></td>
                            <td class="py-3 pr-4"><x-ds.btn :variant="$variant" :size="$size">Click me</x-ds.btn></td>
                            <td class="py-3 pr-4"><x-ds.btn :variant="$variant" :size="$size">Tab to focus</x-ds.btn></td>
                            <td class="py-3 pr-4"><x-ds.btn :variant="$variant" :size="$size" disabled>Disabled</x-ds.btn></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-dev.gallery-card>
    @endforeach

    <x-dev.gallery-card label="Avec icônes (leading & trailing)" class="mt-6">
        <div class="flex flex-wrap items-center gap-4">
            <x-ds.btn variant="primary" icon="search">Rechercher</x-ds.btn>
            <x-ds.btn variant="primary" icon-trailing="arrow-right">Continuer</x-ds.btn>
            <x-ds.btn variant="secondary" icon="plus">Ajouter</x-ds.btn>
            <x-ds.btn variant="secondary" size="sm" icon-trailing="external-link">Lien externe</x-ds.btn>
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     TAGS
     =================================================================== --}}
<section id="tags" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Tags</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2274:11691 — activités / mots-clés et domaines artistiques.</p>

    <x-dev.gallery-card label="Tag activité / mots-clés (primary + secondary)">
        <div class="flex flex-wrap items-center gap-2">
            <x-ds.tag>Chanteur·euse</x-ds.tag>
            <x-ds.tag>Compositeur·ice</x-ds.tag>
            <x-ds.tag variant="secondary">Instrumentiste</x-ds.tag>
            <x-ds.tag variant="secondary">Producteur·ice</x-ds.tag>
            <x-ds.tag removable>Avec close</x-ds.tag>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Tag domaine (label MAJ + carré couleur)" class="mt-6">
        <div class="flex flex-wrap items-center gap-4">
            <x-ds.tag variant="domain" domain-tone="primary">Musique</x-ds.tag>
            <x-ds.tag variant="domain" domain-tone="primary">Art vivant</x-ds.tag>
            <x-ds.tag variant="domain" domain-tone="secondary">Arts visuels</x-ds.tag>
            <x-ds.tag variant="domain" domain-tone="secondary">Cinéma et audiovisuel</x-ds.tag>
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     BADGES
     =================================================================== --}}
<section id="badges" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Badges</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2289:14466 — pastilles numériques.</p>

    <x-dev.gallery-card label="Variantes × tailles">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase text-brand-muted">
                    <th class="pb-3">Variant</th>
                    <th class="pb-3">SM (16)</th>
                    <th class="pb-3">MD (20)</th>
                    <th class="pb-3">LG (24)</th>
                </tr>
            </thead>
            <tbody>
                @foreach (['dark', 'light', 'soft'] as $v)
                    <tr class="border-t border-brand-hairline">
                        <td class="py-3 pr-4 text-xs font-medium text-brand">{{ $v }}</td>
                        <td class="py-3 pr-4"><x-ds.badge :variant="$v" size="sm">3</x-ds.badge></td>
                        <td class="py-3 pr-4"><x-ds.badge :variant="$v" size="md">9</x-ds.badge></td>
                        <td class="py-3 pr-4"><x-ds.badge :variant="$v" size="lg">12</x-ds.badge></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     INPUTS
     =================================================================== --}}
<section id="inputs" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Inputs & textarea & select</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2269:16306 / 2272:12569 — tous les états.</p>

    <x-dev.gallery-card label="Input — états">
        <div class="grid max-w-2xl gap-4">
            <x-ds.input label="Default" placeholder="Saisissez du texte" />
            <x-ds.input label="Avec valeur" value="Marie Dupont" />
            <x-ds.input label="Avec icône leading" icon="email" placeholder="email@exemple.ch" />
            <x-ds.input label="Succès (validé)" value="marie@exemple.ch" success />
            <x-ds.input label="Erreur" value="marie@" error="Adresse e-mail invalide." />
            <x-ds.input label="Désactivé" placeholder="Non modifiable" disabled />
            <x-ds.input label="Avec description" description="Texte d'aide sous le champ." placeholder="…" />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Textarea" class="mt-6">
        <div class="grid max-w-2xl gap-4">
            <x-ds.textarea label="Commentaire" placeholder="Votre commentaire…" />
            <x-ds.textarea label="Désactivé" placeholder="Verrouillé" disabled />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Select" class="mt-6">
        <div class="grid max-w-2xl gap-4">
            <x-ds.select label="Domaine principal" placeholder="Choisir un domaine"
                :options="['musique' => 'Musique', 'arts_visuels' => 'Arts visuels', 'theatre' => 'Théâtre']" />
            <x-ds.select label="Désactivé" disabled :options="['' => '—']" />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     CHECKBOX / RADIO
     =================================================================== --}}
<section id="selection" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Checkbox / Radio</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2272:11389 — sélection.</p>

    <x-dev.gallery-card label="Checkbox">
        <div class="flex flex-col gap-3">
            <x-ds.checkbox label="Non coché" />
            <x-ds.checkbox label="Coché par défaut" checked />
            <x-ds.checkbox label="Désactivé" disabled />
            <x-ds.checkbox label="Désactivé + coché" checked disabled />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Radio" class="mt-6">
        <div class="flex flex-col gap-3">
            <x-ds.radio name="demo" value="a" label="Option A" />
            <x-ds.radio name="demo" value="b" label="Option B (sélectionnée par défaut)" checked />
            <x-ds.radio name="demo" value="c" label="Option C" />
            <x-ds.radio name="demo2" value="x" label="Désactivée" disabled />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     DATEPICKER
     =================================================================== --}}
<section id="datepicker" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Datepicker</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2272:13659 — champ date avec icône calendrier (calendrier natif, popover custom à venir).</p>

    <x-dev.gallery-card label="Datepicker — états">
        <div class="grid max-w-md gap-4">
            <x-ds.datepicker label="Date de naissance" />
            <x-ds.datepicker label="Avec valeur" value="1985-06-15" />
            <x-ds.datepicker label="Désactivé" disabled />
            <x-ds.datepicker label="Erreur" error="Date invalide." />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     SEARCH
     =================================================================== --}}
<section id="search" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Search</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:38605 — composant de recherche.</p>

    <x-dev.gallery-card label="Sur fond sombre (bannière)">
        <div class="bg-brand p-6">
            <x-ds.search :show-filters="true" />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Avec filtres actifs (3)" class="mt-6">
        <div class="bg-brand p-6">
            <x-ds.search :show-filters="true" :filter-count="3" />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Avec couche suggestions + valeur" class="mt-6">
        <div class="bg-brand p-6">
            <x-ds.search
                value="neu"
                :suggestions="['Neuberger', 'Neuchâtel', 'Neumann', 'Suggestion 4', 'Suggestion 5', 'Suggestion 6', 'Suggestion 7']"
                :result-count="140"
            />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Sur fond clair" class="mt-6">
        <x-ds.search variant="on-light" :show-filters="true" />
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     CARD ARTIST
     =================================================================== --}}
<section id="card-artist" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Card Artist</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:38922 — fiche d'artiste.</p>

    <x-dev.gallery-card label="Master — exemple Figma">
        <div class="bg-brand-cream p-8">
            <x-ds.card-artist
                name="Jean Kevin"
                location="La-Chaux-de-Fonds"
                primary-domain="Musique"
                secondary-domain="Art vivant"
                :activities="['Chanteur·euse', 'Compositeur·ice', 'Instrumentiste', 'Producteur·ice']"
                href="#"
            />
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Grille (3 colonnes desktop)" class="mt-6">
        <div class="grid grid-cols-1 gap-6 bg-brand-cream p-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-ds.card-artist
                name="Marie Dupont"
                location="Neuchâtel"
                primary-domain="Arts visuels"
                :activities="['Peintre', 'Illustratrice']"
                href="#"
            />
            <x-ds.card-artist
                name="Jean-Pierre Müller"
                location="Le Locle"
                primary-domain="Musique"
                secondary-domain="Théâtre"
                :activities="['Guitariste', 'Compositeur', 'Comédien']"
                href="#"
            />
            <x-ds.card-artist
                name="Aïsha Nkemdirim"
                location="La Chaux-de-Fonds"
                primary-domain="Danse"
                :activities="['Chorégraphe', 'Danseuse']"
                href="#"
            />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     LIST
     =================================================================== --}}
<section id="list" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">List</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:39018 — listes à puces du DS.</p>

    <x-dev.gallery-card label="Canvas (paragraphe)">
        <x-ds.list :items="['Premier élément', 'Deuxième élément', 'Troisième élément']" />
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Card (em-dash — utilisée dans Card Artist)" class="mt-6">
        <x-ds.list variant="card" :items="['Chanteur·euse', 'Compositeur·ice', 'Instrumentiste']" />
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     LIST HEADER
     =================================================================== --}}
<section id="list-header" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">List header</h2>
    <p class="mb-6 text-sm text-brand-muted">Titre + compteur + sort menu + hairline. Utilisé sur la page d'accueil et listes.</p>

    <x-dev.gallery-card label="Avec tri (5 artistes)">
        <x-ds.list-header
            title="Tous les artistes"
            :count="5"
            entity-label="artiste"
            :sort-options="['recent' => 'Plus récents', 'name' => 'Alphabétique']"
            sort-selected="recent"
        />
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Sans tri (1 résultat)" class="mt-6">
        <x-ds.list-header title="Résultats de la recherche" :count="1" entity-label="résultat" />
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     PROFILE SECTION
     =================================================================== --}}
<section id="profile-section" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Profile section</h2>
    <p class="mb-6 text-sm text-brand-muted">Bloc 2 colonnes (titre gauche / contenu droite) avec séparateur hairline. Utilisé sur la fiche artiste.</p>

    <x-dev.gallery-card label="Empilé (avec séparateurs)">
        <div>
            <x-ds.profile-section title="Espaces personnels">
                <x-ds.link-list-item href="https://exemple.ch">exemple.ch</x-ds.link-list-item>
                <x-ds.link-list-item href="https://instagram.com/exemple">@exemple</x-ds.link-list-item>
            </x-ds.profile-section>
            <x-ds.profile-section title="Activités secondaires">
                <p class="text-base text-brand">Enseignement / Programmation</p>
            </x-ds.profile-section>
            <x-ds.profile-section title="Mots-clés" :divided="false">
                <div class="flex flex-wrap gap-2">
                    <x-ds.tag variant="primary">Improvisation</x-ds.tag>
                    <x-ds.tag variant="primary">Performance</x-ds.tag>
                </div>
            </x-ds.profile-section>
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     LINK LIST ITEM
     =================================================================== --}}
<section id="link-list-item" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Link list item</h2>
    <p class="mb-6 text-sm text-brand-muted">Carré mint + label. Utilisé dans les sections "Espaces personnels" et "Collaborations" de la fiche artiste.</p>

    <x-dev.gallery-card label="Lien externe">
        <x-ds.link-list-item href="https://exemple.ch">exemple.ch</x-ds.link-list-item>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Lien interne (sans flèche externe)" class="mt-6">
        <x-ds.link-list-item href="/contact" :external="false">Page de contact</x-ds.link-list-item>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     ACCORDION
     =================================================================== --}}
<section id="accordion" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Accordion</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:39210 — accordéon avec badge.</p>

    <x-dev.gallery-card label="Level 1 — fermé / ouvert / avec compteur">
        <div class="max-w-md">
            <x-ds.accordion title="Domaine principal" :active-count="4" :open="true">
                <div class="flex flex-wrap gap-2 pt-3">
                    <x-ds.tag>Musique</x-ds.tag>
                    <x-ds.tag variant="secondary">Spectacle vivant</x-ds.tag>
                    <x-ds.tag variant="secondary">Arts visuels</x-ds.tag>
                    <x-ds.tag variant="secondary">Cinéma et audiovisuel</x-ds.tag>
                </div>
            </x-ds.accordion>
            <x-ds.accordion title="Sous domaine" />
            <x-ds.accordion title="Commune de résidence" :active-count="2" />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     BANNERS
     =================================================================== --}}
<section id="banner" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Banners</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:38826 (title) + 2439:39402 (push).</p>

    <x-dev.gallery-card label="Banner Title — desktop">
        <x-ds.banner-title
            title="Annuaire des artistes"
            subtitle="neuchâtelois·es"
            description="Artistes.ne réunit les profils des artistes professionnel·les ancré·es dans le canton. Découvrez leurs pratiques, explorez leurs univers, entrez en contact."
        >
            <x-ds.search :show-filters="true" />
        </x-ds.banner-title>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Banner Title — compact" class="mt-6">
        <x-ds.banner-title title="Annuaire" subtitle="neuchâtelois·es" compact />
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Banner Push — large" class="mt-6">
        <x-ds.banner-push
            title="Se faire référencer sur la plateforme"
            description="Artiste professionnel·le dans le canton de Neuchâtel ? Rejoignez les [X] artistes de l'annuaire et ouvrez votre pratique à de nouveaux regards."
            cta-label="Espace artistes"
            layout="large"
        />
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Banner Push — small (mobile)" class="mt-6">
        <div class="max-w-sm">
            <x-ds.banner-push
                title="Se faire référencer sur la plateforme"
                description="Vous n'êtes pas encore référencé·e sur l'annuaire ?"
                cta-label="Créer un profil artiste"
                layout="small"
            />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     MODAL
     =================================================================== --}}
<section id="modal" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Modal</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2275:11730 — overlay filtres.</p>

    <x-dev.gallery-card label="Modal filtres (cliquer pour ouvrir)">
        <x-ds.btn variant="primary" icon="filter"
                  x-on:click="$dispatch('open-modal', { id: 'filters-demo' })">
            Ouvrir la modal filtres
        </x-ds.btn>

        <x-ds.modal id="filters-demo" title="Filtres">
            <x-ds.accordion title="Domaine principal" :active-count="4" :open="true">
                <div class="flex flex-wrap gap-2 pt-3">
                    <x-ds.tag>Musique</x-ds.tag>
                    <x-ds.tag variant="secondary">Spectacle vivant</x-ds.tag>
                    <x-ds.tag variant="secondary">Arts visuels</x-ds.tag>
                    <x-ds.tag variant="secondary">Cinéma et audiovisuel</x-ds.tag>
                </div>
            </x-ds.accordion>
            <x-ds.accordion title="Sous domaine" />
            <x-ds.accordion title="Domaine secondaire" :active-count="2" />
            <x-ds.accordion title="Activité secondaire" />
            <x-ds.accordion title="Commune de résidence" />

            <x-slot:footer>
                <button type="button" class="text-sm font-medium text-brand-teal underline hover:no-underline">
                    Effacer les filtres (4)
                </button>
                <x-ds.btn variant="primary" size="md">Afficher les 102 artistes</x-ds.btn>
            </x-slot:footer>
        </x-ds.modal>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     PAGE SHELL — section / hero / page-header
     =================================================================== --}}
<section id="page-shell" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Page shell</h2>
    <p class="mb-6 text-sm text-brand-muted">
        Extraits des patterns de la Home (Figma 561:49042) : conteneurs de
        section, hero d'accueil et page header pour les pages internes.
    </p>

    <x-dev.gallery-card label="x-ds.section · variant=paper / cream / dark" class="mt-6">
        <div class="flex flex-col gap-3">
            <x-ds.section variant="paper" padding="tight" class="border border-brand-hairline">
                <p class="text-sm text-brand">Section <code>paper</code> — fond <code>bg-brand-paper</code></p>
            </x-ds.section>
            <x-ds.section variant="cream" padding="tight" class="border border-brand-hairline">
                <p class="text-sm text-brand">Section <code>cream</code> — fond <code>bg-brand-cream</code></p>
            </x-ds.section>
            <x-ds.section variant="dark" padding="tight">
                <p class="text-sm text-brand-paper">Section <code>dark</code> — fond <code>bg-brand</code>, texte clair</p>
            </x-ds.section>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="x-ds.section · containers (default / narrow / wide / none)" class="mt-6">
        <div class="flex flex-col gap-2">
            <x-ds.section variant="cream" padding="tight" container="default" class="border border-brand-hairline">
                <p class="text-xs text-brand-muted">container=<strong>default</strong> · max-w-6xl + px-6 sm:px-20</p>
            </x-ds.section>
            <x-ds.section variant="cream" padding="tight" container="narrow" class="border border-brand-hairline">
                <p class="text-xs text-brand-muted">container=<strong>narrow</strong> · max-w-3xl + px-6 sm:px-10</p>
            </x-ds.section>
            <x-ds.section variant="cream" padding="tight" container="wide" class="border border-brand-hairline">
                <p class="text-xs text-brand-muted">container=<strong>wide</strong> · max-w-[1216px] + px-6 sm:px-20</p>
            </x-ds.section>
            <x-ds.section variant="cream" padding="tight" container="none" class="border border-brand-hairline">
                <p class="px-6 text-xs text-brand-muted">container=<strong>none</strong> · le slot gère sa propre largeur</p>
            </x-ds.section>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="x-ds.hero · variant=home (avec slot action)" class="mt-6">
        <x-ds.section variant="paper" padding="tight" class="border border-brand-hairline">
            <x-ds.hero title="Annuaire des artistes" accent="neuchâtelois·es">
                <x-slot:lead-slot>
                    <p>
                        <span class="font-normal">Artistes.ne</span> réunit les profils des artistes professionnel·les ancré·es dans le canton.
                    </p>
                </x-slot:lead-slot>

                <x-slot:action>
                    <x-ds.search variant="on-dark" />
                </x-slot:action>
            </x-ds.hero>
        </x-ds.section>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="x-ds.hero · variant=page (sans action)" class="mt-6">
        <x-ds.section variant="paper" padding="tight" class="border border-brand-hairline">
            <x-ds.hero
                variant="page"
                title="À propos"
                accent="d'Artistes.ne"
                lead="L'annuaire des artistes professionnel·les actif·ves dans le canton de Neuchâtel."
            />
        </x-ds.section>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="x-ds.page-header (lien retour + titre + lead + actions)" class="mt-6">
        <x-ds.section variant="paper" padding="tight" class="border border-brand-hairline">
            <x-ds.page-header
                title="Découvrir les artistes"
                accent="référencé·es"
                lead="Filtrez par domaine, activité ou commune pour affiner votre recherche."
            >
                <x-slot:back>
                    <a href="#" class="inline-flex items-center gap-2 text-brand-muted transition hover:text-brand">
                        <x-picto name="arrow-left" class="size-4" />
                        Retour
                    </a>
                </x-slot:back>

                <p class="text-sm text-brand-muted">152 résultats</p>
                <x-ds.btn variant="secondary" size="sm" icon="filter">Filtrer</x-ds.btn>
            </x-ds.page-header>
        </x-ds.section>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     SORT MENU
     =================================================================== --}}
<section id="sort-menu" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Sort menu</h2>
    <p class="mb-6 text-sm text-brand-muted">
        Listbox déroulante « Trier par » utilisée sur les pages de listing.
        Émet <code>$dispatch('ds-sort-change', { value })</code> ou met à jour
        une propriété Livewire via <code>wire-key</code>.
    </p>

    <x-dev.gallery-card label="Sort menu — sélection par évènement DOM (démo Alpine)">
        <div class="flex flex-col gap-4" x-data="{ value: 'recent' }">
            <div class="flex items-center justify-between border-b border-brand-hairline pb-3">
                <p class="text-sm text-brand-muted">
                    Sélection courante : <span class="font-medium text-brand" x-text="value"></span>
                </p>
                <x-ds.sort-menu
                    :options="['recent' => 'Plus récents', 'name' => 'Nom (A→Z)', 'oldest' => 'Plus anciens']"
                    x-on:ds-sort-change="value = $event.detail.value"
                />
            </div>
        </div>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Sort menu — alignement gauche" class="mt-6">
        <div class="flex items-center justify-between border-b border-brand-hairline pb-3">
            <x-ds.sort-menu
                label="Ordre"
                align="left"
                :options="['asc' => 'Ascendant', 'desc' => 'Descendant']"
                selected="asc"
            />
        </div>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     EMPTY STATE
     =================================================================== --}}
<section id="empty-state" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Empty state</h2>
    <p class="mb-6 text-sm text-brand-muted">
        Bloc « aucun résultat / page vide » réutilisé sur la Home, la liste,
        les pages de confirmation et les écrans d'erreur (404/500).
    </p>

    <x-dev.gallery-card label="Empty state — minimal (titre + description)">
        <x-ds.empty-state
            title="Aucun artiste"
            description="Aucun artiste ne correspond à votre recherche."
        />
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Empty state — avec icône et CTA" class="mt-6">
        <x-ds.empty-state
            title="Votre demande a bien été transmise"
            description="Le SCNE l'examinera dans les meilleurs délais. Vous recevrez ensuite un e-mail vous informant de la suite donnée."
        >
            <x-slot:icon>
                <x-picto name="check" class="size-6" />
            </x-slot:icon>

            <x-ds.btn href="/" variant="secondary" size="md">Retour à l'accueil</x-ds.btn>
        </x-ds.empty-state>
    </x-dev.gallery-card>

    <x-dev.gallery-card label="Empty state — tone=cream + align=left" class="mt-6">
        <x-ds.empty-state
            tone="cream"
            align="left"
            title="404 — Page introuvable"
        >
            <x-slot:body>
                <p>Le contenu que vous cherchez a peut-être été déplacé.</p>
                <p>Revenez à l'accueil ou utilisez la recherche.</p>
            </x-slot:body>

            <x-ds.btn href="/" variant="primary" size="md">Retour à l'accueil</x-ds.btn>
        </x-ds.empty-state>
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     CONTACT FORM
     =================================================================== --}}
<section id="contact-form" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Contact form</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 561:51129 — formulaire de contact public.</p>

    <x-dev.gallery-card label="Aperçu — variante par défaut">
        <x-ds.contact-form />
    </x-dev.gallery-card>
</section>

{{-- ===================================================================
     COOKIES
     =================================================================== --}}
<section id="cookies" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">Cookies banner</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:39114 — bandeau d'acceptation.</p>

    <x-dev.gallery-card label="Aperçu (le bandeau réel s'affiche en bas si non accepté)">
        <p class="text-sm text-brand-muted">Le bandeau cookies est rendu au bas de cette page si <code>localStorage.artistes-ne-cookies</code> est vide.</p>
        <button type="button"
                class="mt-3 text-sm font-medium text-brand-teal underline"
                onclick="localStorage.removeItem('artistes-ne-cookies'); location.reload();">
            Réinitialiser & afficher le bandeau
        </button>
    </x-dev.gallery-card>
</section>
<section id="email" class="mb-20 scroll-mt-6">
    <h2 class="mb-1 font-serif text-2xl font-bold text-brand">E-mail layout</h2>
    <p class="mb-6 text-sm text-brand-muted">Figma 2439:39306 — gabarit pour emails transactionnels.</p>

    <x-dev.gallery-card label="Aperçu HTML (rendu inline pour démo)">
        <iframe srcdoc='<!doctype html><html><body style="margin:0;padding:24px;background:#fefcf7;font-family:Public Sans,Arial,sans-serif;color:#2e3d3c">
            <div style="max-width:600px;margin:0 auto;background:#fefefe;padding:32px 24px">
                <div style="display:inline-block;background:#2e3d3c;color:#fefefe;font-family:Lora,Georgia,serif;font-weight:700;font-size:20px;padding:8px 16px">Artistes.ne</div>
                <h1 style="font-family:Lora,Georgia,serif;font-weight:700;font-size:24px;margin:24px 0 8px">Confirmation de référencement sur <span style="color:#477e7b;font-style:italic">Artistes.ne</span></h1>
                <p>Bonjour [Nom complet],</p>
                <p>Nous avons bien examiné votre demande de référencement et nous avons le plaisir de vous informer qu&#39;elle a été acceptée.</p>
                <p>Vous pouvez désormais <strong>créer votre profil</strong> sur Artistes.ne en cliquant sur le lien ci-dessous.</p>
                <p><a href="#" style="display:inline-block;background:#bfeceb;color:#2e3d3c;padding:12px 24px;font-weight:500;text-decoration:none">Créer mon profil</a></p>
                <p style="margin-top:24px">Bienvenue parmi les artistes de l&#39;annuaire neuchâtelois.<br><strong>L&#39;équipe du SCNE</strong></p>
            </div>
            <div style="text-align:center;padding:24px 0;font-size:14px;color:#5f6665"><strong>//ne.ch</strong></div>
        </body></html>'
        class="block h-[640px] w-full border border-brand-hairline"></iframe>
    </x-dev.gallery-card>
</section>

{{-- Bandeau cookies en bas (effet réel) --}}
<x-ds.cookies-banner />

</div>

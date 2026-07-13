<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\SearchSynonym;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('scout:sync-synonyms')]
#[Description('Sync search synonyms from the database to Meilisearch')]
class SyncSearchSynonyms extends Command
{
    public function handle(): int
    {
        $synonyms = SearchSynonym::all();

        if ($synonyms->isEmpty()) {
            $this->info('Aucun synonyme à synchroniser.');

            return self::SUCCESS;
        }

        $host = rtrim((string) config('scout.meilisearch.host', 'http://localhost:7700'), '/');
        $key = (string) config('scout.meilisearch.key', '');
        $index = (new Artist)->searchableAs();

        /** @var array<string, array<int, string>> $payload */
        $payload = [];

        foreach ($synonyms as $synonym) {
            $payload[$synonym->term] = $synonym->synonyms;

            if (! $synonym->one_way) {
                foreach ($synonym->synonyms as $syn) {
                    $allTerms = array_merge([$synonym->term], $synonym->synonyms);
                    $payload[$syn] = array_values(array_filter($allTerms, fn (string $t): bool => $t !== $syn));
                }
            }
        }

        $response = Http::withToken($key)
            ->put("{$host}/indexes/{$index}/settings/synonyms", $payload);

        if ($response->successful()) {
            $this->info("{$synonyms->count()} règle(s) de synonymes synchronisée(s) vers Meilisearch (index « {$index} »).");

            return self::SUCCESS;
        }

        $this->error('Échec de la synchronisation Meilisearch : '.$response->body());

        return self::FAILURE;
    }
}

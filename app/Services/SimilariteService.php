<?php

namespace App\Services;

use App\Models\AnneeAcademique;
use App\Models\Memoire;
use Illuminate\Support\Collection;

// Comparaison "simplifiée" au sens du cahier des charges : titre + résumé uniquement,
// jamais le contenu du fichier PDF. Indice de Jaccard sur les mots (hors mots vides),
// pas d'appel réseau, aucune donnée transmise à un tiers.
// Comparaison restreinte aux années académiques ANTÉRIEURES à celle du dépôt : deux
// mémoires de la même promotion traitant un sujet académique proche ne doivent pas
// se signaler mutuellement comme doublons.
class SimilariteService
{
    private const MOTS_VIDES = [
        'le', 'la', 'les', 'de', 'des', 'du', 'un', 'une', 'et', 'en', 'pour',
        'dans', 'sur', 'au', 'aux', 'est', 'sont', 'avec', 'par', 'que', 'qui',
        'ce', 'ces', 'se', 'son', 'sa', 'ses', 'ou', 'à', 'd', 'l',
    ];

    /**
     * Retourne la meilleure correspondance trouvée parmi les années antérieures :
     * ['memoire_id' => int, 'score' => float], ou null (aucune année antérieure, ou rien trouvé).
     */
    public function meilleureCorrespondance(string $titre, string $resume, int $anneeAcademiqueId, ?int $exclureId = null): ?array
    {
        $anneesAnterieures = $this->anneesAnterieures($anneeAcademiqueId);

        if ($anneesAnterieures->isEmpty()) {
            return null;
        }

        $motsA = $this->motsUniques($this->normaliser($titre.' '.$resume));

        if (empty($motsA)) {
            return null;
        }

        $meilleur = null;

        Memoire::query()
            ->whereIn('annee_academique_id', $anneesAnterieures)
            ->when($exclureId, fn ($q) => $q->where('id', '!=', $exclureId))
            ->select('id', 'titre', 'resume')
            ->chunkById(200, function ($memoires) use (&$meilleur, $motsA) {
                foreach ($memoires as $memoire) {
                    $motsB = $this->motsUniques($this->normaliser($memoire->titre.' '.$memoire->resume));
                    $score = $this->jaccard($motsA, $motsB);

                    if (! $meilleur || $score > $meilleur['score']) {
                        $meilleur = ['memoire_id' => $memoire->id, 'score' => $score];
                    }
                }
            });

        return $meilleur;
    }

    /**
     * IDs des années académiques dont l'année de début (4 premiers chiffres du libellé,
     * ex. "2025-2026" -> 2025) est strictement antérieure à celle de $anneeAcademiqueId.
     */
    private function anneesAnterieures(int $anneeAcademiqueId): Collection
    {
        $courante = AnneeAcademique::find($anneeAcademiqueId);

        if (! $courante) {
            return collect();
        }

        $anneeDebutCourante = (int) substr($courante->libelle, 0, 4);

        return AnneeAcademique::all()
            ->filter(fn ($a) => (int) substr($a->libelle, 0, 4) < $anneeDebutCourante)
            ->pluck('id');
    }

    private function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte);
        $texte = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texte);

        return preg_replace('/\s+/', ' ', trim($texte));
    }

    private function motsUniques(string $texte): array
    {
        $mots = array_filter(
            explode(' ', $texte),
            fn ($mot) => mb_strlen($mot) > 2 && ! in_array($mot, self::MOTS_VIDES, true)
        );

        return array_unique($mots);
    }

    private function jaccard(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? round(($intersection / $union) * 100, 2) : 0.0;
    }
}

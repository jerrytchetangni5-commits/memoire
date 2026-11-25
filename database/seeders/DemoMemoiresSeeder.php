<?php

namespace Database\Seeders;

use App\Models\AnneeAcademique;
use App\Models\Filiere;
use App\Models\Memoire;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DemoMemoiresSeeder extends Seeder
{
    public function run(): void
    {
        $filiere = Filiere::query()->orderBy('id')->first();

        if (! $filiere) {
            throw new RuntimeException('Run EtudiantsEneamSeeder before DemoMemoiresSeeder.');
        }

        $annee = AnneeAcademique::firstOrCreate(['libelle' => '2026-2027']);

        $memoires = [
            [
                'titre' => 'Memoire de demonstration - acces public',
                'resume' => 'Document de demonstration pour tester le catalogue, la lecture PDF et le telechargement.',
                'auteur_nom' => 'Demo',
                'auteur_prenom' => 'Etudiant',
                'autres_auteurs' => 'Co-auteur Exemple',
                'statut' => 'valide',
                'telechargement_autorise' => true,
            ],
            [
                'titre' => 'Memoire de demonstration - lecture seule',
                'resume' => 'Document de demonstration publie, dont le telechargement est desactive pour tester les droits.',
                'auteur_nom' => 'Test',
                'auteur_prenom' => 'Aminata',
                'autres_auteurs' => null,
                'statut' => 'valide',
                'telechargement_autorise' => false,
            ],
            [
                'titre' => 'Memoire de demonstration - en attente',
                'resume' => 'Document de demonstration en attente de validation par un responsable.',
                'auteur_nom' => 'Test',
                'auteur_prenom' => 'Koffi',
                'autres_auteurs' => null,
                'statut' => 'soumis',
                'telechargement_autorise' => false,
            ],
            [
                'titre' => 'Memoire de demonstration - rejete',
                'resume' => 'Document de demonstration rejete pour tester l’affichage du motif de rejet.',
                'auteur_nom' => 'Test',
                'auteur_prenom' => 'Marie',
                'autres_auteurs' => null,
                'statut' => 'rejete',
                'telechargement_autorise' => false,
            ],
        ];

        foreach ($memoires as $donnees) {
            $fichier = 'memoires/demo-'.Str::slug($donnees['titre']).'.pdf';
            $this->ecrirePdf($fichier, $donnees['titre'], $donnees['auteur_prenom'].' '.$donnees['auteur_nom']);

            Memoire::updateOrCreate(
                ['titre' => $donnees['titre']],
                [
                    ...$donnees,
                    'user_id' => null,
                    'filiere_id' => $filiere->id,
                    'annee_academique_id' => $annee->id,
                    'fichier' => $fichier,
                    'valide_par' => null,
                    'date_validation' => $donnees['statut'] === 'soumis' ? null : now(),
                    'motif_rejet' => $donnees['statut'] === 'rejete'
                        ? 'Mémoire de démonstration : rejet utilisé pour tester le parcours.'
                        : null,
                ]
            );
        }
    }

    private function ecrirePdf(string $chemin, string $titre, string $auteur): void
    {
        $texte = "BT /F1 16 Tf 50 780 Td (".$this->echapperPdf($titre).") Tj "
            ."/F1 12 Tf 0 -32 Td (".$this->echapperPdf($auteur).") Tj "
            ."0 -40 Td (PDF de demonstration genere par le seeder.) Tj ET";
        $objets = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($texte)." >>\nstream\n".$texte."\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objets as $index => $objet) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$objet."\nendobj\n";
        }

        $debutXref = strlen($pdf);
        $pdf .= "xref\n0 ".count($offsets)."\n0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size ".count($offsets)." /Root 1 0 R >>\nstartxref\n{$debutXref}\n%%EOF";

        if (! Storage::disk('local')->put($chemin, $pdf)) {
            throw new RuntimeException("Unable to write demonstration PDF: {$chemin}");
        }
    }

    private function echapperPdf(string $texte): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texte);
    }
}

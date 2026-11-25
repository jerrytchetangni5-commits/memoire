<?php

namespace App\Services;

use App\Models\Paiement;

/** Résultat d'une demande d'accès : par quelle porte, et avec quels droits. */
final class AccesMemoire
{
    /** Utilisateur connecté (étudiant, responsable, admin) : règle historique. */
    public const CONNECTE = 'connecte';

    /** Externe ayant payé : accès par jeton. */
    public const JETON = 'jeton';

    public function __construct(
        public readonly string $mode,
        public readonly ?Paiement $paiement,
        public readonly bool $peutTelecharger,
    ) {}
}

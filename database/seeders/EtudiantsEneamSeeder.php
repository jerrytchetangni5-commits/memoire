<?php

namespace Database\Seeders;

use App\Models\EtudiantEneam;
use App\Models\Filiere;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Domaine e-mail "@eneam.bj" : non spécifié par le cahier des charges, assumé ici.
class EtudiantsEneamSeeder extends Seeder
{
    public function run(): void
    {
        $filieres = collect([
            ['code' => 'FC', 'nom' => 'Finance-Comptabilité'],
            ['code' => 'GRH', 'nom' => 'Gestion des Ressources Humaines'],
            ['code' => 'MKT', 'nom' => 'Marketing'],
            ['code' => 'CI', 'nom' => 'Commerce International'],
            ['code' => 'SE', 'nom' => 'Statistique-Économie'],
            ['code' => 'IG', 'nom' => 'Informatique de Gestion'],
        ])->map(fn ($f) => Filiere::firstOrCreate(['code' => $f['code']], ['nom' => $f['nom']]));

        $etudiants = [
            ['nom' => 'Tchetangni', 'prenom' => 'Jerry', 'matricule' => '11408325'],
            ['nom' => 'Adjovi', 'prenom' => 'Komlan', 'matricule' => '20230001'],
            ['nom' => 'Houngbo', 'prenom' => 'Affoué', 'matricule' => '20230002'],
            ['nom' => 'Dossou', 'prenom' => 'Rachidatou', 'matricule' => '20230003'],
            ['nom' => 'Agbodjan', 'prenom' => 'Eustache', 'matricule' => '20230004'],
            ['nom' => 'Akpovi', 'prenom' => 'Fidélia', 'matricule' => '20230005'],
            ['nom' => 'Zannou', 'prenom' => 'Boris', 'matricule' => '20230006'],
            ['nom' => 'Sagbo', 'prenom' => 'Chimène', 'matricule' => '20230007'],
            ['nom' => 'Tognon', 'prenom' => 'Désiré', 'matricule' => '20230008'],
            ['nom' => 'Houessou', 'prenom' => 'Edwige', 'matricule' => '20230009'],
            ['nom' => 'Koudjo', 'prenom' => 'Franck', 'matricule' => '20230010'],
            ['nom' => 'Lawani', 'prenom' => 'Gracia', 'matricule' => '20230011'],
            ['nom' => 'Mensah', 'prenom' => 'Hervé', 'matricule' => '20230012'],
            ['nom' => 'Noudegbessi', 'prenom' => 'Inès', 'matricule' => '20230013'],
            ['nom' => 'Oussou', 'prenom' => 'Josias', 'matricule' => '20230014'],
            ['nom' => 'Padonou', 'prenom' => 'Kafayath', 'matricule' => '20230015'],
            ['nom' => 'Quenum', 'prenom' => 'Luc', 'matricule' => '20230016'],
            ['nom' => 'Sossou', 'prenom' => 'Mahoussi', 'matricule' => '20230017'],
            ['nom' => 'Tchibozo', 'prenom' => 'Nadège', 'matricule' => '20230018'],
            ['nom' => 'Vigan', 'prenom' => 'Olivier', 'matricule' => '20230019'],
            ['nom' => 'Zinsou', 'prenom' => 'Prisca', 'matricule' => '20230020'],
        ];

        foreach ($etudiants as $i => $data) {
            $email = $i === 0
                ? 'ligopa1596@afronewz.com'
                : Str::slug($data['prenom']).'.'.Str::slug($data['nom']).'@eneam.bj';

            EtudiantEneam::updateOrCreate(
                ['matricule' => $data['matricule']],
                [
                    'email' => $email,
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom'],
                    'filiere_id' => $filieres[$i % $filieres->count()]->id,
                ]
            );
        }
    }
}
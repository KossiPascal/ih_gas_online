<?php

namespace App\Enums;

enum UserType: string
{
    case Administrateur = '1';

    case Comptabilite = '2';

    case Magasinier = '3';

    case Caisse = '4';

    case Pharmacie = '5';

    public function label(): string
    {
        return match ($this) {
            UserType::Administrateur => '',
            UserType::Comptabilite => '',
            UserType::Magasinier => '',
            UserType::Caisse => '',
            UserType::Pharmacie => '',
        };
    }
}

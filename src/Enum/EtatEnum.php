<?php

namespace App\Enum;

enum EtatEnum: string
{
    case enAttente = 'enAttente';
    case Acceptée = 'Acceptée';
    case Rejetée = 'Rejetée';
}
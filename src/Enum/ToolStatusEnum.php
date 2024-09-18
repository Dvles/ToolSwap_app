<?php


namespace App\Enum;

enum ToolStatusEnum: string
{
    case PENDING = 'En attente';
    case ACTIVE = 'Actif';
    case RETURNED = 'Rendu';
    case RECEIVED = 'Reçu';
}


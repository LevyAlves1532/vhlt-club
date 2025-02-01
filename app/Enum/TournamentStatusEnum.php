<?php

namespace App\Enum;

use Filament\Support\Colors\Color;

enum TournamentStatusEnum: string
{
    case OUTLINE = 'outline';
    case SAMPLE = 'sample';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case FINISHED = 'finished';
    case CANCELED = 'canceled';

    public static function labels()
    {
        return [
            self::OUTLINE->value => 'Esboço',
            self::SAMPLE->value => 'Amostra',
            self::OPEN->value => 'Aberto',
            self::CLOSED->value => 'Fechado',
            self::FINISHED->value => 'Finalizado',
            self::CANCELED->value => 'Cancelado',
        ];
    }

    public static function color()
    {
        return [
            self::OUTLINE->value => Color::Gray,
            self::SAMPLE->value => Color::Blue,
            self::OPEN->value => Color::Green,
            self::CLOSED->value => Color::Orange,
            self::FINISHED->value => Color::Red,
            self::CANCELED->value => Color::Red,
        ];
    }
}

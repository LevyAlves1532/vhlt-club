<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Resources\TeamResource;
use App\Models\Team;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTeam extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected function beforeValidate(): void
    {
        $tournament_id = $this->data['tournament_id'];
        $name = $this->data['name'];

        $count = Team::where('tournament_id', $tournament_id)->where('name', $name)->count();

        if ($count > 0) {
            Notification::make()
                ->title('Nome repetido')
                ->body('Existe um time neste torneio com esse nome')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}

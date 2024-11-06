<?php
namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;


class BandsFacade
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }
    public function getAllBands(): array
    {
    return $this->database->table('bands')->fetchAll();
    }
    public function addBand(string $name, $description): void
    {
        $this->database->table('bands')->insert(
            [
                'name' => $name,
                'description' => $description
            ]
        );
        

    }
    public function assignBandToStage(int $stageId, int $bandId): void
    {
        $this->database->table('stage_bands')->insert([
            'stage_id' => $stageId,
            'band_id' => $bandId
        ]);
    }
    public function getBandsByStageWithTimes(int $stageId): array
    {
        $stageBands = $this->database->table('stage_bands')
            ->where('stage_bands.stage_id', $stageId)
            ->fetchAll();
    
        return array_map(function ($stageBand) {
            $band = $stageBand->ref('bands', 'band_id');
            return (object)[
                'time' => $stageBand->time,
                'name' => $band->name,
                'description' => $band->description,
                'id' => $band->id
            ];
        }, $stageBands);
    }
}
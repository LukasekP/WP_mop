<?php
namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;


class FestivalFacade
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function getFestivals(): Selection
    {
        return $this->database->table('festivals');
    }

    public function getFestivalById(int $id): ?ActiveRow
    {
        return $this->database->table('festivals')->get($id);
    }
    public function getBandById(int $id): ?ActiveRow
    {
        return $this->database->table('bands')->get($id);
    }

    
 

    public function addFestival(array $data): ActiveRow
    {
        return $this->database->table('festivals')->insert($data);
    }

    public function addStage(int $festivalId, string $name): void
    {
        $this->database->table('stages')->insert([
            'festival_id' => $festivalId,
            'name' => $name
        ]);
    }

   

    public function assignBandToStage(int $stageId, int $bandId): void
    {
        $this->database->table('stage_bands')->insert([
            'stage_id' => $stageId,
            'band_id' => $bandId
        ]);
    }
    public function getStagesWithBands(int $festivalId): array
    {
        $stages = $this->database->table('stages')->where('festival_id', $festivalId)->fetchAll();
        $result = [];
    
        foreach ($stages as $stage) {
            $bands = $this->database->table('stage_bands')
                ->where('stage_id', $stage->id)
                ->fetchAll();
    
            $stageBands = [];
            foreach ($bands as $band) {
                $stageBands[] = $this->database->table('bands')->get($band->band_id);
            }
    
            $result[] = (object)[
                'id' => $stage->id,
                'name' => $stage->name,
                'bands' => $stageBands
            ];
        }
    
        return $result;
    }
    

    public function getLastInsertedBandId(): int
    {
        return $this->database->table('bands')->max('id');
    }
    public function getStagesByFestival(int $festivalId): array
    {
        return $this->database->table('stages')->where('festival_id', $festivalId)->fetchAll();
    }



    public function getStageById(int $stageId)
{
    return $this->database->table('stages')
        ->get($stageId);
}

    public function getBandsByStage(int $stageId): array
    {
    $bands = $this->database->table('stage_bands')
        ->where('stage_id', $stageId)
        ->fetchAll();

    $result = [];
    foreach ($bands as $band) {
        $result[] = $this->database->table('bands')->get($band->band_id);
    }

    return $result;
    }
    public function deleteBand(int $bandId): void
    {
        $this->database->table('stage_bands')->where('band_id', $bandId)->delete();

        $this->database->table('bands')->where('id', $bandId)->delete();
    }
    public function editBand(int $bandId, array $values): void
    {
        $this->database->table('bands')->get($bandId)->update($values);
    }
    public function deleteFestival(int $festivalId): void
    {
        $this->database->table('festivals')->where('id', $festivalId)->delete();
       
    }
 
    public function getStagesByBand(int $bandId): array
    {
        $band = $this->database->table('bands')->get($bandId);
    
        $bands = $this->database->table('bands')
            ->where('name', $band->name)
            ->fetchAll();
    
        $result = [];
        foreach ($bands as $bandEntry) {
            $stages = $this->database->table('stage_bands')
                ->where('band_id', $bandEntry->id)
                ->fetchAll();
    
            foreach ($stages as $stageBand) {
                $stage = $this->database->table('stages')->get($stageBand->stage_id);
    
                if ($stage) {
                    $festival = $this->database->table('festivals')->get($stage->festival_id);
    
                    if ($festival) {
                        $result[] = (object)[
                            'stage_name' => $stage->name,
                            'festival_id' => $festival->id,
                            'festival_name' => $festival->name,
                            'time' => $bandEntry->time
                        ];
                    }
                }
            }
        }
    
        return $result;
    }
}
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
       public function getBandById(int $id): ?ActiveRow
    {
        return $this->database->table('bands')->get($id);
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
    public function getBandsByFestivalWithTimes(int $festivalId): array
    {
        $stages = $this->database->table('stages')
            ->where('festival_id', $festivalId)
            ->fetchAll();
    
        $festival = $this->database->table('festivals')
            ->get($festivalId);
    
        $result = [];
        foreach ($stages as $stage) {
            $stageBands = $this->database->table('stage_bands')
                ->where('stage_id', $stage->id)
                ->fetchAll();
    
            foreach ($stageBands as $stageBand) {
                $band = $stageBand->ref('bands', 'band_id');
                $result[] = (object)[
                    'stage_name' => $stage->name,
                    'time' => $stageBand->time,
                    'band_name' => $band->name,
                    'band_description' => $band->description,
                    'band_id' => $band->id,
                    'festival_id' => $festivalId,
                    'festival_name' => $festival->name // Přidání festival_name
                ];
            }
        }
    
        return $result;
    }
    public function getStageBand(int $stageId, int $bandId)
{
    return $this->database->table('stage_bands')
        ->where('stage_id', $stageId)
        ->where('band_id', $bandId)
        ->fetch();
}

public function updateStageBand(int $stageId, int $bandId, array $data): void
{
    $this->database->table('stage_bands')
        ->where('stage_id', $stageId)
        ->where('band_id', $bandId)
        ->update($data);
}
public function deleteBand(int $bandId): void
{
    $this->database->table('stage_bands')->where('band_id', $bandId)->delete();

    $this->database->table('bands')->where('id', $bandId)->delete();
}

public function editBand(int $bandId, int $stageId, array $values): void
{
    $bandData = [
        'name' => $values['name']
    ];
    $this->database->table('bands')->get($bandId)->update($bandData);

    $stageBandData = [
        'time' => $values['time']
    ];
    $this->database->table('stage_bands')
        ->where('stage_id', $stageId)
        ->where('band_id', $bandId)
        ->update($stageBandData);
}


public function getFestivalsByBand(int $bandId): array
{
    $stages = $this->database->table('stage_bands')
        ->where('band_id', $bandId)
        ->fetchAll();

    $festivals = [];
    foreach ($stages as $stage) {
        // Ensure stage is being properly accessed
        $stageRecord = $this->database->table('stages')->get($stage->stage_id);
        if ($stageRecord) {
            $festival = $this->database->table('festivals')->get($stageRecord->festival_id);
                $festivals[] = $festival;
            
        }
    }

    return $festivals;
}
public function getPerformanceTimes(int $bandId, int $stageId): ?string
{
    $stageBand = $this->database->table('stage_bands')
        ->where('band_id', $bandId)
        ->where('stage_id', $stageId)
        ->fetch();

    return $stageBand ? $stageBand->time : null;
}
public function getBandsList(): array
{
    return $this->database->table('bands')
        ->select('id, name') 
        ->fetchAll(); 
}
    public function addBandToStage(int $bandId, int $stageId, string $time): void
{
    $this->database->table('stage_bands')->insert([
        'band_id' => $bandId,
        'stage_id' => $stageId,
        'time' => $time,
    ]);
}
}
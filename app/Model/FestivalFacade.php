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
    public function getFestivalNameById(int $festivalId): string
    {
        $festival = $this->database->table('festivals')->get($festivalId);
        return $festival ? $festival->name : '';
    }
   

   public function getStagesWithBands(int $festivalId): array // FestivalPresenter - renderDetail
   {
       $stages = $this->database->table('stages')
           ->where('festival_id', $festivalId)
           ->fetchAll();
   
       $result = [];
       foreach ($stages as $stage) {
           $bands = $this->database->table('stage_bands')
               ->where('stage_id', $stage->id)
               ->fetchAll();
   
           $stageBands = [];
           foreach ($bands as $band) {
               $bandInfo = $band->ref('bands', 'band_id');
               $stageBands[] = (object)[
                   'id' => $bandInfo->id,
                   'name' => $bandInfo->name,
                   'description' => $bandInfo->description,
                   'time' => $band->time,
               ];
           }
   
           $result[] = (object)[
               'id' => $stage->id,
               'name' => $stage->name,
               'bands' => $stageBands
           ];
       }
   
       return $result;
   }
    
    public function getStageById(int $stageId)
{
    return $this->database->table('stages')
        ->get($stageId);
}

    public function getBandsByStage(int $stageId): array // FestivalPresenter - renderEditStage
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

    public function deleteFestival(int $festivalId): void
    {
        $this->database->table('festivals')->where('id', $festivalId)->delete();
       
    }

    public function getStagesByFestival(int $festivalId): array
    {
        return $this->database->table('stages')
            ->where('festival_id', $festivalId)
            ->fetchAll(); 
    }

 
}
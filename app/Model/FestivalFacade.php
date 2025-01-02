<?php
namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;
use Tracy\Debugger;


class FestivalFacade
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function addView(int $festivalId): void
    {
      

            $festival = $this->database
            ->table('festivals')
            ->get($festivalId);
    
            $festival->update(['views' => $festival->views + 1]);
    }


    public function getFestivals()
    {
        return $this->database->table('festivals')->fetchAll();
    }
    public function getFestivalById(int $id)
{
    $festival = $this->database->table('festivals')->get($id);

   

    return $festival;
}
 
    public function addFestival(array $values): ActiveRow
    {
        return $this->database->table('festivals')->insert($values);
    }
    public function updateFestival($id, array $values): void
    {
    
        $this->database->table('festivals')->where('id', $id)->update($values);
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
                   'start_time' => $band->start_time,
                   'end_time' => $band->end_time,
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
    public function addImage($festivalId, $imagePath)
    {
        $this->database->table('festival_images')->insert([
            'festival_id' => $festivalId,
            'file_path' => $imagePath,
        ]);
    }

    public function setMainImage(int $festivalId, int $imageId): void
    {
        Debugger::log("Setting main image for festival $festivalId to image $imageId", 'info');

        $this->database->table('festival_images')
            ->where('festival_id', $festivalId)
            ->update(['is_main' => 0]); // Zruší hlavní označení u všech obrázků daného festivalu
    
        $this->database->table('festival_images')
            ->where('id', $imageId)
            ->update(['is_main' => 1]); // Nastaví nový hlavní obrázek
    }

    public function getFestivalImages(int $festivalId): Selection
{
    return $this->database->table('festival_images')
        ->where('festival_id', $festivalId);
}
    public function getFestivalsWithMainImage(): array
{
    $festivals = $this->database->table('festivals');
    $result = [];

    foreach ($festivals as $festival) {
        // Najít hlavní obrázek pro tento festival
        $mainImage = $festival->related('festival_images')
            ->where('is_main', 1)
            ->fetch();

        // Přidat data do výsledného pole
        $result[] = [
            'id' => $festival->id,
            'name' => $festival->name,
            'description' => $festival->description,
            'price' => $festival->price,
            'start_date' => $festival->start_date,
            'end_date' => $festival->end_date,
            'location' => $festival->location,
            'main_image' => $mainImage ? $mainImage->file_path : 'no_image.jpg', // Výchozí obrázek
        ];
    }

    return $result;
}
public function getTopTrendingFestivals(int $limit = 8): array
{
    $festivals = $this->database->table('festivals')
        ->order('views DESC')
        ->limit($limit);

    $result = [];

    foreach ($festivals as $festival) {
        // Najít hlavní obrázek pro tento festival
        $mainImage = $festival->related('festival_images')
            ->where('is_main', 1)
            ->fetch();

        // Přidat data do výsledného pole
        $result[] = [
            'id' => $festival->id,
            'name' => $festival->name,
            'description' => $festival->description,
            'price' => $festival->price,
            'start_date' => $festival->start_date,
            'end_date' => $festival->end_date,
            'location' => $festival->location,
            'main_image' => $mainImage ? $mainImage->file_path : 'no_image.jpg', // Výchozí obrázek
        ];
    }

    return $result;
}
public function deleteImage(int $imageId): void
{
    // Zjistíme, zda je obrázek hlavní
    $image = $this->database->table('festival_images')
        ->get($imageId);

    if ($image && $image->is_main) {
        // Smažeme obrázek
        $this->database->table('festival_images')
            ->where('id', $imageId)
            ->delete();

        // Najdeme náhodný obrázek spojený s festivalem
        $randomImage = $this->database->table('festival_images')
            ->where('festival_id', $image->festival_id)
            ->order('RAND()')
            ->fetch();

        // Pokud existuje náhodný obrázek, nastavíme ho jako hlavní
        if ($randomImage) {
            $this->setMainImage($image->festival_id, $randomImage->id);
        }
    } else {
        // Smažeme obrázek
        $this->database->table('festival_images')
            ->where('id', $imageId)
            ->delete();
    }
}public function deleteStage(int $stageId): void
{
    $this->database->table('stages')
        ->where('id', $stageId)
        ->delete();
}
public function search(string $keyword): array
{
    $festivals = $this->database->table('festivals')
        ->where('name LIKE ?', '%' . $keyword . '%');

    $result = [];

    foreach ($festivals as $festival) {
        $mainImage = $festival->related('festival_images')
            ->where('is_main', 1)
            ->fetch();

        $result[] = [
            'id' => $festival->id,
            'name' => $festival->name,
            'description' => $festival->description,
            'price' => $festival->price,
            'start_date' => $festival->start_date,
            'end_date' => $festival->end_date,
            'location' => $festival->location,
            'main_image' => $mainImage ? $mainImage->file_path : 'no_image.jpg', 
        ];
    }

    return $result;
}
}
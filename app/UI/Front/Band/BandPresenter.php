<?php
namespace App\UI\Front\Band;

use App\Model\FestivalFacade;
use App\Model\BandsFacade;
use Nette;
class BandPresenter extends Nette\Application\UI\Presenter
{
    private $bandsFacade;
    private $festivalFacade;
    public function __construct(BandsFacade $bandsFacade, FestivalFacade $festivalFacade)
    {
        $this->bandsFacade = $bandsFacade;
        $this->festivalFacade = $festivalFacade;
    }
    public function renderBand(int $bandId): void
    {
        // Fetch band details and associated festivals
        $band = $this->bandsFacade->getBandById($bandId);
        $festivals = $this->bandsFacade->getFestivalsByBand($bandId);
        
        // Initialize an empty array to store the results
        $result = [];
        
        foreach ($festivals as $festival) {
            // Fetch stages for each festival
            $stages = $this->festivalFacade->getStagesByFestival($festival->id);
            
            foreach ($stages as $stage) {
                // Fetch performance times for the band at each stage
                $time = $this->bandsFacade->getPerformanceTimes($bandId, $stage->id);
                
                // Only add performance if a time is found
                if ($time) {
                    $performance = [
                        'stage' => $stage->name,
                        'festival_name' => $festival->name,
                        'festival_id' => $festival->id,
                        'time' => $time
                    ];
    
                    // Optionally check for duplicates in the results
                    if (!in_array($performance, $result)) {
                        $result[] = $performance;
                    }
                }
            }
        }
    
        // Pass the data to the template
        $this->template->band = $band;
        $this->template->performances = $result;
    }



}
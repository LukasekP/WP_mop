<?php
namespace App\UI\Front\Band;

use App\Model\FestivalFacade;
use Nette;
class BandPresenter extends Nette\Application\UI\Presenter
{
    private $festivalFacade;

    public function __construct(FestivalFacade $festivalFacade)
    {
        $this->festivalFacade = $festivalFacade;
    }

    public function renderBand(int $bandId): void
    {
        
        $band = $this->festivalFacade->getBandById($bandId);
        $stages = $this->festivalFacade->getStagesByBand($bandId);
    
        $this->template->band = $band;
        $this->template->stages = $stages;
    }



}
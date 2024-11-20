<?php
namespace App\UI\Front\Detail;

use Nette\Application\UI\Form;
use App\Model\FestivalFacade;
use Nette;
class DetailPresenter extends Nette\Application\UI\Presenter
{
    private $festivalFacade;

    public function __construct(FestivalFacade $festivalFacade)
    {
        $this->festivalFacade = $festivalFacade;
    }


    

    public function renderDetail(int $id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
        $images = $this->festivalFacade->getFestivalImages($id);
    
        $this->template->festival = $festival;
        $this->template->images = $images;
        $this->template->stages = $this->festivalFacade->getStagesWithBands($id);
    }
    
}
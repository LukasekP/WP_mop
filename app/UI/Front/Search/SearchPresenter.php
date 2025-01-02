<?php
namespace App\UI\Front\Search;

use App\Model\festivalFacade;
use Nette\Application\UI\Form;
use Nette;
final class SearchPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private FestivalFacade $festivalFacade)
    {
        $this->festivalFacade = $festivalFacade;
     
    }

    public function renderResults(string $keyword): void
    {
        $this->template->keywords = $keyword;

        $this->template->results = $this->festivalFacade->search($keyword);

    }



}
<?php
namespace App\UI\Front\Home;

use App\Model\FestivalFacade;


use Nette;
final class HomePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(
		private FestivalFacade $festivalFacade,
	) {
	}
    public function renderDefault(): void
    {
        $this->template->festivals = $this->festivalFacade->getFestivals();

    }
}

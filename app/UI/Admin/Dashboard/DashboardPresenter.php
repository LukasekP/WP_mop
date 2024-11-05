<?php

declare(strict_types=1);

namespace App\UI\Admin\Dashboard;

use App\UI\Accessory\RequireLoggedUser;
use Nette;
use App\Model\FestivalFacade;


/**
 * Presenter for the dashboard view.
 * Ensures the user is logged in before access.
 */
final class DashboardPresenter extends Nette\Application\UI\Presenter
{
	private $festivalFacade;

    public function __construct(FestivalFacade $festivalFacade)
    {
        $this->festivalFacade = $festivalFacade;
    }
	public function renderDefault(): void
    {
        $this->template->festivals = $this->festivalFacade->getFestivals();
    }
    public function handleDeleteFestival(int $festivalId): void
    {
        $this->festivalFacade->deleteFestival($festivalId);
        $this->flashMessage('Festival úspěšně smazán.', 'success');
        $this->redirect('this');
    }
	
	// Incorporates methods to check user login status
	use RequireLoggedUser;
}

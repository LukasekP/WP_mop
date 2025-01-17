<?php
namespace App\UI\Front\Profile;
use Ublaboo\DataGrid\DataGrid;

use Nette\Application\UI\Form;
use App\Model\OrdersFacade;
use App\Model\UserFacade;
use App\Model\FestivalFacade;

use Nette;

class OrdersPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private OrdersFacade $ordersFacade, private UserFacade $userFacade, private FestivalFacade $festivalFacade)
    {
        $this->ordersFacade = $ordersFacade;
        $this->userFacade = $userFacade;
        $this->festivalFacade = $festivalFacade;
    }
   

    public function renderList(): void
    {
    }


}        


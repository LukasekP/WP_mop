<?php

declare(strict_types=1);

namespace App\UI\Admin\Dashboard;
use Ublaboo\DataGrid\DataGrid;

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
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('bandManager') && !$this->getUser()->isInRole('festivalManager') && !$this->getUser()->isInRole('accountant')) {
            $this->redirect(':Front:Home:default');
        }

        $this->template->festivals = $this->festivalFacade->getFestivals();
    }
   
  
    public function createComponentGrid()
    {
        $grid = new DataGrid();
    
        $grid->setDataSource($this->festivalFacade->getFestivals());
    
        $grid->addColumnNumber('id', 'id')
             ->setSortable();
    
        $grid->addColumnText('name', 'Název')
             ->setTemplateEscaping(false)
             ->setRenderer(function($item) {
                 $link = $this->link('Festival:detail', ['id' => $item->id]);
                 return '<a href="' . $link . '">' . htmlspecialchars($item->name) . '</a>';
             })
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat název');
    
             $grid->addColumnText('description', 'Popisek')
             ->setRenderer(function($item) {
                 $description = strip_tags((string) $item->description);
                 $description = str_replace('&nbsp;', ' ', $description);
                 return Nette\Utils\Strings::truncate($description, 200);
             })
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat popisek');

    
        $grid->addColumnText('start_date', 'Od kdy')
             ->setSortable()
             
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat od kdy'); 
    
        $grid->addColumnText('end_date', 'Do kdy')
             ->setSortable()
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat do kdy');
             
    
        $grid->addColumnText('price', 'Cena')
             ->setSortable()
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat cenu');

    
    if ($this->getUser()->isInRole('admin') || $this->getUser()->isInRole('festivalManager')) {
        $grid->addAction('edit', 'Edit', 'edit!')
             ->setIcon('pencil-alt')
             ->setClass('btn btn-xs btn-primary ajax');  
    
        $grid->addAction('deleteFestival', 'Smazat', 'deleteFestival!')
             ->setClass('btn btn-xs btn-danger ajax');
            }
    
        return $grid;
    }
    public function handleDeleteFestival(int $id): void
    {
        $this->festivalFacade->deleteFestival($id);
        $this->flashMessage('Festival úspěšně smazán.', 'success');
        $this->redirect('this');
    }
    public function handleEdit($id)
    {
        $this->redirect('Festival:editFestival', $id);
    }

	// Incorporates methods to check user login status
	use RequireLoggedUser;
}

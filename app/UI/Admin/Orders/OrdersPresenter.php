<?php
namespace App\UI\Admin\Orders;
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
    protected function createComponentOrdersGrid(string $name): DataGrid
    {
        $grid = new DataGrid($this, $name);

        // Zdrojem dat je tabulka orders
        $grid->setDataSource($this->ordersFacade->getOrders());
        // Sloupce
        $grid->addColumnNumber('id', 'ID')
            ->setSortable();

        $grid->addColumnText('user_name', 'User')
            ->setSortable()
            ->setTemplateEscaping(false)
            ->setRenderer(function ($item) {
                $user = $this->userFacade->getUserById($item->user_id);
                $link = $this->link('User:detail', ['id' => $user->id]);
                return '<a href="' . $link . '">' . htmlspecialchars($user->username) . '</a>';
            })
            ->setFilterText()
            ->setAttribute('placeholder', 'Vyhledat uživatele');
    
        $grid->addColumnText('festival_name', 'Festival')
            ->setSortable()
            ->setTemplateEscaping(false)
            ->setRenderer(function ($item) {
                $festival = $this->festivalFacade->getFestivalById($item->festival_id);
                $link = $this->link('Festival:detail', ['id' => $festival->id]);
                return '<a href="' . $link . '">' . htmlspecialchars($festival->name) . '</a>';
            })
            ->setFilterText()
            ->setAttribute('placeholder', 'Vyhledat festival');

        $grid->addColumnText('variable_symbol', 'Variable Symbol')
            ->setSortable()
            ->setFilterText()
            ->setAttribute('placeholder', 'Vyhledat symbol');

        $grid->addColumnText('status', 'Status')
            ->setSortable()
            ->setFilterText()
            ->setAttribute('placeholder', 'Vyhledat status');

        $grid->addColumnNumber('total_price', 'Cena')
            ->setSortable()
            ->setRenderer(function ($item) {
                return number_format($item->total_price, 2) . ' CZK';
            });

        $grid->addColumnDateTime('created_at', 'Created At')
            ->setSortable()
            ->setFormat('d.m.Y H:i');

        $grid->addColumnDateTime('updated_at', 'Updated At')
            ->setSortable()
            ->setFormat('d.m.Y H:i');

        // Akce (např. pro úpravu objednávky)
        // $grid->addAction('edit', 'Edit', 'editOrder!')
        //     ->setIcon('pencil-alt')
        //     ->setClass('btn btn-sm btn-primary');

        // $grid->addAction('delete', 'Delete', 'deleteOrder!')
        //     ->setIcon('trash')
        //     ->setClass('btn btn-sm btn-danger')
        //     ->setConfirmation(new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Are you sure you want to delete order %s?', 'variable_symbol'));

        return $grid;
    }

}        


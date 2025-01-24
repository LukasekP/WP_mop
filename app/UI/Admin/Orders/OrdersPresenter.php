<?php
namespace App\UI\Admin\Orders;
use Ublaboo\DataGrid\DataGrid;
use App\MailSender\ConfirmMailSender;

use Nette\Application\UI\Form;
use App\Model\OrdersFacade;
use App\Model\UserFacade;
use App\Model\FestivalFacade;

use Nette;

class OrdersPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private OrdersFacade $ordersFacade, 
        private UserFacade $userFacade,
        private FestivalFacade $festivalFacade,
        private ConfirmMailSender $mailSender,)
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

        $grid->addColumnText('firstname', 'Jméno');
        
        $grid->addColumnText('lastname', 'Příjmení');

        $grid->addColumnText('email', 'Email');

        $grid->addColumnText('phone', 'Telefon');
    
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
            ->setRenderer(function ($item) {
                // Zobrazí aktuální stav
                return ucfirst($item->status);
            });
        
            $grid->addAction('changeStatus', 'Změnit stav', 'changeStatus!')
            ->setIcon('edit')
            ->setClass(function ($item) {
                // Pokud je stav 'paid' nebo 'canceled', přidáme třídu 'disabled'
                return ($item->status == 'paid' || $item->status == 'canceled') ? 'btn btn-primary disabled' : 'btn btn-primary';
            });

            $grid->addAction('cancelOrder', 'Zrušit objednávku', 'cancelOrder!')
    ->setIcon('trash')
    ->setRenderer(function ($item) {
        // Pokud je objednávka zrušena, deaktivujeme tlačítko
        if ($item->status == 'canceled') {
            return '<span class="btn btn-danger disabled">Objednávka zrušena</span>';
        }
        
        // Tlačítko pro zrušení objednávky
        return '<a href="' . $this->link('cancelOrder!', ['id' => $item->id]) . '" class="btn btn-danger">Zrušit objednávku</a>';
    });

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
    public function handleChangeStatus(int $id): void
    {
        // Získání objednávky
        $order = $this->ordersFacade->getOrderById($id);
        $festivalName = $this->festivalFacade->getFestivalById($order->festival_id)->name;
        $mail = $this->mailSender->createConfirmEmail($order->id, $order->email, $order->firstname, $order->lastname, $festivalName);
        if (!$order) {
            $this->error('Objednávka nenalezena.');
        }

        // Pokud je stav 'paid' nebo 'canceled', změna stavu není možná
        if ($order->status == 'paid' || $order->status == 'canceled') {
            $this->flashMessage('Stav objednávky nelze změnit.', 'error');
        } else {
            // Změna stavu na nový
            $newStatus = $order->status == 'unpaid' ? 'paid' : 'unpaid'; // Příklad změny stavu
            $this->ordersFacade->updateOrderStatus($id, $newStatus);

            $this->mailSender->sendConfirmEmail($mail);

            $this->flashMessage('Stav objednávky byl změněn.', 'success');
        }

        // Přesměrování zpět
        $this->redirect('this');
    }

    public function handleCancelOrder(int $id): void
    {
        // Získání objednávky
        $order = $this->ordersFacade->getOrderById($id);
        
        if (!$order) {
            $this->error('Objednávka nenalezena.');
        }

        // Zrušení objednávky
        $this->ordersFacade->cancelOrder($id);
        $this->flashMessage('Objednávka byla zrušena.', 'success');

        // Přesměrování zpět
        $this->redirect('this');
    }
}        


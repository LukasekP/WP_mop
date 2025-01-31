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
   

    public function renderDefault(string $order = 'unpaid')
    {
        if (!$this->user->isInRole('admin') && !$this->user->isInRole('accountant')) {
            $this->redirect(':Front:Home:default');
        }
        $this->template->order = $order;

    }
    protected function createComponentUnpaidOrdersGrid(): DataGrid
    {
        return $this->createOrdersGrid('unpaid');
    }
    protected function createComponentCanceledOrdersGrid(): DataGrid
    {
        return $this->createOrdersGrid('canceled');
    }
    
    protected function createComponentPaidOrdersGrid(): DataGrid
    {
        return $this->createOrdersGrid('paid');
    }
    private function createOrdersGrid($order): DataGrid
    {
        $grid = new DataGrid();

        $dataSource = $this->ordersFacade->getOrders()->where('status', $order);
        // Zdrojem dat je tabulka orders
        $grid->setDataSource($dataSource);

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
        
            $grid->addAction('changeStatus', 'Zaplaceno', 'changeStatus!')
            ->setIcon('edit')
            ->setClass(function ($item) {
                return ($item->status == 'paid' || $item->status == 'canceled') ? 'btn btn-primary disabled' : 'btn btn-primary';
            });

            $grid->addAction('cancelOrder', 'Zrušit', 'cancelOrder!')
                ->setIcon('trash')
                ->setRenderer(function ($item) {
                    if ($item->status == 'canceled') {
                        return '<span class="btn btn-danger disabled">Objednávka zrušena</span>';
                    }
                    
                    return '<a href="' . $this->link('cancelOrder!', ['id' => $item->id]) . '" class="btn btn-danger">Zrušit</a>';
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

      

        return $grid;
    }
    public function handleChangeStatus(int $id): void
    {
        $order = $this->ordersFacade->getOrderById($id);
        $festivalName = $this->festivalFacade->getFestivalById($order->festival_id)->name;
        $mail = $this->mailSender->createConfirmEmail($order->id, $order->email, $order->firstname, $order->lastname, $festivalName);
        if (!$order) {
            $this->error('Objednávka nenalezena.');
        }

        if ($order->status == 'paid' || $order->status == 'canceled') {
            $this->flashMessage('Stav objednávky nelze změnit.', 'error');
        } else {
           
            $newStatus = $order->status == 'unpaid' ? 'paid' : 'unpaid'; 
            $this->ordersFacade->updateOrderStatus($id, $newStatus);

            $this->mailSender->sendConfirmEmail($mail);

            $this->flashMessage('Stav objednávky byl změněn.', 'success');
        }


        $this->redirect('this');
    }

    public function handleCancelOrder(int $id): void
    {
        $order = $this->ordersFacade->getOrderById($id);
        
        if (!$order) {
            $this->error('Objednávka nenalezena.');
        }

        $this->ordersFacade->cancelOrder($id);
        $this->flashMessage('Objednávka byla zrušena.', 'success');

        $this->redirect('this');
    }
}        


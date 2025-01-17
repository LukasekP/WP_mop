<?php
namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Database\Table\ActiveRow;


class OrdersFacade
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }
    public function getOrders(): array
    {
    return $this->database->table('orders')->fetchAll();
    }
    public function updateOrderStatus($id, $newStatus): void
    {
        $this->database->table('orders')
            ->where('id', $id)
            ->update(['status' => $newStatus]);
    }
}

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

    public function getOrders()
    {
        return $this->database->table('orders');
    }

    public function getOrderById(int $id)
    {
        return $this->database->table('orders')->get($id);
    }

    public function updateOrderStatus(int $id, string $status): void
    {
        $this->database->table('orders')->where('id', $id)->update(['status' => $status]);
    }
    public function cancelOrder(int $id): void
    {
        $this->updateOrderStatus($id, 'canceled');
    }

    public function getOrdersByUserEmail(string $email)
    {
        return $this->database->table('orders')
            ->where('email', $email)
            ->order('created_at DESC')
            ->fetchAll();
    }
    public function createOrder(array $data): void
    {
        $this->database->table('orders')->insert($data);
    }
}

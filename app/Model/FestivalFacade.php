<?php
namespace App\Model;

use Nette\Database\Context;

class FestivalFacade
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function addFestival(string $name, string $description, string $imagePath, float $price): void
    {
        $this->database->table('festivals')->insert([
            'name' => $name,
            'description' => $description,
            'image' => $imagePath,
            'price' => $price
        ]);
    }
    public function getFestivals(): array
    {
        return $this->database->table('festivals')->fetchAll();
    }
}
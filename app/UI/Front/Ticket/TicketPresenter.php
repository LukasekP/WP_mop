<?php

namespace App\UI\Front\Ticket;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use App\MailSender\PurchaseMailSender;
use Nette\Database\Explorer;

class TicketPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PurchaseMailSender $mailSender,
        private Explorer $database,
        private User $user,
    ) {
    }


    /**
     * Formulář pro nákup vstupenky
     */
    public function renderDefault(int $id): void
    {
        // Načtení informací o festivalu z databáze
        $festival = $this->database->table('festivals')->get($id);

        if (!$festival) {
            $this->error('Festival nenalezen.');
        }

        // Předání dat do šablony
        $this->template->festival = $festival;
    }

    protected function createComponentTicketPurchaseForm(): Form
    {
        $form = new Form;

        $form->addText('firstname', 'Jméno:')
            ->setRequired('Zadejte prosím své jméno.');

        $form->addText('lastname', 'Příjmení:')
            ->setRequired('Zadejte prosím své příjmení.');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Zadejte prosím svůj e-mail.');

        $form->addText('phone', 'Telefon:')
            ->setRequired('Zadejte prosím svůj telefon.');

        $form->addHidden('festival_id', $this->getParameter('id') ?? null)
            ->setRequired('Festival ID chybí.');

        // Přidání ceny festivalu z načtených dat


        $form->addSubmit('submit', 'Koupit vstupenku');

        // Předvyplnění formuláře, pokud je uživatel přihlášen
        if ($this->user->isLoggedIn()) {
            $identity = $this->user->getIdentity();
            $form->setDefaults([
                'firstname' => $identity->firstname ?? '',
                'lastname' => $identity->lastname ?? '',
                'email' => $identity->email ?? '',
                'phone' => $identity->phone ?? '',
            ]);
        }

        $form->onSuccess[] = [$this, 'processTicketPurchase'];

        return $form;
    }

    public function processTicketPurchase(Form $form, \stdClass $values): void
    {
        // Generování variabilního symbolu
        $variableCode = random_int(10000000, 99999999);

        // Uložení objednávky do databáze
        $this->database->table('orders')->insert([
            'firstname' => $values->firstname,
            'lastname' => $values->lastname,
            'email' => $values->email,
            'phone' => $values->phone,
            'festival_id' => $values->festival_id,
            'variable_symbol' => $variableCode,
            'total_price' => 10,
        ]);
		$mail = $this->mailSender->createPurchaseEmail($values->email, $values->firstname, $values->lastname, $variableCode);

        // Odeslání e-mailu uživateli
     
        $this->mailSender->sendPurchaseEmail($mail);
        // Přesměrování na potvrzovací stránku
        $this->redirect('Ticket:confirmation');
    }
    /**
     * Akce pro potvrzovací stránku
     */
    public function renderConfirmation(): void
    {
        // Obsah potvrzovací stránky
    }
}
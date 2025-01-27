<?php

namespace App\UI\Front\Ticket;
use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use App\MailSender\PurchaseMailSender;
use App\Model\FestivalFacade;
use App\Model\OrdersFacade;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Nette\Application\LinkGenerator;


class TicketPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PurchaseMailSender $mailSender,
        private User $user,
        private FestivalFacade $festivalFacade,
        private OrdersFacade $ordersFacade,
        private LinkGenerator $linkGenerator
    ) {
        $this->festivalFacade = $festivalFacade;
        $this->ordersFacade = $ordersFacade;
        $this->linkGenerator = $linkGenerator;
    }


    /**
     * Formulář pro nákup vstupenky
     */
    public function renderDefault(int $id, int $quantity = 1): void
    {
        // Načtení informací o festivalu z databáze
        $festival = $this->festivalFacade->getFestivalById($id);
    
        if (!$festival) {
            $this->error('Festival nenalezen.');
        }
    
        // Předání dat do šablony
        $this->template->festival = $festival;
        $this->template->quantity = $quantity; // Počet vstupenek
        $this->template->totalPrice = $festival->price * $quantity; // Celková cena
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
    
        $form->addInteger('quantity', 'Počet vstupenek:')
            ->setDefaultValue($this->getParameter('quantity') ?? 1)
            ->addRule($form::INTEGER, 'Zadejte platný počet vstupenek.')
            ->addRule($form::MIN, 'Minimální počet vstupenek je 1.', 1)
            ->addRule($form::MAX, 'Maximální počet vstupenek je 10.', 10)
            ->setRequired('Zadejte počet vstupenek.');
    
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
        // Načtení ceny festivalu
        $festival = $this->festivalFacade->getFestivalById($values->festival_id);
    
        if (!$festival) {
            $this->error('Festival nenalezen.');
        }
    
        // Výpočet celkové ceny
        $totalPrice = $festival->price * $values->quantity;

        // Generování variabilního symbolu
        $variableCode = random_int(10000000, 99999999);

       


        // Uložení objednávky do databáze
        $this->ordersFacade->createOrder([
            'firstname' => $values->firstname,
            'lastname' => $values->lastname,
            'email' => $values->email,
            'phone' => $values->phone,
            'festival_id' => $values->festival_id,
            'quantity' => $values->quantity, // Ukládáme počet vstupenek
            'total_price' => $totalPrice, // Ukládáme celkovou cenu
            'variable_symbol' => $variableCode,
        ]);

    // Vytvoření a odeslání e-mailu
    $mail = $this->mailSender->createPurchaseEmail(
        $values->email,
        $values->firstname,
        $values->lastname,
        $variableCode,
        $totalPrice,
        
        
    );

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
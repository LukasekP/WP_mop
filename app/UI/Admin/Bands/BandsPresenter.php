<?php
namespace App\UI\Admin\Bands;

use Nette\Application\UI\Form;
use App\Model\BandsFacade;
use Nette;
class BandsPresenter extends Nette\Application\UI\Presenter
{
    private $bandsFacade;

    public function __construct(BandsFacade $bandsFacade)
    {
        $this->bandsFacade = $bandsFacade;
    }
    public function renderList(): void
    {
        $this->template->bands = $this->bandsFacade->getAllBands();
    }
    protected function createComponentAddBandForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Název kapely:')
            ->setRequired('Prosím, zadejte název kapely.');
        $form->addText('description', 'Popis kapely:')
            ->setRequired('Prosím, zadejte popis kapely.');
        $form->addSubmit('submit', 'Přidat kapelu');
        $form->onSuccess[] = [$this, 'addBandFormSucceeded'];
        return $form;
    }

    public function addBandFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->bandsFacade->addBand($values->name, $values->description);
       // $this->bandsFacade->assignBandToStage((int)$values->stage_id, $this->bandsFacade->getLastInsertedBandId());
        $this->flashMessage('Kapela byla úspěšně přidána.', 'success');
        $this->redirect('this');
    }


}
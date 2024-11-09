<?php
namespace App\UI\Admin\Bands;

use Nette\Application\UI\Form;
use App\Model\BandsFacade;
use App\Model\FestivalFacade;
use Nette;
class BandsPresenter extends Nette\Application\UI\Presenter
{
    private BandsFacade $bandsFacade;
    private FestivalFacade $festivalFacade;
    public function __construct(BandsFacade $bandsFacade, FestivalFacade $festivalFacade)
    {
        $this->bandsFacade = $bandsFacade;
        $this->festivalFacade = $festivalFacade;
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

    public function renderEditBand(int $bandId, int $festivalId, int $stageId): void
    {
        $band = $this->bandsFacade->getBandById($bandId);
        $stageBand = $this->bandsFacade->getStageBand($stageId, $bandId);

        $defaults = $band->toArray();
        $defaults['time'] = $stageBand->time;

        $this->getComponent('editBandForm')
             ->setDefaults($defaults);
    }

    public function createComponentEditBandForm(){
        $form = new Form;
        $form->addText('name', 'Název kapely:')
            ->setRequired('Prosím, zadejte název kapely.');
        $form->addText('time', 'Čas vystoupení:')
            ->setRequired('Prosím, zadejte čas vystoupení.');
        $form->addSubmit('submit', 'Uložit');
        $form->onSuccess[] = [$this, 'editBandFormSucceeded'];
        return $form;
    }      public function editBandFormSucceeded(Form $form, \stdClass $values): void
    {
        $bandId = $this->getParameter('bandId');
        $stageId = $this->getParameter('stageId');

        $this->bandsFacade->editBand($bandId, $stageId, (array)$values);

        $this->flashMessage('Kapela byla úspěšně upravena.', 'success');
        $this->redirect('Festival:editStage' , $this->getParameter('festivalId'), $this->getParameter('stageId'));    }
    


}        

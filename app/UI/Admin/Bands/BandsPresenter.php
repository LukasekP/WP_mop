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

    public function renderEditBand(int $festivalId, int $stageId,int $bandId): void
    {
        $band = $this->bandsFacade->getBandById($bandId);
        $stageBand = $this->bandsFacade->getStageBand($stageId, $bandId);

        $defaults = $band->toArray();
        $defaults['time'] = $stageBand->time;

        $this->getComponent('editBandForm')
             ->setDefaults($defaults);
    }

    public function renderAddBand(int $festivalId, int $stageId): void
{
    // Get the stage information using the stage ID
    $stage = $this->festivalFacade->getStageById($stageId);
    $this->template->stage = $stage;

    // Get the list of bands to be displayed in the form
    $bands = $this->bandsFacade->getAllBands();
    $this->template->bands = $bands;

    // Pass the festival ID and stage ID to the template
    $this->template->festivalId = $festivalId;
    $this->template->stageId = $stageId;
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
    }      
    
    public function editBandFormSucceeded(Form $form, \stdClass $values): void
    {
        $bandId = $this->getParameter('bandId');
        $stageId = $this->getParameter('stageId');

        $this->bandsFacade->editBand($bandId, $stageId, (array)$values);

        $this->flashMessage('Kapela byla úspěšně upravena.', 'success');
        $this->redirect('Festival:editStage' , $this->getParameter('festivalId'), $this->getParameter('stageId'));    }
    

        protected function createComponentAddBandToStageForm(): Nette\Application\UI\Form
        {
            $form = new Nette\Application\UI\Form;
        
            $form->addSelect('band', 'Vyberte kapelu:', $this->getBandsList())
                ->setPrompt('Vyberte kapelu')
                ->setRequired('Vyberte kapelu.');
        
            $form->addText('time_from', 'Čas od:')
                ->setRequired('Zadejte čas od.');
        
            $form->addText('time_to', 'Čas do:')
                ->setRequired('Zadejte čas do.');
        
            $form->addSubmit('submit', 'Přidat kapelu');
        
            $form->onSuccess[] = [$this, 'addBandFormSucceeded'];
        
            return $form;
        }
        public function addBandToStageFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void
{
    $stageId = $this->getParameter('stageId');
    $festivalId = $this->getParameter('festivalId');

    // Combine the time input into a single string
    $time = $values->time_from . ' - ' . $values->time_to;

    // Add the band to the stage
    $this->bandsFacade->addBandToStage($values->band, $stageId, $time);

    $this->flashMessage('Kapela byla úspěšně přidána.', 'success');
    $this->redirect('Festival:editStage', $festivalId, $stageId);
}
protected function getBandsList(): array
{
    // Use BandsFacade to get the list of bands
    $bands = $this->bandsFacade->getBandsList(); 

    // You can modify the format of the list if needed
    $bandList = [];
    foreach ($bands as $band) {
        $bandList[$band->id] = $band->name; // Replace 'id' and 'name' if necessary
    }

    return $bandList;
}
}        

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
        $this->flashMessage('Kapela byla úspěšně přidána.', 'success');
        $this->redirect('this');
    }

    public function renderEditBand(int $festivalId, int $stageId, int $bandId): void
    {
        $band = $this->bandsFacade->getBandById($bandId);
        $stageBand = $this->bandsFacade->getStageBand($stageId, $bandId);
    
        $defaults = $stageBand->toArray();
        $defaults['band'] = $band->id;
    
        $this->getComponent('editBandToStageForm')
             ->setDefaults($defaults);
    
        $this->template->festivalId = $festivalId;
        $this->template->stageId = $stageId;
        $this->template->bandId = $bandId;
    }

    public function renderAddBand(int $festivalId, int $stageId): void
{
    $stage = $this->festivalFacade->getStageById($stageId);
    $this->template->stage = $stage;

    $bands = $this->bandsFacade->getAllBands();
    $this->template->bands = $bands;

    $this->template->festivalId = $festivalId;
    $this->template->stageId = $stageId;
}



    

        protected function createComponentAddBandToStageForm(): Nette\Application\UI\Form
        {
            $form = new Nette\Application\UI\Form;
        
            $form->addSelect('band', 'Vyberte kapelu:', $this->getBandsList())
                ->setPrompt('Vyberte kapelu')
                ->setRequired('Vyberte kapelu.');
        
            $form->addText('start_time', 'Čas od:')
                 ->setHtmlAttribute('type', 'time')
                 ->setRequired('Zadejte čas od.');
        
            $form->addText('end_time', 'Čas do:')
                 ->setHtmlAttribute('type', 'time')
                 ->setRequired('Zadejte čas do.');
        
            $form->addSubmit('submit', 'Odeslat');
        
            $form->onSuccess[] = [$this, 'addBandToStageFormSucceeded'];
        
            return $form;
        }
        public function addBandToStageFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void
        {
            $festivalId = $this->getParameter('festivalId');
            $stageId = $this->getParameter('stageId');
            $bandId = $values->band;
  
            $this->bandsFacade->addBandToStage($bandId, $stageId, (array)$values);
            $this->flashMessage('Kapela byla úspěšně přidána.', 'success');
            $this->redirect('Stage:editStage', $festivalId, $stageId);
        }


        protected function createComponentEditBandToStageForm(): Nette\Application\UI\Form
        {
            $form = new Nette\Application\UI\Form;
        
            $form->addSelect('band', 'Vyberte kapelu:', $this->getBandsList())
                ->setPrompt('Vyberte kapelu')
                ->setRequired('Vyberte kapelu.');
        
            $form->addText('start_time', 'Čas od:')
                 ->setHtmlAttribute('type', 'time')
                 ->setRequired('Zadejte čas od.');
        
            $form->addText('end_time', 'Čas do:')
                 ->setHtmlAttribute('type', 'time')
                 ->setRequired('Zadejte čas do.');
        
            $form->addSubmit('submit', 'Odeslat');
        
            $form->onSuccess[] = [$this, 'editBandToStageFormSucceeded'];
        
            return $form;
        }
        public function editBandToStageFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void
        {
            $festivalId = $this->getParameter('festivalId');
            $stageId = $this->getParameter('stageId');
            $originalBandId = $this->getParameter('bandId'); 

            $this->bandsFacade->editBand($stageId, $originalBandId, [
                'band' => $values->band,
                'start_time' => $values->start_time,
                'end_time' => $values->end_time,
            ]);

            $this->flashMessage('Kapela byla úspěšně upravena.', 'success');
            $this->redirect('Stage:editStage', $festivalId, $stageId);
        }


        protected function getBandsList(): array
        {
            $bands = $this->bandsFacade->getBandsList();
        
            $bandList = [];
            foreach ($bands as $band) {
                $bandList[$band->id] = $band->name;
            }
        
            return $bandList;
        }
}        


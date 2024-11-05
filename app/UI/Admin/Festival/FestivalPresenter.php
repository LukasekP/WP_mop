<?php
namespace App\UI\Admin\Festival;

use Nette\Application\UI\Form;
use App\Model\FestivalFacade;
use Nette;
class FestivalPresenter extends Nette\Application\UI\Presenter
{
    private $festivalFacade;

    public function __construct(FestivalFacade $festivalFacade)
    {
        $this->festivalFacade = $festivalFacade;
    }




    // Rendery

    public function renderDetail(int $id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
        
        $this->template->festival = $festival;
        $this->template->stages = $this->festivalFacade->getStagesWithBands($id);
    }
    public function renderAddStage(int $festivalId): void
    {
        $festival = $this->festivalFacade->getFestivalById($festivalId);
      
        $this->template->festival = $festival;
    }
    public function renderEditStage(int $festivalId, int $stageId): void
    {
        $festival = $this->festivalFacade->getFestivalById($festivalId);
        $stage = $this->festivalFacade->getStageById($stageId);
       
        $this->template->stage = $stage;
        $this->template->bands = $this->festivalFacade->getBandsByStage($stageId);
      
        $this->template->festival = $festival;
    }
    public function actionEditBand(int $bandId,int $festivalId, int $stageId): void
    {
        $band = $this->festivalFacade->getBandById($bandId);
        $this->getComponent('editBandForm')->setDefaults($band->toArray());
    }



    protected function createComponentAddFestivalForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Název festivalu:')
            ->setRequired('Zadejte název festivalu');
        $form->addTextArea('description', 'Popis festivalu:')
            ->setRequired('Zadejte popis');
        $form->addText('price', 'Cena vstupenky:')
            ->setRequired('Zadejte cenu');
        $form->addUpload('image', 'Obrázek festivalu:')
            ->setRequired('Nahrajte obrázek');
        $form->addSubmit('submit', 'Přidat festival');

        $form->onSuccess[] = [$this, 'addFestivalSucceeded'];
        return $form;
    }

    public function addFestivalSucceeded(Form $form, $values): void
    {
        $image = $values->image;
        $imagePath = 'uploads/' . $image->getSanitizedName();
        $image->move($imagePath);
        $this->festivalFacade->addFestival((array) $values);
        $this->flashMessage('Festival byl přidán.', 'success');
        $this->redirect('Dashboard:default');
    }

    protected function createComponentAddStageForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Název stage:')
            ->setRequired('Prosím, zadejte název stage.');
        $form->addHidden('festival_id', $this->getParameter('festivalId'));
        $form->addSubmit('submit', 'Přidat stage');
        $form->onSuccess[] = [$this, 'addStageFormSucceeded'];
        return $form;
    }

    public function addStageFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->festivalFacade->addStage($values->festival_id, $values->name);
        $this->flashMessage('Stage byla úspěšně přidána.', 'success');
        $this->redirect('detail', $values->festival_id);
    }

    protected function createComponentAddBandForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Název kapely:')
            ->setRequired('Prosím, zadejte název kapely.');
        $form->addText('time', 'Čas vystoupení:')
            ->setRequired('Prosím, zadejte čas vystoupení.');
        $form->addSelect('stage_id', 'Stage:', $this->getStages())
            ->setRequired('Prosím, vyberte stage.');
        $form->addSubmit('submit', 'Přidat kapelu');
        $form->onSuccess[] = [$this, 'addBandFormSucceeded'];
        return $form;
    }

    public function addBandFormSucceeded(Form $form, \stdClass $values): void
    {
        $this->festivalFacade->addBand($values->name, $values->time);
        $this->festivalFacade->assignBandToStage((int)$values->stage_id, $this->festivalFacade->getLastInsertedBandId());
        $this->flashMessage('Kapela byla úspěšně přidána.', 'success');
        $this->redirect('this', $this->getParameter('festivalId'));
    }
    private function getStages(): array
    {
        $festivalId = $this->getParameter('festivalId');
        $stages = $this->festivalFacade->getStagesByFestival($festivalId);
        $stageOptions = [];
        foreach ($stages as $stage) {
            $stageOptions[$stage->id] = $stage->name;
        }
        return $stageOptions;
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
    }    public function editBandFormSucceeded(Form $form, \stdClass $values): void
    {
        $bandId = $this->getParameter('bandId');
        $this->festivalFacade->editBand($bandId, (array)$values);
        $this->flashMessage('Kapela byla úspěšně upravena.', 'success');
        $this->redirect('editStage' , $this->getParameter('festivalId'), $this->getParameter('stageId'));
    }
    

    public function handleDeleteBand(int $bandId): void
    {
        $this->festivalFacade->deleteBand($bandId);
        $this->flashMessage('Kapela byla úspěšně smazána.', 'success');
        $this->redirect('this');
    }
}
    

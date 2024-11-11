<?php
namespace App\UI\Admin\Festival;

use Nette\Application\UI\Form;
use App\Model\FestivalFacade;
use App\Model\BandsFacade;

use Nette;
class FestivalPresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private FestivalFacade $festivalFacade, private BandsFacade $BandsFacade)
    {
        $this->festivalFacade = $festivalFacade;
        $this->BandsFacade = $BandsFacade;
    }
    




    // Rendery

    public function renderDetail(int $id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
    
        $this->template->festival = $festival;
        $this->template->stages = $this->festivalFacade->getStagesWithBands($id);
        $this->template->bands = $this->BandsFacade->getBandsByFestivalWithTimes($id); 
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
        $this->template->bands = $this->BandsFacade->getBandsByStageWithTimes($stageId);
        $this->template->festival = $festival;
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

   


    public function handleDeleteBand(int $bandId): void
    {
        $this->festivalFacade->deleteBand($bandId);
        $this->flashMessage('Kapela byla úspěšně smazána.', 'success');
        $this->redirect('this');
    }

}
    

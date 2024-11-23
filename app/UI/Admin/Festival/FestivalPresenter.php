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


    public function renderEditFestival($id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
    
        $this->getComponent('addFestivalForm')
            ->setDefaults($festival->toArray());
    }
 


    protected function createComponentAddFestivalForm(): Form
    {
        $form = new Form;
    
        $form->addText('name', 'Název festivalu:')
            ->setRequired('Zadejte název festivalu')
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addTextArea('description', 'Popis festivalu:')
            ->setRequired('Zadejte popis festivalu')
            ->setHtmlAttribute('class', 'form-control');
        
            $form->addText('start_date', 'Od kdy:')
            ->setRequired('Zadejte počáteční datum')
            ->addRule($form::PATTERN, 'Zadejte platné datum ve formátu YYYY-MM-DD', '\d{4}-\d{2}-\d{2}')
            ->setHtmlAttribute('type', 'date')
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addText('end_date', 'Do kdy:')
            ->setRequired('Zadejte koncové datum')
            ->addRule($form::PATTERN, 'Zadejte platné datum ve formátu YYYY-MM-DD', '\d{4}-\d{2}-\d{2}')
            ->setHtmlAttribute('type', 'date')
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addText('price', 'Cena vstupenky:')
            ->setRequired('Zadejte cenu vstupenky')
            ->addRule($form::FLOAT, 'Cena musí být číslo')
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addUpload('image', 'Obrázek k festivalu:')
            
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addSubmit('submit', 'Odeslat')
            ->setHtmlAttribute('class', 'btn btn-primary mt-3');
    
        $form->onSuccess[] = [$this, 'addFestivalFormSucceeded'];
        return $form;
    }
    

    public function addFestivalFormSucceeded(Form $form, \stdClass $values): void
    {
       $id = $this->getParameter('id');
    
        if ($values->image->isOk()) {
            $values->image->move("upload/" . $values->image->getSanitizedName());
            $values->image = "upload/" . $values->image->getSanitizedName();
        }  else {
                $this->flashMessage("Obrázek nebyl přidán", "failed");
                $this->redirect('this');
            }
        
    
        if ($id) {
            $this->festivalFacade->updateFestival($id,(array)$values);
            $this->flashMessage('Festival byl úspěšně aktualizován.', 'success');
        } else {
            $this->festivalFacade->addFestival((array)$values);
            $this->flashMessage('Festival byl úspěšně přidán.', 'success');
        }
    
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
    

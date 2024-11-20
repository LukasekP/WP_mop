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
        // Získání festivalu
        $festival = $this->festivalFacade->getFestivalById($id);
    
        // Získání obrázků pro tento festival
        $images = $this->festivalFacade->getFestivalImages($id);
    
        // Předání dat do šablony
        $this->template->festival = $festival;
        $this->template->images = $images; // Předání obrázků jako samostatné proměnné
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
    
        // Název festivalu
        $form->addText('name', 'Název festivalu:')
            ->setRequired('Zadejte název festivalu')
            ->setHtmlAttribute('class', 'form-control');
    
        // Popis festivalu
        $form->addTextArea('description', 'Popis festivalu:')
            ->setRequired('Zadejte popis festivalu')
            ->setHtmlAttribute('class', 'form-control');
    
        // Cena vstupenky
        $form->addText('price', 'Cena vstupenky:')
            ->setRequired('Zadejte cenu vstupenky')
            ->addRule($form::FLOAT, 'Cena musí být číslo')
            ->setHtmlAttribute('class', 'form-control');
    
        // Obrázky festivalu
        $form->addMultiUpload('images', 'Obrázky festivalu:')
            ->setRequired('Vyberte alespoň jeden obrázek')
            ->setHtmlAttribute('class', 'form-control');
    
        // Tlačítko odeslat
        $form->addSubmit('submit', 'Přidat festival')
            ->setHtmlAttribute('class', 'btn btn-primary mt-3');
    
        $form->onSuccess[] = [$this, 'addFestivalSucceeded'];
        return $form;
    }
    

    public function addFestivalSucceeded(Form $form, $values): void
    {
        // Přidání festivalu
        $festival = $this->festivalFacade->addFestival([
            'name' => $values->name,
            'description' => $values->description,
            'price' => $values->price,
        ]);
        
        // Pokud existují obrázky
        if (!empty($values->images)) {
            foreach ($values->images as $image) {
                if ($image->isOk()) {
                    $imagePath = 'uploads/' . $image->getSanitizedName();
                    $image->move($imagePath);
    
                    // Přidání obrázku do databáze
                    $this->festivalFacade->addFestivalImage($festival->id, $imagePath);
                }
            }
        }
    
        $this->flashMessage('Festival byl přidán včetně obrázků.', 'success');
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
    

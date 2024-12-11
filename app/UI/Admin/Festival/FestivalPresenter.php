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
    public function renderEditFestival($id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
    
        $this->getComponent('addFestivalForm')
            ->setDefaults($festival->toArray());
    }
    public function renderMainImage(){
        
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
    
        $form->addMultiUpload('images', 'Obrázek k festivalu:')
            
            ->setHtmlAttribute('class', 'form-control');
    
        $form->addSubmit('submit', 'Odeslat')
            ->setHtmlAttribute('class', 'btn btn-primary mt-3');
    
        $form->onSuccess[] = [$this, 'addFestivalFormSucceeded'];
        return $form;
    }
    

    public function addFestivalFormSucceeded(Form $form, \stdClass $values): void
    {
        $id = $this->getParameter('id');
    
        if ($id) {
            $this->festivalFacade->updateFestival($id, (array)$values);
            $this->flashMessage('Festival byl úspěšně aktualizován.', 'success');
            $festivalId = $id;
        } else {
            // Odstranění obrázků z $values
            $images = $values->images;
            unset($values->images);
    
            // Uložení základních informací o festivalu a získání ID
            $festivalId = $this->festivalFacade->addFestival((array)$values);
            $this->flashMessage('Festival byl úspěšně přidán.', 'success');
        }
    
        // Uložení obrázků s nově získaným festivalId
        if (!empty($images)) {
            foreach ($images as $image) {
                if ($image->isOk()) {
                    $image->move("upload/" . $image->getSanitizedName());
                    $imagePath = "upload/" . $image->getSanitizedName();
                    
                    // Uložení cesty k obrázku do databáze
                    $this->festivalFacade->addImage($festivalId, $imagePath);
                } else {
                    $this->flashMessage("Obrázek {$image->getName()} nebyl přidán", "failed");
                }
            }
        } else {
            $this->flashMessage("Žádné obrázky nebyly nahrány", "failed");
        }
    
        $this->redirect('Dashboard:default');
    }
    
    public function handleDeleteBand(int $stageId, int $bandId): void
    {
        $this->BandsFacade->deleteBand($stageId, $bandId);
        $this->flashMessage('Kapela byla úspěšně smazána.', 'success');
        $this->redirect('this');
    }

}
?>
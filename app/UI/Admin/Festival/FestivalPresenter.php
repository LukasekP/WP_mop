<?php
namespace App\UI\Admin\Festival;

use Nette\Application\UI\Form;
use App\Model\FestivalFacade;
use App\Model\BandsFacade;
use Tracy\Debugger;

use Nette;
class FestivalPresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private FestivalFacade $festivalFacade, private BandsFacade $BandsFacade)
    {
        $this->festivalFacade = $festivalFacade;
        $this->BandsFacade = $BandsFacade;
    }
    
    public function actionAdd(): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('festivalManager')) {
            $this->redirect(':Front:Home:default');
        }
    }
    public function actionDetail(int $id): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('bandManager') && !$this->getUser()->isInRole('festivalManager') && !$this->getUser()->isInRole('accountant')) {
            $this->redirect(':Front:Home:default');
        }
    }

    public function renderDetail(int $id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
        $images = $this->festivalFacade->getFestivalImages($id);

        $this->template->images = $images;
        $this->template->festival = $festival;
        $this->template->stages = $this->festivalFacade->getStagesWithBands($id);
        $this->template->bands = $this->BandsFacade->getBandsByFestivalWithTimes($id);
    }
    public function actionEditFestival(int $id): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('festivalManager')) {
            $this->redirect(':Front:Home:default');
        }
    }
    public function renderEditFestival($id): void
    {
        $festival = $this->festivalFacade->getFestivalById($id);
        $images = $this->festivalFacade->getFestivalImages($id); 

        $this->template->festival = $festival;
        $this->template->images = $images; 
        $this->getComponent('addFestivalForm')
            ->setDefaults($festival->toArray());
    }
    public function actionMainImage(int $id): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('festivalManager')) {
            $this->redirect(':Front:Home:default');
        }
    }
    public function renderMainImage(int $id): void
    {

    $festivalImages = $this->festivalFacade->getFestivalImages($id);

    Debugger::log("Festival Images: " . json_encode($festivalImages->fetchAll()), 'info');
    Debugger::log("Festival ID: $id", 'info');

    $this->template->images = $festivalImages;
    $this->template->festivalId = $id;
    }


    protected function createComponentAddFestivalForm(): Form
    {   
        $form = new Form;
    
        $form->addText('name', 'Název festivalu:')
            ->setHtmlAttribute('autocomplete', 'off')
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
        
        $form->addText('location', 'Místo konání:')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setRequired('Zadejte místo konání')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('price', 'Cena vstupenky:')
            ->setHtmlAttribute('autocomplete', 'off')
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
        $images = $values->images;
        unset($values->images);
    
        if ($id) {
            $this->festivalFacade->updateFestival($id, (array)$values);
            $this->flashMessage('Festival byl úspěšně aktualizován.', 'success');
            $festivalId = $id;
        } else {
            $festival = $this->festivalFacade->addFestival((array)$values);
            $this->flashMessage('Festival byl úspěšně přidán.', 'success');
            $festivalId = $festival->id; 
        }
    
        $hasImages = false;
    
        if (!empty($images) && $images[0]->isOk()) {
            foreach ($images as $image) {
                if ($image->isOk()) {
                    $image->move("upload/" . $image->getSanitizedName());
                    $imagePath = "upload/" . $image->getSanitizedName();
                    
                    $this->festivalFacade->addImage($festivalId, $imagePath);
                    $hasImages = true;
                } else {
                    $this->flashMessage("Obrázek {$image->getName()} nebyl přidán", "failed");
                }
            }
        }
    
        if ($hasImages) {
            $this->redirect('Festival:mainImage', $festivalId);
        } else {
            $this->redirect('Dashboard:default');
        }
    }
    
    public function handleDeleteBand(int $stageId, int $bandId): void
    {
        $this->BandsFacade->deleteBand($stageId, $bandId);
        $this->flashMessage('Kapela byla úspěšně smazána.', 'success');
        $this->redirect('this');
    }


    protected function createComponentSetMainImageForm(): Form 
    {
        $form = new Form;
    
        $festivalId = $this->getParameter('id');
        $images = $this->festivalFacade->getFestivalImages($festivalId);
    
        $imageOptions = [];
        foreach ($images as $image) {
            $imageOptions[$image->id] = $image->file_path; 
        }
    
        $form->addHidden('festivalId', $festivalId)
            ->setRequired();
    
        $form->addRadioList('mainImage', 'Hlavní obrázek', $imageOptions)
            ->setRequired('Vyberte hlavní obrázek');
    
        $form->addSubmit('submit', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-primary mt-3');
    
        $form->onSuccess[] = [$this, 'setMainImageFormSucceeded'];
        return $form;
    }

    public function setMainImageFormSucceeded(Form $form, \stdClass $values): void
    {
        $festivalId = $values->festivalId;
        $mainImageId = $values->mainImage;
    
        $this->festivalFacade->setMainImage($festivalId, $mainImageId);
    
        $this->flashMessage('Hlavní obrázek byl nastaven.', 'success');
        $this->redirect('Dashboard:default');
    }
    public function handleDeleteImage($imageId): void
    {
        $this->festivalFacade->deleteImage($imageId);
        $this->flashMessage('Obrázek byl úspěšně smazán.', 'success');
        $this->redirect('this');
    }
    public function handleMainImage($imageId): void
    {
        $festivalId = $this->getParameter('id');
        $this->festivalFacade->setMainImage($festivalId, $imageId);
        $this->flashMessage('Hlavní obrázek byl úspěšně nastaven.', 'success');
        $this->redirect('this');
    }
    public function handleDeleteStage(int $stageId): void
    {
        $this->festivalFacade->deleteStage($stageId);
        $this->flashMessage('Stage byla úspěšně smazána.', 'success');
        $this->redirect('this');
    }
}
?>
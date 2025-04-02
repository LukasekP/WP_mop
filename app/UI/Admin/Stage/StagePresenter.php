<?php
namespace App\UI\Admin\Stage;

use Nette\Application\UI\Form;
use App\Model\FestivalFacade;
use App\Model\BandsFacade;

use Nette;
class StagePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private FestivalFacade $festivalFacade, private BandsFacade $BandsFacade)
    {
        $this->festivalFacade = $festivalFacade;
        $this->BandsFacade = $BandsFacade;
    }
    public function actionEditStage(int $festivalId, int $stageId): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('festivalManager')) {
            $this->redirect(':Front:Home:default');
        }
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
    public function actionAddStage(int $festivalId): void
    {
        if (!$this->getUser()->isInRole('admin') && !$this->getUser()->isInRole('festivalManager')) {
            $this->redirect(':Front:Home:default');
        }
    }
    public function renderAddStage(int $festivalId): void
    {
        $festival = $this->festivalFacade->getFestivalById($festivalId);
      
        $this->template->festival = $festival;
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
        $this->redirect('Festival:detail', $values->festival_id);
    }

    public function handleDeleteBand(int $stageId, int $bandId): void
    {
        $this->BandsFacade->deleteBand($stageId, $bandId);
        $this->flashMessage('Kapela byla úspěšně smazána.', 'danger');
        $this->redirect('this');
    }
}
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

    protected function createComponentAddFestivalForm(): Form
    {
        $form = new Form;
        $form->addText('name', 'Název festivalu:')
            ->setRequired('Prosím, zadejte název festivalu.');
        $form->addTextArea('description', 'Popis:')
            ->setRequired('Prosím, zadejte popis festivalu.');
        $form->addUpload('image', 'Obrázek:')
            ->setRequired('Prosím, nahrajte obrázek festivalu.');
        $form->addText('price', 'Cena:')
            ->setRequired('Prosím, zadejte cenu festivalu.')
            ->addRule(Form::FLOAT, 'Cena musí být číslo.');
        $form->addSubmit('submit', 'Přidat festival');
        $form->onSuccess[] = [$this, 'addFestivalFormSucceeded'];
        return $form;
    }

    public function addFestivalFormSucceeded(Form $form, \stdClass $values): void
    {
        // Handle file upload
        $image = $values->image;
        $imagePath = 'uploads/' . $image->getSanitizedName();
        $image->move($imagePath);

        // Use the facade to insert the festival into the database
        $this->festivalFacade->addFestival(
            $values->name,
            $values->description,
            $imagePath,
            (float) $values->price
        );

        $this->flashMessage('Festival byl úspěšně přidán.', 'success');
        $this->redirect('this');
    }
}
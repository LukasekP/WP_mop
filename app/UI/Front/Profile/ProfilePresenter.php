<?php
namespace App\UI\Front\Profile;
use Ublaboo\DataGrid\DataGrid;

use Nette\Application\UI\Form;
use App\Model\OrdersFacade;
use App\Model\UserFacade;
use App\Model\FestivalFacade;

use Nette;

class ProfilePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private OrdersFacade $ordersFacade, private UserFacade $userFacade, private FestivalFacade $festivalFacade)
    {
        $this->ordersFacade = $ordersFacade;
        $this->userFacade = $userFacade;
        $this->festivalFacade = $festivalFacade;
    }
   
    public function renderDefault()
    {
        $id = $this->getUser()->getId();
        $this->template->userData = $this->userFacade->getUserById($id);

    }

    public function renderTickets(): void
    {
        $email = $this->getUser()->getIdentity()->email;
        $this->template->orders = $this->ordersFacade->getOrdersByUserEmail($email);
    }
    public function renderChangeProfile(): void
    {
        $userId = $this->getUser()->getId();
        $userData = $this->userFacade->getUserById($userId);
        $this->template->userData = $userData;
    }

    public function handleDeleteImage(int $userId): void
    {
        $userData = $this->userFacade->getUserById($userId);
    
        if ($userData) {
            if (!empty($userData->image) && file_exists($userData->image)) {
                unlink($userData->image);
            }
    
            $this->userFacade->updateUserImage($userId, null);
    
            $this->flashMessage('Profilový obrázek byl smazán', 'success');
        } 
    
        $this->redirect('this');
    }

    protected function createComponentUploadForm(): Form
    {
        $form = new Form;
        $form->addUpload('image', 'Profilová fotka:'); 
    
        $form->addSubmit('submit', 'Uložit');
    
        $form->onSuccess[] = [$this, 'uploadFormSucceeded'];
        return $form;
    }
    
    public function uploadFormSucceeded(Form $form, $values): void
    {
        $userId = $this->getUser()->getId();
        $image = $values->image;
    
        if ($image->isOk() && $image->isImage()) {
            $imagePath = 'uploads/profile/' . $userId . '.jpg';
            $image->move($imagePath);
            $this->userFacade->updateUserImage($userId, $imagePath);
            $this->flashMessage('Profilová fotka byla úspěšně změněna.', 'success');
            $this->redirect('Profile:default');
        } else {
            if (!$image->isOk()) {
                $this->redirect('Profile:default');
                return;
            }
            $this->flashMessage('Nahrání obrázku se nezdařilo.', 'error');
        }
    
        $this->redirect('this');
    }
}        


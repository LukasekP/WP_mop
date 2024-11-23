<?php
namespace App\UI\Admin\User;

use App\Model\UserFacade;
use Nette\Application\UI\Form;
use Nette;
final class UserPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private UserFacade $userFacade)
    {
        $this->userFacade = $userFacade;
     
    }
    public function renderDefault()
    {
        $user = $this->getUser();
    
        if(!$user->isInRole('admin')){
            $this->flashMessage('Nemáte dostatečná práva k této strance', 'error');
            $this->redirect(':Front:Home:');
        }
        
        $this->template->users = $this->userFacade->getUsers();
    }
    public function renderDetail($id)
    {
        $user = $this->userFacade->getUserById($id); 
        $this->template->u = $this->userFacade->getUserById($id);
        $this->template->userData = $this->userFacade->getUserById($id);
        $this->getComponent('editForm')
        ->setDefaults($user->toArray()); 
    }
    public function renderEdit($id)
    {
        $user = $this->userFacade->getUserById($id); 
       
    
        $this->getComponent('editForm')
             ->setDefaults($user->toArray()); 
    }


    public function createComponentEditForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
             ->setHtmlAttribute('class', 'form-control')
             ->setNullable();
        $form->addPassword('password', 'Nové Heslo:')
             ->setOption('description', sprintf('Heslo musí mít aspoň %d znaků', $this->userFacade::PasswordMinLength))
             ->setHtmlAttribute('class', 'form-control')
             ->setNullable(); 
        $form->addUpload('image', 'Profilový obrázek:')
             ->addRule(Form::IMAGE, 'Obrazek musí být JPEG a PNG')
             ->setHtmlAttribute('class', 'form-control-file')
             ->setNullable();
        $form->addSubmit('send', 'Změnit')
             ->setHtmlAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(Form $form, \stdClass $values): void
    {
        $userId = $this->getParameter('id'); 

        if ($values->image->isOk()) {
            $values->image->move("upload/" . $values->image->getSanitizedName());
            $this->userFacade->updateUserImage($userId, "upload/" . $values->image->getSanitizedName());
            $imageChanged = true;
        }
        $this->userFacade->updateUser($userId, (array)$values);

        $this->flashMessage('Údaje změněny', 'success');
        $this->redirect(':Front:Home:default');
    }
    public function handleDelete(int $userId) {
        $this->userFacade->delete($userId);
        $this->redirect('User:default');
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
}
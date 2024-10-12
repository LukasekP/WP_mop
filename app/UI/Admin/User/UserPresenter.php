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
        $this->template->userData = $this->userFacade->getUserById($id);
    }
    public function renderEdit($id)
    {
        $this->template->userData = $this->userFacade->getUserById($id);
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

       
    
        $usernameChanged = false;
        $passwordChanged = false;
    
        
            // Aktualizujte uživatelské jméno, pokud bylo zadáno
            if (!empty($values->username)) {
                $this->userFacade->updateUsername($userId, $values->username);
                $usernameChanged = true;
            }
    
            // Aktualizujte heslo, pokud bylo zadáno
            if (!empty($values->password)) {
                $this->userFacade->updateUserPassword($userId, $values->password);
                $passwordChanged = true;
            }
            if (!empty($values->image)) {

            if ($values->image->isOk()) {
                $values->image->move("upload/" . $values->image->getSanitizedName());
                $this->userFacade->updateUserImage($userId, "upload/" . $values->image->getSanitizedName());
                $imageChanged = true;
            }}

            if ($usernameChanged && $passwordChanged) {
                $this->flashMessage('Uživatelské jméno a heslo byly úspěšně změněny.', 'success');
            } elseif ($usernameChanged) {
                $this->flashMessage('Uživatelské jméno bylo úspěšně změněno.', 'success');
            } elseif ($passwordChanged) {
                $this->flashMessage('Heslo bylo úspěšně změněno.', 'success');
            } elseif ($imageChanged) {
                $this->flashMessage('Profilový obrázek byl úspěšně přidán.', 'success');
            }
             else {
                $this->flashMessage('Prosím, zadejte nové uživatelské jméno nebo heslo.', 'error');
            }
        
    
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
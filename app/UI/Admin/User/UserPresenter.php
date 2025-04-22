<?php
namespace App\UI\Admin\User;

use Ublaboo\DataGrid\DataGrid;
use App\Model\UserFacade;
use Nette\Application\UI\Form;
use Nette;
final class UserPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(private UserFacade $userFacade)
    {
        $this->userFacade = $userFacade;
     
    }
    public function actionDefault(string $role = 'user'): void
    {
        if(!$this->getUser()->isInRole('admin')){
            $this->redirect(':Front:Home:');
        }
    }
    public function renderDefault(string $role = 'user')
    {
        $user = $this->getUser();
        
        $this->template->users = $this->userFacade->getUsers();
        $this->template->role = $role;

    }
    public function actionDetail($id): void
    {
        $user = $this->getUser();
        if(!$user->isInRole('admin')){
            $this->redirect(':Front:Home:');
        }
    }
    
    public function renderDetail($id)
    {
        $user = $this->getUser();
        $user = $this->userFacade->getUserById($id); 
                $this->template->u = $this->userFacade->getUserById($id);
                $this->template->userData = $this->userFacade->getUserById($id);
                $this->getComponent('editForm')
                    ->setDefaults($user->toArray()); 
                    $this->template->userId = $id; 

    }
    public function actionEdit($id): void
    {
        $user = $this->getUser();
        if(!$user->isInRole('admin')){
            $this->redirect(':Front:Home:');
        }
    }
    public function renderEdit($id)
    {
        $user = $this->getUser();
        
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
             ->addRule(Form::IMAGE, 'Obrázek musí být JPEG nebo PNG')
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
        $userData = $this->userFacade->getUserById($userId); 
        $dataToUpdate = [];
    
        if (!empty($values->username)) {
            $dataToUpdate['username'] = $values->username;
        }
    
        if (!empty($values->password)) {
            $dataToUpdate['password'] = $values->password;
        }
    
        if ($values->image instanceof \Nette\Http\FileUpload && $values->image->isOk() && $values->image->isImage()) {
            $imagePath = "upload/" . $values->image->getSanitizedName();
            $values->image->move($imagePath);
            $dataToUpdate['image'] = $imagePath;
        } else {
            $dataToUpdate['image'] = $userData->image; 
        }
    
        if (!empty($dataToUpdate)) {
            $this->userFacade->updateUser($userId, $dataToUpdate);
            $this->flashMessage('Údaje změněny', 'success');
        } else {
            $this->flashMessage('Žádná změna nebyla provedena.', 'info');
        }
    
        $this->redirect('this');
    }
    
    public function handleDelete(int $id) {
        $this->userFacade->delete($id);
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


    protected function createComponentUsersGrid(): DataGrid
    {
        return $this->createRoleGrid('user');
    }
    
    protected function createComponentBandManagersGrid(): DataGrid
    {
        return $this->createRoleGrid('bandManager');
    }
    
    protected function createComponentFestivalManagersGrid(): DataGrid
    {
        return $this->createRoleGrid('festivalManager');
    }
    
    protected function createComponentAccountantsGrid(): DataGrid
    {
        return $this->createRoleGrid('accountant');
    }
    
    private function createRoleGrid(string $role): DataGrid
    {
        $grid = new DataGrid();
        $dataSource = $this->userFacade->getUsers()->where('role', $role);
        $grid->setDataSource($dataSource);
    
        $grid->addColumnNumber('id', 'id')
             ->setSortable();
    
        $grid->addColumnText('username', 'už. jméno')
             ->setTemplateEscaping(false)
             ->setRenderer(function($item) {
                 $link = $this->link('User:detail', ['id' => $item->id]);
                 return '<a href="' . $link . '">' . htmlspecialchars($item->username) . '</a>';
             })
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat už. jméno');
    
        $grid->addColumnText('firstname', 'jméno')
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat jméno');
    
        $grid->addColumnText('lastname', 'Přijmení')
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat přijmení');
    
        $grid->addColumnText('email', 'E-mail')
             ->setFilterText()
             ->setAttribute('placeholder', 'Vyhledat e-mail');
    
        $grid->addColumnText('phone', 'Telefon');
    
        $grid->addColumnText('role', 'Role');
    
        $grid->addAction('delete', 'Smazat', 'delete!')
             ->setClass('btn btn-xs btn-danger ajax');

        $grid->setTranslator(new \Ublaboo\DataGrid\Localization\SimpleTranslator([
            'ublaboo_datagrid.action' => 'Akce', 
        ]));

        return $grid;
    }


    public function handleChangeRoleToUser(int $userId): void
    {
        $userRole = $this->userFacade->getUserById($userId);
    
        if (!$userRole) {
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('this');
        }
    
        $this->userFacade->updateUserRole($userId, 'user');
        $this->flashMessage('Role uživatele byla úspěšně změněna na Uživatele.', 'success');
        $this->redirect('this');
    }
    public function handleChangeRoleToFestivalManager(int $userId): void
    {
        $userRole = $this->userFacade->getUserById($userId);
    
        if (!$userRole) {
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('this');
        }
    
        $this->userFacade->updateUserRole($userId, 'festivalManager');
        $this->flashMessage('Role uživatele byla úspěšně změněna na Manažer festivalů.', 'success');
        $this->redirect('this');
    }
    public function handleChangeRoleToAccountant(int $userId): void
    {
        $userRole = $this->userFacade->getUserById($userId);
    
        if (!$userRole) {
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('this');
        }
    
        $this->userFacade->updateUserRole($userId, 'accountant');
        $this->flashMessage('Role uživatele byla úspěšně změněna na Učetní.', 'success');
        $this->redirect('this');
    }
    public function handleChangeRoleToBandManager(int $userId): void
    {
        $userRole = $this->userFacade->getUserById($userId);
    
        if (!$userRole) {
            $this->flashMessage('Uživatel nebyl nalezen.', 'error');
            $this->redirect('this');
        }
    
        $this->userFacade->updateUserRole($userId, 'bandManager');
        $this->flashMessage('Role uživatele byla úspěšně změněna na Manažer kapel.', 'success');
        $this->redirect('this');
    }
}
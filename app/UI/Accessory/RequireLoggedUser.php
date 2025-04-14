<?php

declare(strict_types=1);

namespace App\UI\Accessory;


/**
 * Trait to enforce user authentication.
 * Redirects unauthenticated users to the sign-in page.
 */
trait RequireLoggedUser
{
	/**
	 * Injects the requirement for a logged-in user.
	 * Attaches a callback to the startup event of the presenter.
	 */
	public function injectRequireLoggedUser(): void
	{
		$this->onStartup[] = function () {
			$user = $this->getUser();
			// If the user isn't logged in, redirect them to the sign-in page
			if ($user->isLoggedIn()) {
				return;
			} elseif ($user->getLogoutReason() === $user::LogoutInactivity) {
				$this->flashMessage('Byli jste odhlášeni z důvodu nečinnosti. Přihlaste se prosím znovu.');
				$this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
			} else {
				$this->redirect(':Front:Sign:in');
			}
		};
	}
}

<?php


namespace App\Presenters;

use App\Auth\EmailAuthenticator;
use App\OAuth\OAuthService;
use Nette\Application\Attributes\Persistent;
use Nette\DI\Attributes\Inject;
use Tracy\Debugger;

final class SignPresenter extends BasePresenter
{
	#[Inject]
	public EmailAuthenticator $authenticator;

	#[Inject]
	public OAuthService $oauthService;

	#[Persistent]
	public string $backlink = '';

	public function actionIn()
	{
		if ($this->user->identity) {
			$token = $this->user->identity->token;
			$tokenExpiration = $this->user->identity->tokenExpiration;

			if (($token) && ($tokenExpiration > time())) {
				if ($this->authenticate($token)) {
					$this->user->identity->token = $token;
					$this->user->identity->tokenExpiration = $tokenExpiration;

					if ($this->backlink) {
						$this->restoreRequest($this->backlink);
					}

					$this->redirect('Homepage:default');
				} else {
					$this->flashMessage('Přihlášení přes token se nezdařilo', 'error');
					unset($this->user->identity->token);
				}
			}
		}

		$url = $this->oauthService->getAuthorizeCodeUrl($this->backlink);
		$this->redirectUrl($url);
	}

	public function actionOAuth(string $code, string $state = null): void
	{
		$response = $this->oauthService->sendAccessTokenRequest($code);

		if ($this->authenticate($response->access_token)) {
			$this->user->identity->token = $response->access_token;
			$this->user->identity->tokenExpiration = time() + $response->expires_in;

			if ($state) {
				$this->restoreRequest($state);
			}
		} else {
			$this->flashMessage('Přihlášení přes token se nezdařilo', 'error');
		}

		$this->redirect('Homepage:default');
	}

	private function authenticate(string $accessToken): bool
	{
		try {
			$response = $this->oauthService->sendUserRequest($accessToken);
		} catch (\Exception $exception) {
			return false;
		}

		$this->authenticator->authenticate($response->email);

		$this->user->identity->mail = $response->user_email;

		return true;
	}

	public function actionOut()
	{
		if ($this->user->isLoggedIn()) {
			$this->user->logout();
		}

		$this->redirect('Homepage:albums');
	}
}
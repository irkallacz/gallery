<?php


namespace App\Presenters;

use App\Authenticator\EmailAuthenticator;
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
			if ($this->user->identity->token_expiration > time()) {

				$this->authenticate($this->user->identity->token);

				if ($this->backlink) {
					$this->restoreRequest($this->backlink);
				} else {
					$this->redirect('Homepage:default');
				}
			}
		}

		$url = $this->oauthService->getAuthorizeCodeUrl($this->backlink);
		$this->redirectUrl($url);
	}

	public function actionOAuth(string $code, string $state = null): void
	{
		$response = $this->oauthService->sendAccessTokenRequest($code);

		$this->authenticate($response->access_token);

		$this->user->identity->token = $response->access_token;
		$this->user->identity->token_expiration = $response->expires_in;

		if ($state) {
			$this->restoreRequest($state);
		} else {
			$this->redirect('Homepage:default');
		}
	}

	private function authenticate(string $accessToken)
	{
		$response = $this->oauthService->sendUserRequest($accessToken);
		//$this->user->setExpiration('+6 hours');
		$this->authenticator->authenticate($response->email);

		//TODO add login to database

		$this->user->identity->mail = $response->email;
	}

	public function actionOut()
	{
		if ($this->user->isLoggedIn()) {
			$this->user->logout();
		}

		$this->redirect('Homepage:albums');
	}
}
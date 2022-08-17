<?php


namespace App\Presenters;


final class SignPresenter extends BasePresenter
{

	public function actionIn()
	{
		$url = 'https://account.vzs-jablonec.lh/oauth/auth';

		$url = $url . http_build_query([
			'response_type' => 'code',
			'access_type' => 'online',
			'client_id' => '',
			'redirect_uri' => $this->link('//Sign:oAuth'),
			'state' => '',
			'scope' => '',
		]);

		$this->redirectUrl($url);
	}

	public function actionOAuth(string $code): void
	{
		//POST https://login.szn.cz/api/v1/oauth/token
		//Accept: application/json
		//{
				//"grant_type": "authorization_code",
				//"code": "..."
				//"redirect_uri": "...",
				//"client_secret": "...",
				//"client_id": "..."
		//}
		//
		//Odpověď obsahuje standardní data dle RFC a navíc ještě:
		//
				//oauth_user_id obsahující unikátní jedinečný identifikátor uživatele; případná další data o uživateli je nutné získat následujícím voláním (které je již autorizováno tokenem)
				//account_name obsahující e-mail uživatele
				//scopes obsahující pole scopes, které uživatel odsouhlasil
		//
		//Data o uživateli
		//GET https://login.szn.cz/api/v1/user
		//Authorization: bearer ...token...
		//Accept: application/json
	}
}
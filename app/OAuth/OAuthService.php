<?php


namespace App\OAuth;


use GuzzleHttp\Client;
use Nette\Utils\Json;

final class OAuthService
{
	private string $clientId;

	private string $clientSecret;

	private string $authorizeUrl;

	private string $tokenUrl;

	private string $resourceUrl;

	private string $redirectUrl;

	/**
	 * OAuthService constructor.
	 * @param string $clientId
	 * @param string $clientSecret
	 * @param string $authorizeUrl
	 * @param string $tokenUrl
	 * @param string $resourceUrl
	 * @param string $redirectUrl
	 */
	public function __construct(string $clientId, string $clientSecret, string $redirectUrl, string $authorizeUrl, string $tokenUrl, string $resourceUrl)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->redirectUrl = $redirectUrl;
		$this->authorizeUrl = $authorizeUrl;
		$this->tokenUrl = $tokenUrl;
		$this->resourceUrl = $resourceUrl;
	}

	public function getAuthorizeCodeUrl(string $state): string
	{
		return $this->authorizeUrl . '?' . http_build_query([
				'response_type' => 'code',
				'access_type' => 'online',
				'client_id' => $this->clientId,
				'redirect_uri' => $this->redirectUrl,
				'state' => $state,
				'scope' => 'account',
			]);
	}

	public function sendAccessTokenRequest(string $code): object
	{
		$client = new Client(['verify' => false]);
		$response = $client->request('POST', $this->tokenUrl, ['json' => [
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'redirect_uri' => $this->redirectUrl,
		]]);

		return Json::decode($response->getBody());
	}

	public function sendUserRequest(string $accessToken): object
	{
		$client = new Client(['verify' => false]);
		$response = $client->request('GET', $this->resourceUrl, ['headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken]]);

		return Json::decode($response->getBody());
	}


}
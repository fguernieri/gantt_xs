<?php

namespace OCA\GanttXS\Controller;

use OCP\AppFramework\ApiController as BaseApiController;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Http\Client\IClientService;
use Exception;

class DeckApiController extends BaseApiController {
  private $urlGenerator;
  private $userSession;
  private $clientService;

  public function __construct(
    string $appName,
    IRequest $request,
    IURLGenerator $urlGenerator,
    IUserSession $userSession,
    IClientService $clientService
  ) {
    parent::__construct($appName, $request);
    $this->urlGenerator = $urlGenerator;
    $this->userSession = $userSession;
    $this->clientService = $clientService;
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function getStacks(): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards';

      $response = $client->get($url, $this->getDeckRequestOptions());

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function getStacksForBoard(int $boardId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks';

      $response = $client->get($url, $this->getDeckRequestOptions());

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function getStack(int $boardId, int $stackId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks/' . $stackId;

      $response = $client->get($url, $this->getDeckRequestOptions());

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function getCard(int $boardId, int $stackId, int $cardId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks/' . $stackId . '/cards/' . $cardId;

      $response = $client->get($url, $this->getDeckRequestOptions());

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function updateCard(int $boardId, int $stackId, int $cardId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks/' . $stackId . '/cards/' . $cardId;

      $body = $this->request->getContent();

      $response = $client->put($url, array_merge(
        $this->getDeckRequestOptions(),
        ['body' => $body]
      ));

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function deleteCard(int $boardId, int $stackId, int $cardId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks/' . $stackId . '/cards/' . $cardId;

      $client->delete($url, $this->getDeckRequestOptions());

      return new DataResponse(['success' => true]);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  /**
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function createCard(int $boardId, int $stackId): DataResponse {
    try {
      $client = $this->clientService->newClient();
      $baseUrl = $this->getNextcloudBaseUrl();
      $url = $baseUrl . '/ocs/v2.php/apps/deck/api/v1.0/boards/' . $boardId . '/stacks/' . $stackId . '/cards';

      $body = $this->request->getContent();

      $response = $client->post($url, array_merge(
        $this->getDeckRequestOptions(),
        ['body' => $body]
      ));

      $data = json_decode($response->getBody(), true);
      return new DataResponse($data['ocs']['data'] ?? []);
    } catch (Exception $e) {
      return new DataResponse(['error' => $e->getMessage()], 500);
    }
  }

  private function getNextcloudBaseUrl(): string {
    return $this->urlGenerator->getAbsoluteURL('');
  }

  private function getDeckRequestOptions(): array {
    return [
      'headers' => [
        'OCS-APIRequest' => 'true',
        'Content-Type' => 'application/json',
      ]
    ];
  }

  private function getAuthCredentials(): array {
    $user = $this->userSession->getUser();
    if (!$user) {
      throw new Exception('Usuário não autenticado');
    }

    return [];
  }
}

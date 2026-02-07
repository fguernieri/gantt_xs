<?php

return [
  'routes' => [
    ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'deckApi#getStacks', 'url' => '/api/stacks', 'verb' => 'GET'],
    ['name' => 'deckApi#getStacksForBoard', 'url' => '/api/boards/{boardId}/stacks', 'verb' => 'GET'],
    ['name' => 'deckApi#getStack', 'url' => '/api/stacks/{boardId}/{stackId}', 'verb' => 'GET'],
    ['name' => 'deckApi#getCard', 'url' => '/api/cards/{boardId}/{stackId}/{cardId}', 'verb' => 'GET'],
    ['name' => 'deckApi#updateCard', 'url' => '/api/cards/{boardId}/{stackId}/{cardId}', 'verb' => 'PUT'],
    ['name' => 'deckApi#deleteCard', 'url' => '/api/cards/{boardId}/{stackId}/{cardId}', 'verb' => 'DELETE'],
    ['name' => 'deckApi#createCard', 'url' => '/api/stacks/{boardId}/{stackId}/cards', 'verb' => 'POST'],
  ],
];

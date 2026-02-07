<?php

namespace OCA\GanttXS\Controller;

// Ensure the parent DeckApiController is loaded before declaring ApiController.
// This prevents reflection/autoload ordering problems that caused
// "Class 'OCA\\GanttXS\\Controller\\ApiController' does not exist" errors.
require_once __DIR__ . '/DeckApiController.php';

class ApiController extends DeckApiController {}

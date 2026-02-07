<?php

namespace OCA\GanttXS\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCA\GanttXS\Middleware\CSPMiddleware;

class Application extends App implements IBootstrap {
  public const APP_ID = 'gantt_xs';

  public function __construct() {
    parent::__construct(self::APP_ID);
  }

  public function register(IRegistrationContext $context): void {
    $context->registerMiddleware(CSPMiddleware::class);
  }

  public function boot(IBootContext $context): void {
    $context->injectFn(function (INavigationManager $navigationManager, IURLGenerator $urlGenerator) {
      $navigationManager->add([
        'id' => self::APP_ID,
        'order' => 100,
        'href' => $urlGenerator->linkToRoute('gantt_xs.page.index'),
        'icon' => $urlGenerator->imagePath(self::APP_ID, 'app.svg'),
        'name' => 'Gantt XS',
      ]);
    });
  }
}



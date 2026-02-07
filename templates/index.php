<?php
/** @var array $_ */

use OCA\GanttXS\Util\AssetLoader;

$assets = AssetLoader::getViteAsset('gantt_xs', 'src/main.js');
$base = \OCP\Util::linkToAbsolute('gantt_xs', 'js/');
?>

<div id="app"></div>

<?php if (!empty($assets['css'])): ?>
  <?php foreach ($assets['css'] as $cssFile): ?>
    <link rel="stylesheet" href="<?php p($base . $cssFile); ?>">
  <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($assets['js'])): ?>
  <script type="module" src="<?php p($base . $assets['js']); ?>"></script>
<?php else: ?>
  <div style="padding: 16px;">
    Build não encontrado. Execute <code>npm run build</code> no app.
  </div>
<?php endif; ?>

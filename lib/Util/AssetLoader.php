<?php
namespace OCA\GanttXS\Util;

class AssetLoader {
  public static function getViteAsset(string $appId, string $entry): array {
    $appPath = \OC_App::getAppPath($appId);
    $manifestPath = $appPath . "/js/manifest.json";
    $manifestFallback = $appPath . "/js/.vite/manifest.json";

    if (file_exists($manifestFallback)) {
      $manifestPath = $manifestFallback;
    }

    if (!file_exists($manifestPath)) {
      return ["js" => null, "css" => []];
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (!is_array($manifest) || !isset($manifest[$entry])) {
      return ["js" => null, "css" => []];
    }

    $entryData = $manifest[$entry];
    $js = $entryData["file"] ?? null;
    $css = $entryData["css"] ?? [];

    return ["js" => $js, "css" => $css];
  }
}

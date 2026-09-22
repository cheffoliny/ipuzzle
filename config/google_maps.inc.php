<?php

// Constants may be set in the site's untracked config.inc.php; environment
// variables provide the same settings without changing application sources.
$googleMapsSetting = static function (string $name, $default) {
    if (defined($name)) {
        return constant($name);
    }
    $value = getenv($name);
    return $value === false ? $default : $value;
};
$googleMapsLocalRequest = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

return [
    'apiKey' => trim((string) $googleMapsSetting('IPUZZLE_GOOGLE_MAPS_API_KEY', '')),
    'mapId' => trim((string) $googleMapsSetting('IPUZZLE_GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID')),
    // This permits a normal SDK request, not a bypass of Google's key checks.
    'allowKeylessDev' => filter_var(
        $googleMapsSetting('IPUZZLE_GOOGLE_MAPS_ALLOW_KEYLESS_DEV', $googleMapsLocalRequest),
        FILTER_VALIDATE_BOOLEAN
    ),
];

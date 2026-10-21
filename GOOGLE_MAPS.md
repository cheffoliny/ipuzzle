# Google Maps in ipuzzle

## Configuration

`config/google_maps.inc.php` reads these environment variables, or constants
with the same names defined in the site's untracked `config/config.inc.php`:

| Setting | Default | Meaning |
| --- | --- | --- |
| `IPUZZLE_GOOGLE_MAPS_API_KEY` | Empty | Browser API key or Maps Demo Key. |
| `IPUZZLE_GOOGLE_MAPS_MAP_ID` | `DEMO_MAP_ID` | Map ID for advanced markers. Set a project map ID for production. |
| `IPUZZLE_GOOGLE_MAPS_ALLOW_KEYLESS_DEV` | True only for requests from `127.0.0.1` or `::1` | Permit attempting to load the official JavaScript SDK without a key. Explicitly enable for a LAN development server if needed. |

For example, in the local configuration, before the closing PHP tag:

```php
define('IPUZZLE_GOOGLE_MAPS_API_KEY', 'YOUR_API_OR_DEMO_KEY');
define('IPUZZLE_GOOGLE_MAPS_ALLOW_KEYLESS_DEV', false);
```

Keep real keys out of tracked sources. Browser keys are visible in the browser;
restrict them to the required APIs and the site's HTTP referrers, including
the local development URLs when applicable. Restart Apache after changing its
environment variables. No database migration is required.

## Without a key

There is no guaranteed unrestricted, keyless development mode in the Google
Maps JavaScript API. A normal SDK request without a key may show a watermark or
be rejected with `MissingKeyMapError`/`NoApiKeys`. This integration does not
hide Google warnings, remove watermarks, proxy tiles, or bypass authentication.

For supported prototyping without billing setup, obtain a Maps Demo Key from
Google and configure it using the API-key setting. It has quotas and supports
only selected features; Street View is not listed among its supported features.
A production project needs the appropriate enabled APIs and billing settings.
`DEMO_MAP_ID` is a map identifier, not an API key.

## Screens

- `object_geo`: Google is the primary map, with hybrid satellite view, map-type
  controls, Street View controls and a draggable advanced marker. Clicking the
  map also moves the marker. Saving keeps the existing `new_lat` / `new_lan`
  RPC contract and coordinate-history logging. Google failures fall back to
  Leaflet/OpenStreetMap, with a visible explanation and working coordinate
  editing. If both providers fail to initialize, saving stays disabled.
- The Google Maps link opens the current marker position using Maps URLs;
  these links do not require an API key. It does not import edits from the
  external Google Maps page back into ipuzzle.
- `templates/objects_traning_window_map.php`: uses the same configuration and
  loader, with advanced markers and its existing object-selection controls.
  Google failures are reported in the page; this older screen has no Leaflet
  fallback. Its legacy database queries were not redesigned.
- The SDK is loaded only on map screens, once per window, using the quarterly
  channel, Bulgarian labels, a network timeout and `gm_authFailure` handling.
- The legacy `suggest_prototype.js` must preserve the browser's native
  `Element` constructor and `Function.prototype.bind`. Its Element helpers
  extend the constructor, rather than replacing it with a plain object; the
  latter breaks Google's advanced markers with an `instanceof` TypeError.

This change does not restore the removed, unused analytics-map screen or the
old custom Street View point/POV editor. Existing saved POV data is untouched.

## Verification

Run `node tests/google_maps_loader_test.cjs`,
`node tests/object_geo_controller_test.cjs` and
`php tests/google_maps_config_test.php` for isolated configuration/loader tests.
Run `node tests/prototype_native_compat_test.cjs` to verify compatibility with
the legacy helpers and native DOM constructors.
`tests/object_geo_google_harness.php` exercises the actual template JavaScript
using synthetic coordinates, without loading client records or saving to the
database. Without a configured key it tests the localhost keyless attempt and
fallback. Append `?scenario=missing-key` to exercise the missing-key fallback
without requesting the Google SDK. The harness includes the legacy Prototype,
suggest and framework scripts; `?scenario=configured-key` loads the local site
configuration as well. It is restricted to loopback requests and never saves
coordinates to the database. On 2026-09-22, the localhost browser test
rendered Google's satellite imagery without a key, with Google's warning and
development watermark still present. This observed behavior is not a service
guarantee. Production rendering needs verification with an authorized key.

## Official references

- [API setup](https://developers.google.com/maps/documentation/javascript/get-api-key)
- [Maps Demo Key](https://developers.google.com/maps/documentation/javascript/demo-key)
- [Errors and authentication failures](https://developers.google.com/maps/documentation/javascript/error-messages)
- [Advanced markers](https://developers.google.com/maps/documentation/javascript/advanced-markers/migration)
- [Keyless Maps URLs](https://developers.google.com/maps/documentation/urls/get-started)

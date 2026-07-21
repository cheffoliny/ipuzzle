# RPC result contract during the XSLT migration

Този файл описва поведението, което трябва да остане съвместимо, докато
`xsl/general_result.xsl` се заменя от `js/rpc_result_renderer.js`.

## Приоритет на настройките

Стойност от атрибут на текущия result контейнер има приоритет пред глобалната
`rpc_*` променлива. Ако атрибутът липсва, се използва глобалната стойност.

Пример:

```html
<div id="result" rpc_excel_panel="off" rpc_paging="off"></div>
```

## Видими панели и колони

| Настройка | Стойност `on` | Стойност `off` | Бележка |
| --- | --- | --- | --- |
| `rpc_excel_panel` | Показва EXCEL и PDF | Скрива и двата бутона | Няма отделен `rpc_pdf_panel` |
| `rpc_paging` | Показва paging панела | Скрива paging панела | Независим е от export панела |
| `rpc_autonumber` | Показва колоната `#` | Скрива колоната `#` | Legacy `false` също води до скрита колона |
| `rpc_resize` | Не променя `general_result` | Не променя `general_result` | Resizer кодът в general XSL е коментиран; размерът идва от template/CSS |

Export бутоните запазват legacy действията:

- EXCEL: `loadDirect('export_to_xls')`
- PDF: `loadDirect('export_to_pdf')`

Преди export се възстановяват `rpc_prefix`, `rpc_result_area` и
`rpc_action_script` на конкретната справка.

## Други поддържани настройки

- `rpc_edit_report`
- `rpc_invoice_toolbar`
- `rpc_admin_invoice_toolbar`
- `rpc_invoice_services_toolbar`
- `rpc_transfer_objects`
- `rpc_offset`
- `rpc_prefix`
- `rpc_action_script`

## Специализирани XSL файлове

DOM renderer-ът прихваща `xsl/general_result.xsl` и вече има отделни profiles за
`xsl/tech_planning_request.xsl`, `xsl/tech_planning_schedule.xsl` и
`xsl/limit_card_persons.xsl` и `xsl/person_schedule.xsl`.

Profile-ът за техническите заявки запазва следния специализиран contract:

- клик върху ред записва реалното ID на заявката в локалното и parent полето `id_request`;
- избраният ред се маркира с legacy тъмносин цвят и бял bold текст;
- `rpc_resize=on` следи resize на прозореца, а `rpc_resize=off` оразмерява еднократно спрямо result контейнера;
- `rpc_excel_panel=off` и `rpc_paging=off` не създават празни панели;
- XSL файлът не се заявява от браузъра при успешно DOM render-ване.

Profile-ът за графика на техническото планиране запазва специализирания contract:

- `id`, `onclick`, `class` и `style` атрибутите на интерактивните клетки се запазват;
- `planning(person, column, row)` продължава да се извиква при клик върху клетка;
- свободните и вече планираните клетки запазват API/legacy цветовете си въпреки общия `table.result td` CSS фон;
- избраният интервал се маркира с `#612c2c`, а при смяна на реда предишният интервал възстановява редуващия се фон;
- след успешно `planning` обновяването на графика и заявките се отлага до освобождаването на RPC lock-а;
- generic row-selection обработчиците не се добавят към schedule редовете;
- legacy table/total класовете се запазват, а `rpc_excel_panel="off"` не създава export панел;
- XSL файлът не се заявява от браузъра и loader-ът се изключва след DOM render-ването.

Profile-ът за служителите към лимитна карта запазва следния contract:

- legacy идентификаторите `tableResult` и `c[field][row]` се запазват;
- линковете използват ID-то от XML клетката, а не само ID-то на реда;
- часовите клетки запазват API цветовете и празните клетки остават видими;
- autonumber, paging, export и generic row-selection обработчиците не се добавят;
- вторият RPC (`result2`) се стартира след освобождаване на RPC lock-а;
- XML debug остава активен, без браузърът да заявява XSL файла.

Profile-ът за месечния график на служителите запазва следния contract:

- legacy идентификаторите `tableShifts`, `container`, `tableResult` и `c[field][person]` се запазват;
- бутоните за смени носят коефициент, продължителност и признак за отпуск, а бутонът за изчистване подава `#0`;
- `sid[day][person]` и `real_hours[person]` се създават преди `FormProcessing_action()` и се инициализират от action XML;
- цветните стойности за остатъчните часове остават отделни DOM елементи и се преизчисляват без парсване на HTML като число;
- горният и долният header, персоналните линкове, печатът и всички бутони за управление на графика се запазват;
- XSL файлът не се заявява от браузъра, а суровият XML продължава да се записва в debug прозореца.

Всички активни присвоявания към `rpc_xsl` вече се обслужват от DOM renderer.
`xsl/object_storage.xsl` е запазен само като legacy архивен файл: в проекта няма template,
JavaScript или PHP код, който го присвоява или заявява. Реалният екран `object_store`
използва `general_result.xsl`, съответно профила `general`. Ако архивният файл бъде
активиран отново, преди това трябва да получи изричен DOM profile.

## XML debug invariant

При `rpc_debug && rpc_eol_debug` прозорецът трябва да записва:

1. `Request to:`;
2. суровия `XML Response:`;
3. информацията от `FormProcessing()`.

Суровият XML response трябва да бъде записан преди DOM renderer-а. Browser XSLT
fallback вече няма. Това се проверява от `xmlrpc_dom_integration_harness.html` и
`xmlrpc_debug_contract.php`.

## PHP 8.5 read-only API smoke

`php85_api_readonly_smoke.php` invokes selected API classes directly, without
`api_general.php`, so the test does not create system-event records. Only methods
audited as read-only are included; changes to `$_SESSION` are process-local.

The current representative set covers:

- `setup_clients/load`;
- `sales_docs/load` and `sales_docs/result` with a bounded page size;
- `tech_planning_requests/load` (not `result`, because the latter resets old requests);
- `buy_docs/load` and `buy_docs/result` with a bounded page size;
- `working_cards/result`;
- `incomings/init` and `incomings/search`;
- asset settings, nomenclatures, groups, attributes, storagehouses, totals and sub-assets;
- salary totals, salary by firms and personal salary reports;
- budget and collections initialization, plus the real `budget/search`,
  `collections/search` and `incomings/search` calculation paths for the latest
  available one- and two-month database periods, including the regional budget
  and collections branches. The search cases also require their computed Flex
  arrays to be present and non-empty, because these legacy screens return their
  report data through `FlexVar` rather than XML result rows. Their returned values
  must not contain legacy `лв.` labels or duplicated `€ €` suffixes;
- empty scalar checkbox selections for limit-card, technical-request, personal-card
  operation and PPP actions. These must remain read-only and return clean XML;
- `missing_documents/result` with a scalar document selection, which protects the
  PHP 8.5 `implode()`/`count()` array-parameter contract;
- `client_objects/result` with an existing client from the local database.

Every case must return parseable XML, without raw PHP diagnostics and without a
`<php>` diagnostics section. `api_general.php` catches `Throwable`, so PHP 8
`Error` and `TypeError` failures are serialized by `DBResponse` and remain visible
in the existing XML debug workflow instead of corrupting it with an HTML fatal error.

Legacy RPC v1 endpoints are executed in isolated PHP processes by
`php85_api_v1_readonly_smoke.php`, because several procedural API files declare the
same `MyHandler` class name. The suite covers `setup_firms/result`,
`setup_objects/generate`, `setup_objects/result`, `admin_personnels/load` and
`admin_personnels/result`. RPC v1 and RPC v2 use the same throwable-to-XML serializer.
The isolated RPC v1 suite also covers the two procedural personal-salary reports
using an existing salary/person/month tuple from the local database.

`xml_output_hygiene_contract.php` rejects active ADOdb `->debug = true` flags in
application API, database and engine code. ADOdb debug writes SQL/HTML directly to
the response and is therefore separate from, and incompatible with, the structured
`APILog` section used by the XML debug window.

`sale_controller_php85_smoke.php` executes the read-only JSON `action=init` path of
`api_sale_controller.php`. It protects the four-argument PHP 8 error-handler
signature and verifies that bank-account rows without the legacy `is_paid` and
`tax` fields are cast without undefined-key diagnostics.

`error_handlers_php85.php` protects the same four-argument callback contract in
the bundled Zend Session, Zend Audioscrobbler and AMF handlers. It also verifies
that the modernized AMF exception keeps its legacy `code`, `file` and `line`
interface without redeclaring PHP 8's typed `Exception` properties.

`fpdi_php85_smoke.php` protects the legacy FPDI package from removed PHP APIs and
PHP4-style constructors. It creates a PDF, imports its page through the bundled
parser and verifies that FPDI returns a valid PDF document.

`legacy_constructors_php85.php` verifies PHP 8 constructors in the ZIP reader,
XML builder, Smarty config reader, barcode drawing and UFPDF helpers. It also
renders a real Code 128 PNG and a valid Unicode PDF document.

`amfphp_php85_smoke.php` exercises the legacy AMFPHP gateway with a real binary
AMF0 request/response round trip. It verifies Gateway, Executive, message,
serializer and deserializer construction; converts a PHP `TypeError` to an AMF
`/onStatus` result; invokes `DiscoveryService.getServices`; and invokes the
read-only application service `setup_code_leave.init` through the full DBResponse
and ADOdb stack. Temporary PHP session files are removed after the test.

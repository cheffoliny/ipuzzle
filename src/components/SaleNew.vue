<template>
  <div
    v-if="loaded && document_data.sale_doc_view"
    class="flex flex-col h-screen antialiased bg-white border-t-4 border-indigo-400 pt-3 pb-2 px-3"
  >
    <div
      class="header"
      :class="headerCollapse ? 'header-collapse' : 'header-full'"
    >
      <div class="client">
        <label class="custom-label" for="client">клиент:</label>
        <Suggest
          id="client"
          name="client"
          v-if="!urlParams.id_object && urlParams.id === null"
          api="api/api_sale_suggest.php?action=client"
          v-model="search"
          :result_id.sync="client.id"
          :autofocus="!client.id || !urlParams.id ? true : false"
          @clear-data="resetClient()"
          @select-client="setClient($event)"
        ></Suggest>
        <Suggest
          id="client"
          name="client"
          v-else-if="showChangeClientDialog"
          api="api/api_sale_suggest.php?action=client"
          v-model="searchNewClient"
          :autofocus="true"
          @select-client="updateClient($event)"
          @hide="(searchNewClient = ''), (showChangeClientDialog = false)"
        ></Suggest>
        <div
          v-else
          id="client"
          class="flex items-center custom-input col-start-2 col-end-5"
        >
          <div
            @click="allowChangeClient"
            class="w-full truncate"
            v-text="urlParams.id ? search : client.name"
          ></div>
          <button
            v-if="client.id"
            class="pl-2 focus:outline-none"
            @click="openClient"
          >
            <i class="fad fa-external-link fa-fw"></i>
          </button>
        </div>

        <div v-show="!headerCollapse" class="custom-label">адрес:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="client.invoice_address"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">ин:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input truncate"
          v-text="client.invoice_ein"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">по ддс:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input truncate"
          v-text="client.invoice_ein_dds"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">мол:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="client.invoice_mol"
        ></div>
      </div>

      <div class="doc">
        <div class="custom-label">номер:</div>
        <input
          v-if="urlParams.is_book && this.client.id"
          class="custom-input book-doc-num"
          type="number"
          step="1"
          min="0"
          @keydown="filterKey"
          @input="filterInput"
          v-model="isBookDocNum"
        />
        <div
          v-else
          class="custom-input truncate"
          v-text="urlParams.id ? (is_new_relative_doc ? 'незаписан' : document_data.doc_num) : 'незаписан'"
        ></div>

        <template v-if="!urlParams.id">
          <label v-show="!headerCollapse" class="custom-label" for="doc_type"
            >вид:</label
          >
          <select
            v-show="!headerCollapse"
            id="doc_type"
            class="custom-input"
            v-model="doc_type"
            :disabled="urlParams.id"
          >
            <option value="faktura">Фактура</option>
            <option v-if="!urlParams.is_book" value="kvitanciq">
              Проформа фактура
            </option>
            <option v-if="!urlParams.is_book" value="oprostena">
              Квитанция
            </option>
            <option
              v-if="document_data.doc_type === 'kreditno izvestie'"
              value="kreditno izvestie"
            >
              Кредитно известие
            </option>
            <option
              v-if="document_data.doc_type === 'debitno izvestie'"
              value="debitno izvestie"
            >
              Дебитно известие
            </option>
          </select>
        </template>
        <template v-else>
          <div v-show="!headerCollapse" class="custom-label">вид:</div>
          <div v-show="!headerCollapse" class="custom-input truncate">
            {{ docType.name }}
          </div>
        </template>

        <label v-show="!headerCollapse" class="custom-label" for="created_date"
          >издаване:</label
        >
        <datepicker
          :disabled="document_data.doc_status === 'canceled'"
          v-show="!headerCollapse"
          id="created_date"
          autocomplete="off"
          spellcheck="false"
          placeholder="xx.xx.xxxx"
          inputClass="custom-input"
          :mondayFirst="true"
          :fullMonthName="true"
          :highlighted="highlighted"
          format="DD.MM.YYYY"
          v-model="doc_date_create"
        />

        <label v-show="!headerCollapse" class="custom-label" for="event_date"
          >събитие:</label
        >
        <datepicker
          v-show="!headerCollapse"
          :disabled="document_data.doc_status === 'canceled'"
          id="event_date"
          autocomplete="off"
          spellcheck="false"
          placeholder="xx.xx.xxxx"
          inputClass="custom-input doc-info"
          :mondayFirst="true"
          :fullMonthName="true"
          :highlighted="highlighted"
          format="DD.MM.YYYY"
          v-model="doc_date"
        />
      </div>

      <div class="deliverer">
        <label class="custom-label deliverer-label" for="deliverer"
          >доставчик:</label
        >
        <select
          v-if="!urlParams.id"
          id="deliverer"
          class="custom-input col-start-2 col-end-5"
          v-model="deliverer"
          @change="setDeliverer"
          :disabled="urlParams.id"
        >
          <option>изберете</option>
          <option
            v-for="(del, index) in deliverers"
            :value="del.name"
            :key="index"
            v-text="del.name"
          ></option>
        </select>
        <div
          v-else
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="document_data.deliverer_name"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">адрес:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="deliverer_address"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="
            deliverer === 'изберете'
              ? ''
              : deliverers[selectedDeliverer].address
          "
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="deliverer_address"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="document_data.deliverer_address"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">ин:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="deliverer_in"
          class="custom-input truncate"
          v-text="
            deliverer === 'изберете' ? '' : deliverers[selectedDeliverer].idn
          "
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="deliverer_in"
          class="custom-input truncate"
          v-text="document_data.deliverer_ein"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">по ддс:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="deliverer_indds"
          class="custom-input truncate"
          v-text="
            deliverer === 'изберете'
              ? ''
              : deliverers[selectedDeliverer].idn_dds
          "
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="deliverer_indds"
          class="custom-input truncate"
          v-text="document_data.deliverer_ein_dds"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">мол:</div>
        <div
          v-if="!urlParams.id"
          v-show="!headerCollapse"
          id="deliverer_mol"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="
            deliverer === 'изберете'
              ? ''
              : deliverers[selectedDeliverer].jur_mol
          "
        ></div>
        <div
          v-else
          v-show="!headerCollapse"
          id="deliverer_mol"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="document_data.deliverer_mol"
        ></div>
      </div>
    </div>

    <div v-if="showToolbar" class="flex w-full items-center my-2">
      <label
        v-if="showLastPaid"
        class="custom-label last-paid mr-2"
        for="last_paid"
        >падеж:</label
      >

      <datepicker
        v-if="showLastPaid"
        title="падеж..."
        wrapperClass="last-paid-picker"
        autocomplete="off"
        placeholder="xx.xxxx"
        inputClass="custom-input"
        id="last_paid"
        format="MMMM YYYY"
        :minimumView="'month'"
        :maximumView="'year'"
        v-model="duty_date"
        @input="getClientObligations"
      />

      <div
        v-if="
          document_data.is_advice ||
          document_data.id_advice ||
          is_new_relative_doc
        "
        class="flex items-center px-2 border-l-4 border-orange-500"
      >
        <div class="flex flex-col">
          <div
            @click="openRelativeDocument(origin_document.id || null)"
            :class="
              !is_new_relative_doc
                ? (document_data.doc_type == 'kreditno izvestie' || document_data.doc_type == 'debitno izvestie')
                  ? `text-xs lowercase text-black cursor-pointer hover:bg-gray-200 rounded-sm focus:outline-none focus:shadow-outline`
                  : `text-xss uppercase text-gray-500`
                : 'text-xss uppercase text-gray-500'
            "
            class="w-full font-medium leading-4 text-left"
            v-text="
              !is_new_relative_doc
                ? (document_data.doc_type == 'kreditno izvestie' || document_data.doc_type == 'debitno izvestie')
                  ? `към фактура: ${origin_document.doc_num} от ${(toDDMMYYYY(origin_document.doc_date_create))} г.`
                  : `има издадено кредитно/дебитно известие:`
                : `${this.docType.name} към фактура:`
            "
          >
          </div>
          <button
            v-if="is_new_relative_doc"
            @click="openRelativeDocument(relative_document.id)"
            type="button"
            class="text-xs text-indigo-500 text-left font-medium tracking-wide leading-4 w-auto hover:bg-gray-200 rounded-sm focus:outline-none focus:shadow-outline"
          > 
            {{ relative_document.doc_num }} от
            {{ toDDMMYYYY(relative_document.doc_date) }} г. /
            {{ relative_document.total_sum | price }} лв.
          </button>
          <button
            v-else
            @click="openRelativeDocumentsList"
            type="button"
            class="text-xs text-indigo-500 text-left font-medium tracking-wide leading-4 w-auto hover:bg-gray-200 rounded-sm focus:outline-none focus:shadow-outline"
          >
            списък релативни документи
          </button>
        </div>
      </div>

      <div
        v-if="urlParams.is_book || document_data.is_book == 1"
        class="flex items-center justify-center w-auto h-8 bg-blue-500 rounded-sm text-white text-xs px-2 rounded-sm tracking-wide font-medium mr-2"
      >
        ФАКТУРА ОТ КОЧАН
      </div>
      <div
        v-if="document_data.doc_status === 'canceled'"
        class="flex items-center justify-center w-auto h-8 bg-pink-500 rounded-sm text-white text-xs px-2 rounded-sm tracking-wide font-medium ml-auto mr-2"
      >
        АНУЛИРАН
      </div>

      <!--
      <button
        v-if="!urlParams.id"
        title="ценообразуване"
        @click="objectsPricingModal = true"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2 ml-auto"
      >
        <i class="fad fa-calculator-alt"></i>
      </button>-->

      <button
        v-if="urlParams.id && allowRelativeDocuments"
        title="Дебитно известие"
        v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
        @click="addDebitSaleDoc"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2 ml-auto"
      >
        <i class="fad fa-layer-plus fa-lg fa-fw"></i>
      </button>

      <button
        v-if="urlParams.id && allowRelativeDocuments"
        title="Кредитно известие"
        v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
        @click="addCreditSaleDoc"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:bg-indigo-400 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2"
      >
        <i class="fad fa-layer-minus fa-lg fa-fw"></i>
      </button>

      <button
        title="Добави ред"
        v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
        v-if="allowNewRows"
        @click="showFreeSaleComponent = true"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 text-sm mr-2 ml-auto"
      >
        <i class="far fa-fw fa-plus"></i>
      </button>
      
      <button
        :title="headerCollapse ? 'Разшири' : 'Смали'"
        v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
        :class="
          (!is_new_relative_doc && document_data.doc_type === 'kreditno izvestie') ||
          (!is_new_relative_doc && document_data.doc_type === 'debitno izvestie') ||
          (!is_new_relative_doc && urlParams.id && document_data.doc_type === 'oprostena') ||
          (!is_new_relative_doc && urlParams.id && document_data.doc_type === 'kvitanciq')
            ? document_data.doc_status ==='canceled' ? '' : 'ml-auto'
            : ''
        "
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 text-sm"
        @click="headerCollapse = !headerCollapse"
      >
        <i
          :class="
            headerCollapse
              ? 'fal fa-arrows-v fa-fw'
              : 'fal fa-arrow-to-top fa-fw'
          "
        ></i>
      </button>
    </div>
    <!-- grid -->
    <div
      id="grid"
      v-if="doc_rows.length"
      class="grid-data mb-2 border-b border-gray-200 shadow-sm"
    >
      <!-- headers -->
      <div class="grid-row headers shadow">
        <div v-if="!urlParams.id" class="grid-cell check">
          <i class="fal fa-square fa-fw"></i>
        </div>
        <div
          :class="urlParams.id ? 'col-span-2' : ''"
          class="grid-cell text-center"
        >
          №
        </div>
        <div
          :class="
            client.invoice_layout === 'single' ||
            client.invoice_layout === 'by_objects'
              ? 'col-start-3 col-end-5 grid-cell name'
              : client.invoice_layout === 'by_services'
              ? 'hidden'
              : 'grid-cell name'
          "
          v-text="
            client.invoice_layout === 'single'
              ? 'услуга'
              : client.invoice_layout === 'by_objects'
              ? 'обект'
              : 'обект / за месец'
          "
        ></div>
        <div
          :class="
            client.invoice_layout === 'single' ||
            client.invoice_layout === 'by_objects'
              ? 'hidden'
              : client.invoice_layout === 'by_services'
              ? 'col-start-3 col-end-5 grid-cell service'
              : 'grid-cell service'
          "
        >
          услуга
        </div>
        <div class="grid-cell quantity">количество</div>
        <div class="grid-cell single">ед. цена</div>
        <div class="grid-cell total">сума</div>
      </div>
      <!-- единичен -->
      <template v-if="client.invoice_layout === 'single'">
        <div class="grid-row">
          <!-- check -->
          <div v-if="!urlParams.id" class="grid-cell">
            <label class="row-checkbox">
              <input
                :checked="servicesForPayment.length > 0"
                type="checkbox"
                @change="toggleAllServices($event)"
              />
              <span></span>
            </label>
          </div>
          <!-- row num -->
          <div
            :class="urlParams.id ? 'col-span-2' : ''"
            class="grid-cell row-num"
          >
            1
          </div>
          <!-- single view name -->
          <div class="grid-cell col-start-3 col-end-5">
            <div
              class="primary-info font-medium w-full focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200"
              contenteditable
              spellcheck="false"
              :title="document_data.single_view_name"
              v-html="document_data.single_view_name"
              @focus="beginTextEdit($event)"
              @keydown.enter.prevent="
                editTextRow($event, document_data, 'single_view_name')
              "
              @blur="restoreTextIfNotConfirmed($event)"
            ></div>
          </div>
          <!-- quantity -->
          <div class="grid-cell text-right">
            <div class="primary-info">1</div>
            <div class="secondary-info">бр.</div>
          </div>
          <!-- single price -->
          <div :title="(baseSum ? baseSum : allRowsTotal)" class="grid-cell text-right">
            <div class="primary-info">{{ (baseSum ? baseSum : allRowsTotal) | price }}</div>
            <div class="secondary-info">лв.</div>
          </div>
          <!-- total-sum -->
          <div :title="(baseSum ? baseSum : allRowsTotal)" class="grid-cell text-right">
            <div class="primary-info">{{ (baseSum ? baseSum : allRowsTotal) | price }}</div>
            <div class="secondary-info">лв.</div>
          </div>
        </div>
      </template>
      <!-- подробен -->
      <template
        v-else-if="
          client.invoice_layout === 'extended' ||
          (doc_type === 'oprostena' && !urlParams.id)
        "
      >
        <div
          :class="!service.for_payment ? 'opacity-50 grid-row' : 'grid-row'"
          v-for="(service, index) in doc_rows"
          :key="service.uuid"
        >
          <!-- check -->
          <div v-if="!urlParams.id" class="grid-cell">
            <label class="row-checkbox">
              <input
                v-if="service.id_object !== 0 && service.type === 'month'"
                type="checkbox"
                v-model="service.for_payment"
                @change="
                  toggleMonthlyServicesForObject(
                    service.id_object,
                    service.for_payment,
                    service.month
                  )
                "
              />
              <input
                v-else-if="service.id_object !== 0 && service.type === 'free'"
                type="checkbox"
                v-model="service.for_payment"
                @change="
                  toggleObjectDiscount(service.id_object, service.for_payment)
                "
              />
              <input v-else type="checkbox" v-model="service.for_payment" />
              <span></span>
            </label>
          </div>
          <!-- row num -->
          <div
            :class="urlParams.id ? 'col-span-2' : ''"
            class="grid-cell row-num"
          >
            {{ index + 1 }}
          </div>
          <!-- object name -->
          <div class="grid-cell">
            <div class="flex primary-info">
              <div
                class="font-medium w-full truncate focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200 mr-2"
                contenteditable
                spellcheck="false"
                :title="service.object_name"
                v-html="service.object_name"
                @focus="beginTextEdit($event)"
                @keydown.enter.prevent="
                  editTextRow($event, service, 'object_name')
                "
                @blur="restoreTextIfNotConfirmed($event)"
              ></div>
              <button
                class="obj-link invisible ml-auto mr-2 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
                @click="handleLinkAction(service)"
              >
                <i
                  :class="
                    service.id_object
                      ? 'fad fa-external-link fa-fw'
                      : !urlParams.id || is_new_relative_doc
                        ? 'fad fa-edit fa-fw'
                         : 'hidden'
                  "
                ></i>
              </button>
            </div>
            <div class="secondary-info truncate">
              за м. {{ service.month | date }}
            </div>
          </div>
          <!-- service -->
          <div class="grid-cell">
            <div
              class="primary-info font-medium w-full truncate focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200"
              contenteditable
              spellcheck="false"
              :title="service.service_name"
              v-html="service.service_name"
              @focus="beginTextEdit($event)"
              @keydown.enter.prevent="
                editTextRow($event, service, 'service_name')
              "
              @blur="restoreTextIfNotConfirmed($event)"
            ></div>
            <div class="secondary-info truncate">
              <span
                @click="showNomenclature(service)"
                class="underline cursor-pointer tracking-wider"
                >{{ service.firm }} - {{ service.region }}</span
              >
            </div>
          </div>
          <!-- quantity -->
          <div class="grid-cell text-right">
            <div class="primary-info">{{ service.quantity }}</div>
            <div class="secondary-info">{{ service.measure }}</div>
          </div>
          <!-- single price -->
          <div :title="service.single_price" class="grid-cell text-right">
            <div class="primary-info">{{ service.single_price | price }}</div>
            <div class="secondary-info">лв.</div>
          </div>
          <!-- total sum -->
          <div :title="service.total_sum" class="grid-cell text-right">
            <div class="primary-info">
              {{ service.total_sum | price }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
        </div>
      </template>
      <!-- по обекти -->
      <template v-else-if="client.invoice_layout === 'by_objects'">
        <div
          :class="
            object.for_payment.checked || object.for_payment === true
              ? 'grid-row'
              : 'grid-row opacity-50'
          "
          v-for="(object, index) in objectsTreeView"
          :key="index"
        >
          <!-- check -->
          <div v-if="!urlParams.id" class="grid-cell">
            <label class="row-checkbox">
              <input
                v-if="object.hasOwnProperty('monthly')"
                type="checkbox"
                v-model="object.for_payment.checked"
                @change="
                  toggleObjectServices(
                    object.id_object,
                    object.for_payment.checked
                  )
                "
              />
              <input v-else type="checkbox" v-model="object.for_payment" />
              <span></span>
            </label>
          </div>
          <!-- row num -->
          <div
            :class="urlParams.id ? 'col-span-2' : ''"
            class="grid-cell row-num"
          >
            {{ index + 1 }}
          </div>
          <!-- object name -->
          <div class="grid-cell col-start-3 col-end-5">
            <div class="flex primary-info">
              <div
                class="font-medium w-full truncate mr-2 focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200"
                contenteditable
                spellcheck="false"
                :title="object.id_object ? object.name : object.object_name"
                v-html="object.id_object ? object.name : object.object_name"
                @focus="beginTextEdit($event)"
                @keydown.enter.prevent="
                  editTextRow(
                    $event,
                    object.hasOwnProperty('monthly')
                      ? object.monthly.months[0].services[0]
                      : object,
                    'object_name'
                  )
                "
                @blur="restoreTextIfNotConfirmed($event)"
              ></div>
              <button
                class="obj-link invisible ml-auto mr-2 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
                @click="handleLinkAction(object)"
              >
                <i
                  :class="
                    object.id_object
                      ? 'fad fa-external-link fa-fw'
                      : !urlParams.id
                        ? 'fad fa-edit fa-fw'
                         : 'hidden'
                  "
                ></i>
              </button>
            </div>
            <div v-if="object.id_object" class="flex secondary-info truncate">
              <button
                class="focus:outline-none mr-2"
                @click="showObjectPricing(object)"
              >
                <i class="fad fa-folder-tree fa-fw text-indigo-400"></i>
              </button>
              <span v-if="object.monthly" class="truncate"
                >[ месечни такси:
                {{ object.monthly.totalForPayment | price }} от
                {{ object.monthly.totalSum | price }} лв. ]</span
              >
              <span v-if="object.discounts" class="truncate mx-1"
                >[ отстъпки: {{ object.discounts.totalForPayment | price }} от
                {{ object.discounts.totalSum | price }} лв. ]</span
              >
              <span v-if="object.singles" class="truncate"
                >[ еднократни: {{ object.singles.totalForPayment | price }} от
                {{ object.singles.totalSum | price }} лв. ]</span
              >
            </div>
            <div v-else class="flex secondary-info truncate">
              <button
                @click="handleLinkAction(object)"
                class="focus:outline-none mr-2"
              >
                <i class="fad fa-folder-tree fa-fw text-indigo-400"></i>
              </button>
              <span class="truncate"
                >[ {{ object.service_name }} {{ object.quantity }}
                {{ object.measure }} x {{ object.single_price | price }} лв.
                ]</span
              >
            </div>
          </div>
          <!-- quantity -->
          <div class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("services")
                  ? object.services[0].quantity
                  : object.quantity
              }}
            </div>
            <div class="secondary-info">
              {{
                object.hasOwnProperty("services")
                  ? object.services[0].measure
                  : object.measure
              }}
            </div>
          </div>
          <!-- single price -->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : object.single_price" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : object.single_price | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
          <!-- total sum -->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : (object.single_price * object.quantity)" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : (object.single_price * object.quantity) | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
        </div>
      </template>
      <!-- по месеци -->
      <template v-else-if="client.invoice_layout === 'detail'">
        <div
          :class="
            object.for_payment.checked || object.for_payment === true
              ? 'grid-row'
              : 'grid-row opacity-50'
          "
          v-for="(object, index) in byMonths"
          :key="index"
        >
          <!-- check -->
          <div v-if="!urlParams.id" class="grid-cell">
            <label class="row-checkbox">
              <input
                v-if="
                  object.services &&
                  object.services.length &&
                  object.services[0].id_object !== 0 &&
                  object.services[0].type === 'month'
                "
                type="checkbox"
                v-model="object.for_payment.checked"
                @change="
                  toggleMonthlyServicesForObject(
                    object.services[0].id_object,
                    object.for_payment.checked,
                    object.month
                  )
                "
              />
              <input
                v-else-if="
                  object.services &&
                  object.services.length &&
                  object.services[0].id_object !== 0 &&
                  object.services[0].type === 'free'
                "
                type="checkbox"
                v-model="object.for_payment.checked"
                @change="
                  toggleObjectDiscount(
                    object.services[0].id_object,
                    object.for_payment.checked
                  )
                "
              />
              <input v-else type="checkbox" v-model="object.for_payment" />
              <span></span>
            </label>
          </div>
          <!-- row-num -->
          <div
            :class="urlParams.id ? 'col-span-2' : ''"
            class="grid-cell row-num"
          >
            {{ index + 1 }}
          </div>
          <!-- object name -->
          <div class="grid-cell">
            <div class="flex primary-info">
              <div
                class="font-medium w-full truncate focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200 mr-2"
                contenteditable
                spellcheck="false"
                :title="
                  object.hasOwnProperty('services')
                    ? object.services[0].object_name
                    : object.object_name
                "
                v-html="
                  object.hasOwnProperty('services')
                    ? object.services[0].object_name
                    : object.object_name
                "
                @focus="beginTextEdit($event)"
                @keydown.enter.prevent="
                  editTextRow(
                    $event,
                    object.hasOwnProperty('services')
                      ? object.services[0]
                      : object,
                    'object_name'
                  )
                "
                @blur="restoreTextIfNotConfirmed($event)"
              ></div>
              <button
                class="obj-link invisible ml-auto mr-2 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
                @click="handleLinkAction(object)"
              >
                <i
                  :class="
                    object.id_object || object.services
                      ? 'fad fa-external-link fa-fw'
                      : !urlParams.id
                        ? 'fad fa-edit fa-fw'
                         : 'hidden'
                  "
                ></i>
              </button>
            </div>
            <div class="secondary-info truncate">
              за м. {{ object.month | date }}
            </div>
          </div>
          <!-- service -->
          <div class="grid-cell">
            <div
              class="primary-info font-medium w-full truncate focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200"
              contenteditable
              spellcheck="false"
              :title="
                object.hasOwnProperty('services')
                  ? object.name
                  : object.service_name
              "
              v-html="
                object.hasOwnProperty('services')
                  ? object.name
                  : object.service_name
              "
              @focus="beginTextEdit($event)"
              @keydown.enter.prevent="
                editTextRow(
                  $event,
                  object.hasOwnProperty('services')
                    ? object.services[0]
                    : object,
                  object.hasOwnProperty('services')
                    ? 'view_type_detail'
                    : 'service_name'
                )
              "
              @blur="restoreTextIfNotConfirmed($event)"
            ></div>
            <div
              v-if="
                object.hasOwnProperty('services') &&
                object.services[0].type == 'month'
              "
              class="secondary-info truncate"
            >
              месечна такса
            </div>
            <div
              v-else-if="object.type == 'single'"
              class="secondary-info truncate"
            >
              еднократно
            </div>
            <div
              v-else-if="
                object.hasOwnProperty('services') &&
                object.id_objecto !== 0 &&
                object.services[0].type == 'free'
              "
              class="secondary-info truncate"
            >
              отстъпка
            </div>
            <div v-else class="secondary-info truncate">свободна продажба</div>
          </div>
          <!-- quntity -->
          <div class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("services")
                  ? object.services[0].quantity
                  : object.quantity
              }}
            </div>
            <div class="secondary-info">
              {{
                object.hasOwnProperty("services")
                  ? object.services[0].measure
                  : object.measure
              }}
            </div>
          </div>
          <!-- single price-->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : object.single_price" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : object.single_price | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
          <!-- total -->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : (object.single_price * object.quantity)" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : (object.single_price * object.quantity) | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
        </div>
      </template>
      <!-- по услуги -->
      <template v-else-if="client.invoice_layout === 'by_services'">
        <div
          :class="
            object.for_payment.checked || object.for_payment === true
              ? 'grid-row'
              : 'grid-row opacity-50'
          "
          v-for="(object, index) in byServices"
          :key="index"
        >
          <!-- check -->
          <div v-if="!urlParams.id" class="grid-cell">
            <label
              class="row-checkbox"
              v-if="!object.hasOwnProperty('services')"
            >
              <input type="checkbox" v-model="object.for_payment" />
              <span></span>
            </label>
          </div>
          <!-- row num -->
          <div
            :class="urlParams.id ? 'col-span-2' : ''"
            class="grid-cell row-num"
          >
            {{ index + 1 }}
          </div>
          <!-- service name -->
          <div class="grid-cell col-start-3 col-end-5">
            <div class="flex primary-info">
              <div
                class="font-medium w-full truncate focus:outline-none focus:shadow-outline focus:bg-white transition ease-in-out duration-200 mr-2"
                contenteditable
                spellcheck="false"
                :title="
                  !object.id_object
                    ? object.type === 'free' && !object.services
                      ? object.object_name
                      : object.name
                    : object.service_name
                "
                v-html="
                  !object.id_object
                    ? object.type === 'free' && !object.services
                      ? object.object_name
                      : object.name
                    : object.service_name
                "
                @focus="beginTextEdit($event)"
                @keydown.enter.prevent="
                  editTextRow(
                    $event,
                    object,
                    (!object.id_object &&
                      object.hasOwnProperty('services') &&
                      object.type === 'month') ||
                      (!object.id_object &&
                        object.hasOwnProperty('services') &&
                        object.type === 'free')
                      ? 'view_type_by_services'
                      : object.id_object
                      ? 'service_name'
                      : 'object_name'
                  )
                "
                @blur="restoreTextIfNotConfirmed($event)"
              ></div>
              <button
                v-if="!object.services"
                class="obj-link invisible ml-auto mr-2 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
                @click="handleLinkAction(object)"
              >
                <i
                  :class="
                    object.id_object
                      ? 'fad fa-external-link fa-fw'
                      : !urlParams.id
                        ? 'fad fa-edit fa-fw'
                         : 'hidden'
                  "
                ></i>
              </button>
            </div>
            <div
              v-if="object.services && object.type === 'month'"
              class="secondary-info truncate"
              v-text="
                object.services[0].for_smartsot
                  ? 'месечни такси [ смарт сот ]'
                  : 'месечни такси'
              "
            ></div>
            <div
              v-else-if="object.services && object.type === 'free'"
              class="secondary-info truncate"
            >
              отстъпки
            </div>
            <div
              v-else-if="object.id_object && object.type === 'single'"
              class="secondary-info truncate"
              :title="`еднократнo задължение [ ${object.object_name} / ${object.firm} - ${object.region} ]`"
            >
              еднократнo задължение [ {{ object.object_name }} /
              {{ object.firm }} - {{ object.region }} ]
            </div>
            <div v-else class="secondary-info truncate">свободна продажба</div>
          </div>
          <!-- quantity -->
          <div class="grid-cell text-right">
            <div class="primary-info">{{ object.quantity }}</div>
            <div class="secondary-info">{{ object.measure }}</div>
          </div>
          <!-- single price -->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : object.single_price" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : object.single_price | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
          <!-- total -->
          <div :title="object.hasOwnProperty('totalSum') ? object.totalSum : (object.single_price * object.quantity)" class="grid-cell text-right">
            <div class="primary-info">
              {{
                object.hasOwnProperty("totalSum")
                  ? object.totalSum
                  : (object.single_price * object.quantity) | price
              }}
            </div>
            <div class="secondary-info">лв.</div>
          </div>
        </div>
      </template>
    </div>
    <div class="flex-1"></div>

    <!-- за триене или доработка ? -->
    <baseDialog v-if="objectPricingModal" @close="closeObjectPricingModal()">
      <template #header>
        <div class="flex items-center bg-white px-3 py-2 border-b">
          <div class="primary-info font-medium truncate">
            {{ objectsTreeView[objectPricingTreeIndex].name }}
          </div>
          <div class="primary-info primary-info ml-auto mr-2 w-48 text-right">
            {{
              objectsTreeView[objectPricingTreeIndex].totalForPayment | price
            }}
            лв.
          </div>
          <button @click="closeObjectPricingModal()" class="p-2 text-xs">
            <i class="fal fa-times fa-fw"></i>
          </button>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col bg-white overflow-y-auto">
          <div
            class="p-3 w-full"
            v-if="objectsTreeView[objectPricingTreeIndex].monthly"
          >
            <div
              class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
            >
              <span class="mr-2">месечни такси</span>
              <span
                >{{
                  objectsTreeView[objectPricingTreeIndex].monthly
                    .totalForPayment | price
                }}
                лв.</span
              >
            </div>

            <div class="soft-shadow">
              <div
                :class="
                  objectMonthlyDetailIndex === index ? 'border-transparent' : ''
                "
                class="flex flex-col w-full items-start text-xs border-b border-gray-100 last:border-transparent"
                v-for="(month, index) in objectsTreeView[objectPricingTreeIndex]
                  .monthly.months"
                :key="index"
              >
                <div
                  :class="
                    objectMonthlyDetailIndex === index
                      ? 'rounded-sm bg-gray-100'
                      : ''
                  "
                  class="flex items-center justify-between w-full p-2"
                >
                  <label class="row-checkbox">
                    <input
                      type="checkbox"
                      v-model="month.for_payment.checked"
                      @change="
                        toggleMonthlyServicesForObject(
                          objectsTreeView[objectPricingTreeIndex].id_object,
                          month.for_payment.checked,
                          month.month,
                          objectsTreeView[objectPricingTreeIndex].monthly
                            .months,
                          objectsTreeView[objectPricingTreeIndex].discounts
                        )
                      "
                    />
                    <span></span>
                  </label>
                  <div
                    class="w-full flex items-center text-xs font-medium"
                    :class="
                      objectMonthlyDetailIndex === index ? 'text-blue-500' : ''
                    "
                  >
                    <span class="ml-2">{{ month.name }}</span>
                    <span class="ml-auto mr-2"
                      >{{ month.totalSum | price }} лв.</span
                    >
                  </div>
                  <button
                    @click="toggleObjectDetailMonthlyPricing(index)"
                    class="w-6 h-6 flex flex-none items-center justify-center text-xs text-black focus:outline-none hover:text-blue-500"
                  >
                    <i
                      :class="
                        objectMonthlyDetailIndex === index
                          ? 'fa-chevron-up'
                          : 'fa-chevron-down'
                      "
                      class="fa fa-fw"
                    ></i>
                  </button>
                </div>

                <div
                  v-if="
                    showObjectMonthlyPricing &&
                    objectMonthlyDetailIndex === index
                  "
                  class="px-2 w-full text-xs text-black"
                >
                  <div
                    v-for="(tax, index) in month.services"
                    :key="index"
                    class="flex items-center w-full my-1 justify-between"
                  >
                    <span class="mr-2 truncate">{{ tax.service_name }}</span>
                    <span
                      >{{ (tax.single_price * tax.quantity) | price }} лв.</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- discounts -->
          <div
            class="px-3 pb-3 w-full"
            v-if="objectsTreeView[objectPricingTreeIndex].discounts"
          >
            <div
              class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
            >
              <span class="mr-2">отстъпки</span>
              <span
                >{{
                  objectsTreeView[objectPricingTreeIndex].discounts
                    .totalForPayment | price
                }}
                лв.</span
              >
            </div>

            <div class="flex flex-col w-full items-start text-xs soft-shadow">
              <div
                class="flex items-center justify-between w-full p-2"
                :class="
                  objectDiscountDetail
                    ? 'rounded-sm bg-gray-100 border-x border-transperant'
                    : ''
                "
              >
                <label class="row-checkbox">
                  <input
                    type="checkbox"
                    v-model="
                      objectsTreeView[objectPricingTreeIndex].discounts
                        .for_payment.checked
                    "
                    @change="
                      toggleObjectDiscount(
                        objectsTreeView[objectPricingTreeIndex].id_object,
                        objectsTreeView[objectPricingTreeIndex].discounts
                          .for_payment.checked
                      )
                    "
                  />
                  <span></span>
                </label>

                <div class="w-full flex items-center text-xs font-medium">
                  <span
                    :class="objectDiscountDetail ? 'text-blue-500' : ''"
                    class="ml-2"
                    >{{
                      objectsTreeView[objectPricingTreeIndex].discounts.name
                    }}</span
                  >
                  <span class="ml-auto mr-2"
                    >{{
                      objectsTreeView[objectPricingTreeIndex].discounts.totalSum
                        | price
                    }}
                    лв.</span
                  >
                </div>
                <button
                  v-if="
                    objectsTreeView[objectPricingTreeIndex].discounts.services
                      .length > 1
                  "
                  @click="objectDiscountDetail = !objectDiscountDetail"
                  class="w-6 h-6 flex flex-none items-center justify-center text-xs text-black focus:outline-none hover:text-blue-500"
                >
                  <i
                    :class="
                      objectDiscountDetail ? 'fa-chevron-up' : 'fa-chevron-down'
                    "
                    class="fa fa-fw"
                  ></i>
                </button>
              </div>
              <template v-if="objectDiscountDetail">
                <div class="px-2 border border-gray-200 border-t-0 w-full">
                  <div
                    v-for="(discount, index) in objectsTreeView[
                      objectPricingTreeIndex
                    ].discounts.services"
                    :key="index"
                    class="flex items-center w-full my-1 justify-between"
                  >
                    <span class="mr-2 truncate">{{
                      discount.reference_service_name
                    }}</span>
                    <span class>{{ discount.total_sum | price }} лв.</span>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- singles -->
          <div
            class="px-3 pb-3 w-full"
            v-if="objectsTreeView[objectPricingTreeIndex].singles"
          >
            <div
              class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
            >
              <span class="mr-2">еднократни задължения</span>
              <span
                >{{
                  objectsTreeView[objectPricingTreeIndex].singles
                    .totalForPayment | price
                }}
                лв.</span
              >
            </div>

            <div
              class="flex flex-col w-full items-start text-xs border-b border-gray-100 last:border-transparent soft-shadow"
            >
              <div
                class="flex items-center justify-between w-full p-2"
                v-for="(single, index) in objectsTreeView[
                  objectPricingTreeIndex
                ].singles.services"
                :key="index"
              >
                <label class="row-checkbox">
                  <input
                    type="checkbox"
                    v-model="
                      objectsTreeView[objectPricingTreeIndex].singles.services[
                        index
                      ].for_payment
                    "
                  />
                  <span></span>
                </label>
                <div class="w-full flex items-center text-xs font-medium">
                  <span class="mr-2 truncate">
                    {{ toDDMMYYYY(single.month) }}
                    {{ single.service_name }}
                  </span>
                  <span class="ml-auto mr-2"
                    >{{
                      (single.single_price * single.quantity) | price
                    }}
                    лв.</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <div class="flex items-center justify-end bg-white px-3 py-2 border-t">
          <button
            @click="closeObjectPricingModal()"
            class="w-32 border rounded-sm bg-white p-2 text-xs text-gray-600 border-gray-400"
          >
            затвори
          </button>
        </div>
      </template>
    </baseDialog>

    <!-- за триене или доработка ? -->
    <baseDialog v-if="objectsPricingModal" @close="closeObjectsPricingModal()">
      <template #header>
        <div class="flex items-center bg-white px-3 py-2 border-b">
          <div class="primary-info font-medium truncate">ценообразуване</div>
          <div class="primary-info primary-info ml-auto mr-2 w-48 text-right">
            ...
          </div>
          <button @click="closeObjectsPricingModal()" class="p-2 text-xs">
            <i class="fal fa-times fa-fw"></i>
          </button>
        </div>
      </template>

      <template #body>
        <div class="flex flex-col bg-white overflow-y-auto">
          <div class="p-3 w-full">
            <div class="soft-shadow">
              <div
                class="flex flex-col w-full items-start text-xs border-b border-gray-100 last:border-transparent"
                v-for="(object, index) in objectsTreeView"
                :key="index"
              >
                <div class="flex items-center justify-between w-full p-2">
                  <label class="row-checkbox">
                    <input
                      v-if="object.hasOwnProperty('monthly')"
                      type="checkbox"
                      v-model="object.for_payment.checked"
                      @change="
                        toggleObjectServices(
                          object.id_object,
                          object.for_payment.checked
                        )
                      "
                    />
                    <input
                      v-else
                      type="checkbox"
                      v-model="object.for_payment"
                    />
                    <span></span>
                  </label>
                  <div
                    class="w-full flex items-center text-xs font-medium truncate"
                  >
                    <span
                      class="mx-2 truncate"
                      v-text="
                        object.id_object ? object.name : object.object_name
                      "
                    ></span>
                    <span class="ml-auto mr-2"
                      >{{
                        object.hasOwnProperty("totalForPayment")
                          ? object.totalForPayment
                          : (object.single_price * object.quantity) | price
                      }}
                      лв.</span
                    >
                  </div>
                  <button
                    v-if="object.id_object"
                    @click="toggleObjectsPricingIndex(index)"
                    class="w-6 h-6 flex flex-none items-center justify-center text-xs text-black focus:outline-none hover:text-blue-500"
                  >
                    <i
                      :class="
                        objectsPricingTreeIndex === index
                          ? 'fa-chevron-up'
                          : 'fa-chevron-down'
                      "
                      class="fa fa-fw"
                    ></i>
                  </button>
                </div>
                <div
                  class="flex flex-col w-full"
                  v-if="objectPricingTreeIndex === index"
                >
                  <div
                    class="p-3 w-full"
                    v-if="objectsTreeView[objectPricingTreeIndex].monthly"
                  >
                    <div
                      class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
                    >
                      <span class="mr-2">месечни такси</span>
                      <span
                        >{{
                          objectsTreeView[objectPricingTreeIndex].monthly
                            .totalForPayment | price
                        }}
                        лв.</span
                      >
                    </div>

                    <div class="soft-shadow">
                      <div
                        :class="
                          objectMonthlyDetailIndex === index
                            ? 'border-transparent'
                            : ''
                        "
                        class="flex flex-col w-full items-start text-xs border-b border-gray-100 last:border-transparent"
                        v-for="(month, index) in objectsTreeView[
                          objectPricingTreeIndex
                        ].monthly.months"
                        :key="index"
                      >
                        <div
                          :class="
                            objectMonthlyDetailIndex === index
                              ? 'rounded-sm bg-gray-100'
                              : ''
                          "
                          class="flex items-center justify-between w-full p-2"
                        >
                          <label class="row-checkbox">
                            <input
                              type="checkbox"
                              v-model="month.for_payment.checked"
                              @change="
                                toggleMonthlyServicesForObject(
                                  objectsTreeView[objectPricingTreeIndex]
                                    .id_object,
                                  month.for_payment.checked,
                                  month.month,
                                  objectsTreeView[objectPricingTreeIndex]
                                    .monthly.months,
                                  objectsTreeView[objectPricingTreeIndex]
                                    .discounts
                                )
                              "
                            />
                            <span></span>
                          </label>
                          <div
                            class="w-full flex items-center text-xs font-medium"
                            :class="
                              objectMonthlyDetailIndex === index
                                ? 'text-blue-500'
                                : ''
                            "
                          >
                            <span class="ml-2">{{ month.name }}</span>
                            <span class="ml-auto mr-2"
                              >{{ month.totalSum | price }} лв.</span
                            >
                          </div>
                          <button
                            @click="toggleObjectDetailMonthlyPricing(index)"
                            class="w-6 h-6 flex flex-none items-center justify-center text-xs text-black focus:outline-none hover:text-blue-500"
                          >
                            <i
                              :class="
                                objectMonthlyDetailIndex === index
                                  ? 'fa-chevron-up'
                                  : 'fa-chevron-down'
                              "
                              class="fa fa-fw"
                            ></i>
                          </button>
                        </div>

                        <div
                          v-if="
                            showObjectMonthlyPricing &&
                            objectMonthlyDetailIndex === index
                          "
                          class="px-2 w-full text-xs text-black"
                        >
                          <div
                            v-for="(tax, index) in month.services"
                            :key="index"
                            class="flex items-center w-full my-1 justify-between"
                          >
                            <span class="mr-2 truncate">{{
                              tax.service_name
                            }}</span>
                            <span
                              >{{
                                (tax.single_price * tax.quantity) | price
                              }}
                              лв.</span
                            >
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- discounts -->
                  <div
                    class="px-3 pb-3 w-full"
                    v-if="objectsTreeView[objectPricingTreeIndex].discounts"
                  >
                    <div
                      class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
                    >
                      <span class="mr-2">отстъпки</span>
                      <span
                        >{{
                          objectsTreeView[objectPricingTreeIndex].discounts
                            .totalForPayment | price
                        }}
                        лв.</span
                      >
                    </div>

                    <div
                      class="flex flex-col w-full items-start text-xs soft-shadow"
                    >
                      <div
                        class="flex items-center justify-between w-full p-2"
                        :class="
                          objectDiscountDetail
                            ? 'rounded-sm bg-gray-100 border-x border-transperant'
                            : ''
                        "
                      >
                        <label class="row-checkbox">
                          <input
                            type="checkbox"
                            v-model="
                              objectsTreeView[objectPricingTreeIndex].discounts
                                .for_payment.checked
                            "
                            @change="
                              toggleObjectDiscount(
                                objectsTreeView[objectPricingTreeIndex]
                                  .id_object,
                                objectsTreeView[objectPricingTreeIndex]
                                  .discounts.for_payment.checked
                              )
                            "
                          />
                          <span></span>
                        </label>

                        <div
                          class="w-full flex items-center text-xs font-medium"
                        >
                          <span
                            :class="objectDiscountDetail ? 'text-blue-500' : ''"
                            class="ml-2"
                            >{{
                              objectsTreeView[objectPricingTreeIndex].discounts
                                .name
                            }}</span
                          >
                          <span class="ml-auto mr-2"
                            >{{
                              objectsTreeView[objectPricingTreeIndex].discounts
                                .totalSum | price
                            }}
                            лв.</span
                          >
                        </div>
                        <button
                          v-if="
                            objectsTreeView[objectPricingTreeIndex].discounts
                              .services.length > 1
                          "
                          @click="objectDiscountDetail = !objectDiscountDetail"
                          class="w-6 h-6 flex flex-none items-center justify-center text-xs text-black focus:outline-none hover:text-blue-500"
                        >
                          <i
                            :class="
                              objectDiscountDetail
                                ? 'fa-chevron-up'
                                : 'fa-chevron-down'
                            "
                            class="fa fa-fw"
                          ></i>
                        </button>
                      </div>
                      <template v-if="objectDiscountDetail">
                        <div
                          class="px-2 border border-gray-200 border-t-0 w-full"
                        >
                          <div
                            v-for="(discount, index) in objectsTreeView[
                              objectPricingTreeIndex
                            ].discounts.services"
                            :key="index"
                            class="flex items-center w-full my-1 justify-between"
                          >
                            <span class="mr-2 truncate">{{
                              discount.reference_service_name
                            }}</span>
                            <span class
                              >{{ discount.total_sum | price }} лв.</span
                            >
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>

                  <!-- singles -->
                  <div
                    class="px-3 pb-3 w-full"
                    v-if="objectsTreeView[objectPricingTreeIndex].singles"
                  >
                    <div
                      class="flex items-center justify-between text-xs font-medium truncate max-w-full mb-3"
                    >
                      <span class="mr-2">еднократни задължения</span>
                      <span
                        >{{
                          objectsTreeView[objectPricingTreeIndex].singles
                            .totalForPayment | price
                        }}
                        лв.</span
                      >
                    </div>

                    <div
                      class="flex flex-col w-full items-start text-xs border-b border-gray-100 last:border-transparent soft-shadow"
                    >
                      <div
                        class="flex items-center justify-between w-full p-2"
                        v-for="(single, index) in objectsTreeView[
                          objectPricingTreeIndex
                        ].singles.services"
                        :key="index"
                      >
                        <label class="row-checkbox">
                          <input
                            type="checkbox"
                            v-model="
                              objectsTreeView[objectPricingTreeIndex].singles
                                .services[index].for_payment
                            "
                          />
                          <span></span>
                        </label>
                        <div
                          class="w-full flex items-center text-xs font-medium"
                        >
                          <span class="mr-2 truncate">
                            {{ toDDMMYYYY(single.month) }}
                            {{ single.service_name }}
                          </span>
                          <span class="ml-auto mr-2"
                            >{{
                              (single.single_price * single.quantity) | price
                            }}
                            лв.</span
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #footer>
        <div class="flex items-center justify-end bg-white px-3 py-2 border-t">
          <button
            @click="closeObjectsPricingModal()"
            class="w-32 border rounded-sm bg-white p-2 text-xs text-gray-600 border-gray-400"
          >
            затвори
          </button>
        </div>
      </template>
    </baseDialog>

    <freeSaleComponent
      v-if="showFreeSaleComponent"
      :user_office_id="document_data.user_office_id"
      :regions="regions"
      :firms="selectedFirms"
      :service="serviceForEdit"
      :services="services"
      :edit="serviceForEditType.edit"
      :date="document_data.doc_date_create"
      :current_document="document_data"
      :relative_document="relative_document"
      :relative_credit_maxValue="relative_credit_maxValue"
      @closeFreeSale="closeService"
      @addService="addService"
      @updateService="updateService"
      @deleteService="deleteService"
    />

    <nomenclature
      v-if="showNomenclatureDialog"
      :regions="regions"
      :firms="selectedFirms"
      :service="serviceForEdit"
      :services="services"
      @closeNomenclature="closeNomenlcature"
    />

    <payment-form
      v-if="showPaymentForm && loaded"
      @closePaymentForm="showPaymentForm = false"
      @reload="(showPaymentForm = false), getDocumentByID()"
      :doc_type="document_data.doc_type"
      :doc_id="urlParams.id"
      :doc_status="document_data.doc_status"
      :doc_sum="document_data.total_sum"
      :paid_sum="document_data.orders_sum"
      :orders="orders"
      :bank_accounts="bank_orders"
      :id_cash_default="idCashDefault"
    />

    <relative-documents-list
      :id_sale_doc="relativeId"
      :exclude_id="document_data.id"
      v-if="showRelativeDocumentsList"
      @closeRelativeDocuemntsList="showRelativeDocumentsList = false"
    />

    <div v-if="showToolbar" class="footer mb-2">
      <div class="f-left">
        <label class="custom-label" for="client_recepient">получил:</label>
        <input
          id="client_recepient"
          class="custom-input col-start 2 col-end-end"
          type="text"
          v-model="client.invoice_recipient"
          autocomplete="off"
          spellcheck="false"
        />
        <textarea
          v-if="is_new_relative_doc || document_data.doc_type == 'debitno izvestie' || document_data.doc_type == 'kreditno izvestie'"
          autocomplete="off"
          spellcheck="false"
          placeholder="основание за издаване известие..."
          class="resize-none w-full h-18 col-start-1 col-end-3 custom-input"
          v-model.lazy="document_data.advice_reason"
        ></textarea>
        <textarea
          v-else
          v-show="!showClientNote"
          autocomplete="off"
          spellcheck="false"
          placeholder="бележка..."
          class="resize-none w-full h-18 col-start-1 col-end-3 custom-input"
          v-model.lazy="client.note"
        ></textarea>
      </div>

      <div class="f-middle">
        <label class="custom-label" for="doc_view">изглед:</label>
        <select
          id="doc_view"
          class="custom-input"
          v-model="client.invoice_layout"
          @change="setDocDataViewType($event.target.value)"
        >
          <option v-if="allowChangeViewType" value="single">единичен</option>
          <option value="extended">подробен</option>
          <option v-if="allowChangeViewType" value="detail">по месеци</option>
          <option v-if="allowChangeViewType" value="by_objects">
            по обекти
          </option>
          <option v-if="allowChangeViewType" value="by_services">
            по услуги
          </option>
        </select>
        <template v-if="urlParams.id && !is_new_relative_doc">
          <div class="custom-label">дължими:</div>
          <div class="custom-input text-right">
            {{
              (document_data.total_sum - document_data.orders_sum) | price
            }}
            лв.
          </div>
          <div class="custom-label">платени:</div>
          <div class="custom-input text-right">
            {{ document_data.orders_sum | price }} лв.
          </div>
        </template>
      </div>

      <div class="f-right">
        <div class="custom-label col-start-3 col-end-4">основа:</div>
        <div :title="baseSum" class="custom-input text-right">
          {{ baseSum | price }} лв.
        </div>

        <div v-if="showVat" class="custom-label col-start-3 col-end-4">
          ддс:
        </div>
        <div v-if="showVat" :title="vatTitle" class="custom-input text-right">
          {{ vatSum | price }} лв.
        </div>
        <div class="custom-label col-start-3 col-end-4">тотал:</div>
        <div :title="totalSum" class="custom-input text-right">
          {{ totalSum | price }} лв.
        </div>
      </div>
    </div>

    <div
      v-if="showToolbar"
      class="flex flex-wrap justify-between items-center w-full pt-2 border-t border-gray-200"
    >
      <div
        class="flex flex-shrink-0 h-8 items-center text-indigo-500 truncate mr-2"
      >
        <i
          @click="showClientNote = !showClientNote"
          class="fad fa-info-circle text-2xl mr-2 cursor-pointer"
        ></i>
        <div class="text-xs truncate">{{ clientPrefferedInvoicePayment }}</div>
      </div>
      <div
        v-if="urlParams.id && !is_new_relative_doc"
        class="flex flex-col truncate text-xss text-cool-gray-500 mr-2"
      >
        <span :title="document_data.created" class="flex mr-2">
          <span class="w-16 mr-2">създал :</span>
          <span class="truncate">{{ document_data.created }}</span>
        </span>

        <span :title="document_data.updated" class="flex mr-2">
          <span class="w-16 mr-2">редактирал :</span>
          <span class="truncate">{{ document_data.updated }}</span>
        </span>
      </div>
      <div v-if="doc_rows.length" class="flex flex-wrap w-auto ml-auto">
        <template
          v-if="
            document_data.sale_doc_edit &&
            document_data.doc_status !== 'canceled'
          "
        >
          <button
            v-if="urlParams.id && !is_new_relative_doc"
            :disable="loading"
            @click="showPaymentForm = true"
            class="w-28 h-8 p-2 shadow-custom rounded-sm bg-white text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2 select-none"
          >
            плащане
          </button>
        </template>
        <button
          v-if="urlParams.id && !is_new_relative_doc"
          :disable="loading"
          @click="printSaleDoc"
          :class="{ 'mr-2': (document_data.sale_doc_edit && document_data.doc_status !== 'canceled')}"
          class="w-28 h-8 p-2 shadow-custom rounded-sm bg-white text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 select-none"
        >
          печат
        </button>
        <template v-if="document_data.sale_doc_edit">
          <button
            v-if="
              urlParams.id &&
              !is_new_relative_doc &&
              document_data.doc_status !== 'canceled'
            "
            :disable="loading"
            @click="cancelDoc"
            class="w-28 h-8 p-2 shadow-custom rounded-sm bg-gray-500 text-white text-xss uppercase font-medium tracking-wide hover:bg-gray-600 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2 select-none"
          >
            анулирай
          </button>
        </template>
        <!-- :class="document_data.sale_doc_edit ? 'mr-2' : ''" -->
        <template v-if="document_data.sale_doc_edit">
          <button
            v-if="!urlParams.id"
            :disable="loading"
            @click="storeClientObligations"
            
            class="w-28 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 select-none"
          >
            запиши
          </button>
        </template>
        <template v-if="document_data.sale_doc_edit">
          <button
            v-if="urlParams.id && document_data.doc_status !== 'canceled'"
            :disable="loading"
            @click="updateDoc"
            v-text="is_new_relative_doc ? 'запиши' : 'потвърди'"
            class="w-28 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 select-none"
          ></button>
        </template>
      </div>
    </div>
    <Loader :fillScreen="true" v-if="loading" />
  </div>
  <Loader v-else preloader :fillScreen="true"></Loader>
</template>
<script>
import Loader from "./Loader.vue";
import Suggest from "./Suggest.vue";
import Datepicker from "./Datepicker.vue";
import BaseDialog from "./BaseDialog.vue";
import freeSaleComponent from "./freeSaleComponent.vue";
import Nomenclature from "./Nomenclature.vue";
import PaymentForm from "./PaymentForm.vue";
import RelativeDocumentsList from "./RelativeDocumentsList.vue";
import { uuidMixin } from "./uuidMixin"
import { dateMixin } from "./dateMixin"
import { utilityMixin } from "./utilityMixin"
import { openRelDocMixin } from "./openRelDocMixin"
import mixins from "./mixins";

export default {
  name: "SaleDoc",
  components: {
    Suggest,
    Datepicker,
    Loader,
    BaseDialog,
    freeSaleComponent,
    PaymentForm,
    Nomenclature,
    RelativeDocumentsList,
  },
  mixins: [
    uuidMixin,
    dateMixin,
    utilityMixin,
    openRelDocMixin,
    mixins
  ],
  data() {
    return {
      searchtwo:"",
      isBookDocNum: 0,
      relative_document: {},
      origin_document: {},
      is_new_relative_doc: false,
      search: "",
      searchNewClient: "",
      showRelativeDocumentsList: false,
      showPaymentForm: false,
      showNomenclatureDialog: false,
      showChangeClientDialog: false,
      showClientNote: false,
      showObjectMonthlyPricing: false,
      orders: [],
      showFreeSaleComponent: false,
      freeSaleComponentMode: "",
      headerCollapse: false,
      bank_accounts: [],
      bank_orders: [],
      client: {
        id: null,
        name: "",
        invoice_address: "",
        invoice_ein: "",
        invoice_ein_dds: "",
        invoice_mol: "",
        invoice_recipient: "",
        invoice_layout: "extended",
        invoice_payment: "",
        invoice_single_view_name: "",
        invoice_last_paid_caption: 0,
        phone: "",
        note: ""
      },
      default_client: {
        id: null,
        name: "",
        invoice_address: "",
        invoice_ein: "",
        invoice_ein_dds: "",
        invoice_mol: "",
        invoice_recipient: "",
        invoice_layout: "extended",
        invoice_payment: "",
        invoice_single_view_name: "услуга",
        invoice_last_paid_caption: 0,
        phone: "",
        note: ""
      },
      prefferedInvoicePayment: "",
      duty_date: "",
      doc_date_create: "",
      doc_date: "",
      doc_rows: [],
      document_data: [],
      deliverer: "изберете",
      deliverers: [],
      concessions: [],
      selectedFirm: null,
      serviceForEdit: {},
      firms: [],
      regions: [],
      services: [],
      invoice_layout: null,
      doc_type: "faktura",
      loaded: false,
      loading: false,
      objectPricingModal: false,
      objectsPricingModal: false,
      objectPricingTreeIndex: null,
      objectsPricingTreeIndex: null,
      objectMonthlyDetailIndex: null,
      objectDiscountDetail: false,
      objectDiscountTemplates: null,
      TempTextEdit: false,
      tempTextVal: "",
      doc_rows_dds: [],
      annulment: false,
      confirm_request: 0,
      request_monthlySuffix: '',
      relative_credit_maxValue: 0,
      current_view_type: '',
    };
  },
  mounted() {
    this.initSaleDoc();
  },
  methods: {
    adjustMonthlySuffix(){
      
      if(this.monthlySuffix == this.request_monthlySuffix) return;
      
      this.document_data.single_view_name = this.document_data.single_view_name.replace(new RegExp(this.request_monthlySuffix,'gi'), this.monthlySuffix)
      
      this.doc_rows
        .filter(service => service.type === 'month')
        .map(service => service.view_type_by_services = service.view_type_by_services.replace(new RegExp(this.request_monthlySuffix,'gi'), this.monthlySuffix))
      
      this.request_monthlySuffix = this.monthlySuffix;

    },
    initSaleDoc() {
      axios
        .get("api/api_sale_controller.php?action=init")
        .then(({ data } = response.data) => {
          
          if (!this.isValidResponse(data)) throw new Error("Грешка: Опитайте да заредите документа отново, ако грешката продължи да се повтаря се свържете с Администратор!");
          if (!data.document_data.sale_doc_view) throw new Error("Грешка: Нямате нужните права за достъп!");

          this.bank_orders = data.bank_orders;
          this.deliverers = data.deliverers;
          this.deliverer = data.default_deliverer;
          this.document_data = data.document_data;
          this.firms = this.arrSortByPropName(data.firms, "name");
          this.services = this.arrSortByPropName(data.services, "name");
          this.regions = this.arrSortByPropName(data.regions, "region");
          this.doc_date = data.document_data.doc_date;
          this.doc_date_create = data.document_data.doc_date_create;
          this.duty_date = data.document_data.doc_date;
          this.concessions = data.concessions;

          if (this.urlParams.id_object) {
            this.getClientObligations();
          }
          if (this.urlParams.id) {
            this.getDocumentByID();
          }
          if (!this.urlParams.id) {
            this.loaded = true;
            this.setWindowTitle();
          }
        })
        .catch((error) => {
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    },
    setDeliverer() {
      this.document_data.deliverer_name = this.deliverers[this.selectedDeliverer].name;
      this.document_data.deliverer_address = this.deliverers[this.selectedDeliverer].address;
      this.document_data.deliverer_ein = this.deliverers[this.selectedDeliverer].idn;
      this.document_data.deliverer_ein_dds = this.deliverers[this.selectedDeliverer].idn_dds;
      this.document_data.deliverer_mol = this.deliverers[this.selectedDeliverer].jur_mol;
    },
    openRelativeDocumentsList() {
      this.showRelativeDocumentsList = true;
    },
    showNomenclature(service) {
      if (service.id_object && service.type === "free") return;
      this.showNomenclatureDialog = true;
      this.serviceForEdit = service;
    },
    closeNomenlcature() {
      this.showNomenclatureDialog = false;
      this.resetServiceForEdit();
    },
    setDocDataViewType(view_type) {
      this.document_data.view_type = view_type;
      this.current_view_type = view_type;
    },
    showObjectPricing(object) {
      console.log('temporary disabled')
      return
      if(this.urlParams.id) return
      this.objectPricingTreeIndex = this.objectsTreeView.indexOf(object);
      this.objectPricingModal = true;
    },
    closeObjectPricingModal() {
      this.objectPricingModal = false;
      this.objectPricingTreeIndex = null;
      this.objectMonthlyDetailIndex = null;
    },
    closeObjectsPricingModal() {
      this.objectsPricingModal = false;
      this.objectPricingTreeIndex = null;
      this.objectsPricingTreeIndex = null;
      this.objectMonthlyDetailIndex = null;
    },
    toggleObjectsPricingIndex(index) {
      if (
        this.objectsPricingTreeIndex === null ||
        this.objectsPricingTreeIndex !== index
      ) {
        this.objectsPricingTreeIndex = index;
        this.objectPricingTreeIndex = index;
        this.objectMonthlyDetailIndex = null;
      } else {
        this.objectsPricingTreeIndex = null;
        this.objectPricingTreeIndex = null;
        this.objectMonthlyDetailIndex = null;
      }
    },
    toggleObjectDetailMonthlyPricing(index) {
      if (this.objectMonthlyDetailIndex === index) {
        this.objectMonthlyDetailIndex = null;
        this.showObjectMonthlyPricing = false;
      } else {
        this.objectMonthlyDetailIndex = index;
        this.showObjectMonthlyPricing = true;
      }
    },
    handleLinkAction(object) {
      if (object.id_object || object.services) {
        let idObj = object.id_object
          ? object.id_object
          : object.services[0].id_object;
        let serviceType = object.id_object
          ? object.type
          : object.services[0].type;
        if (serviceType === "month" || serviceType === "free" || !serviceType)
          dialogObjectTaxes(`&nID=${idObj}`);
        if (serviceType === "single")
          dialogObjectTaxes(`&nID=${idObj}`);
      } else {
        this.editService(object);
      }
    },
    addService(service) {
      this.doc_rows.push(service);
      this.showFreeSaleComponent = false;
      if(this.document_data.doc_type === 'kreditno izvestie') {
        this.relative_credit_maxValue = this.relative_credit_maxValue - service.total_sum.toFixed(2)
      }
    },
    editService(service) {
      if (this.document_data.doc_num) return;
      this.serviceForEdit = service;
      this.showFreeSaleComponent = true;
    },
    updateService(service) {
      const objIndex = this.doc_rows.findIndex((el) => el.uuid === service.uuid);
      let x = false
      let xamount = false

      if(this.document_data.doc_type === 'kreditno izvestie') {
      
        x = this.doc_rows[objIndex].total_sum == service.total_sum ? false : true
        let correctCreditMax = 0

        if(this.relative_credit_maxValue > 0 && this.doc_rows[objIndex].total_sum > service.total_sum) {
          correctCreditMax = this.relative_credit_maxValue - ((this.doc_rows[objIndex].total_sum - service.total_sum) * -1)
        }
        if(this.relative_credit_maxValue == 0 && this.doc_rows[objIndex].total_sum > service.total_sum) {
          correctCreditMax = (this.doc_rows[objIndex].total_sum - service.total_sum)
        }
        if(this.relative_credit_maxValue > 0 && this.doc_rows[objIndex].total_sum < service.total_sum) { 
          correctCreditMax = parseFloat(((this.relative_credit_maxValue + this.doc_rows[objIndex].total_sum) - service.total_sum).toFixed(2))
        }
        if(this.relative_credit_maxValue == 0 && this.doc_rows[objIndex].total_sum < service.total_sum) {
          correctCreditMax = (service.total_sum - this.doc_rows[objIndex].total_sum)
        }
        xamount = correctCreditMax

      }

      this.$set(this.doc_rows, objIndex, service);

      if(this.document_data.doc_type === 'kreditno izvestie') {
        if (x) {
          this.relative_credit_maxValue = xamount
        }
      }
      this.closeService();
    },
    deleteService(service) {
      let objIndex = this.doc_rows.findIndex((el) => el.uuid === service.uuid);
      if(this.document_data.doc_type === 'kreditno izvestie') {
        this.relative_credit_maxValue = this.relative_credit_maxValue + service.total_sum
      }
      this.doc_rows.splice(objIndex, 1);
      this.closeService();
    },
    beginTextEdit($event) {
      if (this.TempTextEdit) return;
      $event.target.classList.remove("truncate");
      this.TempTextEdit = true;
      this.tempTextVal = this.stripHtml($event.target.innerHTML).trim();
    },
    editTextRow($event, service, prop) {
      let text = this.stripHtml($event.target.innerHTML).trim();

      if ( 
        (prop === "object_name" && service.type === "month" && service.id_object !== 0)
        || (prop === "object_name" && service.type === "free" && service.id_object !== 0)
        || (prop === "object_name" && service.type === "single" && service.id_object !== 0)
      ) {
        this.doc_rows
          .filter(obj => obj.id_object === service.id_object)
          .map(el => el.object_name = text);
      } else if (service.type === "month" && prop === "service_name") {
        this.doc_rows
          .filter(obj => obj.id_object === service.id_object && obj.id_duty === service.id_duty && obj.type === service.type)
          .map(el => el.service_name = text);
      } else if ((service.type === "month" && prop === "view_type_detail") || (service.type === "free" && prop === "view_type_detail")) {
        const forSmartSot = service.for_smartsot;

        this.doc_rows
          .filter(obj => obj.id_object === service.id_object && obj.type === service.type && obj.month === service.month && obj.for_smartsot === forSmartSot)
          .map(el => el.view_type_detail = text);
      } else if (
        (prop === "view_type_by_services" && service.type === "month" && service.hasOwnProperty("services"))
        || (prop === "view_type_by_services" && service.type === "free" && service.hasOwnProperty("services"))
        ) {
        service.services.forEach(service => service.view_type_by_services = text);
      } else {
        Vue.set(service, prop, text);
      }
      $event.target.scrollLeft = 0;
      if (!this.document_data.view_type === "single") {
        $event.target.classList.add("truncate");
      }
      this.tempTextVal = "";
      this.TempTextEdit = false;
      $event.target.blur();
    },
    restoreTextIfNotConfirmed($event) {
      if (this.tempTextVal === "" && !this.TempTextEdit) return;
      $event.target.innerHTML = this.tempTextVal;
      this.TempTextEdit = false;
      this.tempTextVal = "";
      $event.target.classList.add("truncate");
      $event.target.blur();
    },
    stripHtml(html) {
      let doc = new DOMParser().parseFromString(html, "text/html");
      return doc.body.textContent || "";
    },
    openClient() {
      dialogClientInfo(this.client.id);
    },
    getClientObligations() {
      this.loading = true;
      const duty_date = moment(this.duty_date)
        .startOf("month")
        .format("YYYY-MM-DD");
      let endPoint = this.urlParams.id_object
        ? `api/api_sale_controller.php?action=duty&id_object=${this.urlParams.id_object}&duty_date=${duty_date}`
        : `api/api_sale_controller.php?action=duty&id_client=${this.client.id}&deliverer_name=${this.deliverer}&duty_date=${duty_date}`;
      axios
        .get(endPoint)
        .then(({ data } = response) => {
          if (!this.isValidResponse(data))
            throw new Error(
              "Грешка: Опитайте да заредите документа отново, ако грешката продължи да се повтаря се свържете с Администратор!"
            );

          this.document_data = {
            ...this.document_data,
            ...data.document_data,
          };

          this.document_data.client_name = data.client.name;
          this.document_data.client_address = data.client.invoice_address;
          this.document_data.client_ein = data.client.invoice_ein;
          this.document_data.client_ein_dds = data.client.invoice_ein_dds;
          this.document_data.client_mol = data.client.invoice_mol;
          this.document_data.client_recipient = data.client.invoice_recipient;
          this.document_data.view_type = data.client.invoice_layout;
          
          if(data.client.invoice_last_paid_caption) {
            this.$nextTick(() => this.document_data.single_view_name = `${data.client.invoice_single_view_name}${this.monthlySuffix}`)
          }
          
          this.document_data.doc_type = this.doc_type;
          this.prefferedInvoicePayment = data.client.invoice_payment;

          if (this.urlParams.id_object) {
            this.client = data.client;
          }

          if (this.current_view_type == '') {
              this.current_view_type = data.client.invoice_layout;
          } else {
            this.client.invoice_layout = this.current_view_type
            if(this.current_view_type != this.document_data.view_type) {
                this.document_data.view_type = this.current_view_type
            }
          }

          this.doc_rows = data.document_rows.map((obj) => ({
            ...obj,
            uuid: this.genUuid(),
          }));

          this.createObjectsDiscountsTemplates();

          if (data.alerts.length > 0) {
            alert(data.alerts[0]);
          }

          if (this.doc_rows.length) {
            this.$nextTick(() => {
              document.getElementById("grid").scrollTo({
                top: 0,
                behavior: "smooth",
              });
            });
          }

          this.setWindowTitle();
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    },
    storeClientObligations() {
      if (!this.urlParams.id || this.is_new_relative_doc) {
        if (!this.doc_rows.length) {
          alert("ГРЕШКА: не може да се издаде празен документ добавете ред!");
          return;
        }
        if (!this.servicesForPayment.length) {
          alert("ГРЕШКА: маркирайте поне един ред за плащане!");
          return;
        }
      }

      this.loading = true;
      this.document_data.doc_date = this.doc_date;
      this.document_data.doc_date_create = this.doc_date_create;

      if (!this.urlParams.id) {
        this.document_data.doc_type = this.doc_type;
      }

      this.document_data.id_client = this.client.id;
      this.document_data.client_recipient = this.client.invoice_recipient;
      this.document_data.note = this.client.note;

      if (this.urlParams.is_book) {
        if (parseInt(this.isBookDocNum) <= 0) {
          alert("ГРЕШКА: въвведете коректен номер на фактура!");
          this.loading = false;
          return;
        }
        if (!this.doc_rows.length) {
          alert("ГРЕШКА: не може да се издаде празна фактура добавете ред!");
          this.loading = false;
          return;
        }
        if (!this.servicesForPayment.length) {
          alert("ГРЕШКА: маркирайте поне един ред за плащане!");
          this.loading = false;
          return;
        }

        this.document_data.client_address = this.client.invoice_address;
        this.document_data.client_ein = this.client.invoice_ein;
        this.document_data.client_ein_dds = this.client.invoice_ein_dds;
        this.document_data.client_mol = this.client.invoice_mol;
        this.document_data.client_name = this.client.name;
        this.document_data.from_book = true;
        this.document_data = Object.assign({}, this.document_data, {
          doc_num: parseInt(this.isBookDocNum),
          is_book: 1,
        });
      }

      axios
        .post(this.actionEndPoint, {
          document_data: this.document_data,
          document_rows: this.servicesForPayment,
          confirm_request: this.confirm_request,
        })
        .then(({ data } = response) => {
          this.loading = false;
          if (data.request_confirm == 1) {
            if (confirm(data.alerts[0])) {
              this.confirm_request = 1;
              this.storeClientObligations();
              return;
            }
          } else {
            if (this.annulment) this.annulment = false;
            if (!this.urlParams.id || this.is_new_relative_doc) {
              window.name = data.document_data.id;
            }
            window.location.replace(
              `page.php?page=sale_new&id=${data.document_data.id}`
            );
          }
        })
        .catch((error) => {
          if (this.annulment) this.annulment = false;
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    },
    printSaleDoc() {
      vPopUp({
        url: `api/api_print_invoice.php?id=${this.urlParams.id}&v=${this.document_data.version}`,
        name: `pdf${this.urlParams.id}`,
        width: 860,
        height: 650,
        reload: true,
      });
    },
    prepareNewRelativeDoc() {
      this.relative_document = JSON.parse(JSON.stringify(this.document_data));
      this.client.invoice_layout = "extended";
      this.is_new_relative_doc = true;
      this.doc_rows = [];
      this.doc_rows_dds = [];
      this.orders = [];
      this.document_data.doc_num = "";
      this.document_data.doc_status = "final";
      this.document_data.doc_date = moment().format("YYYY-MM-DD");
      this.document_data.doc_date_create = moment().format("YYYY-MM-DD");
      this.doc_date = moment().format("YYYY-MM-DD");
      this.doc_date_create = moment().format("YYYY-MM-DD");
      this.document_data.total_sum = 0;
      this.document_data.orders_sum = 0;
      this.document_data.last_order_id = 0;
      this.document_data.last_order_time = "0000-00-00 00:00:00";
      this.document_data.id_bank_account = 0;
      this.document_data.id_bank_epayment = 0;
      this.document_data.epay_provider = 0;
      this.document_data.easypay_date = "0000-00-00 00:00:00";
      this.document_data.invoice_payment = 'bank'
      this.document_data.view_type = "extended";
      this.document_data.single_view_name = "услуга";
      this.document_data.is_book = 0;
      this.document_data.from_book = false,
      this.document_data.is_advice = 0;
      this.document_data.id_advice = 0;
      this.document_data.id_protocol = 0;
      this.document_data.is_user_print = 0;
      this.document_data.user_print_date = "0000-00-00 00:00:00";
      this.document_data.gen_pdf_date = "0000-00-00 00:00:00";
      this.document_data.note = '';
      this.document_data.exported = 0;
      this.document_data.created = "";
      this.document_data.created_time = "";
      this.document_data.created_user = 0;
      this.document_data.updated = "";
      this.document_data.updated_time = "";
      this.document_data.updated_user = 0;
    },
    prepareDebitSaleDoc() {
      this.doc_type = "debitno izvestie";
      this.document_data.doc_type = "debitno izvestie";
      this.setWindowTitle();
      window.name = `new_debit_doc${this.urlParams.id}`;
    },
    prepareCreditSaleDoc() {
      this.doc_type = "kreditno izvestie";
      this.document_data.doc_type = "kreditno izvestie";
      this.setWindowTitle();
      window.name = `new_credit_doc${this.urlParams.id}`;
    },
    addDebitSaleDoc() {
      if (confirm("Създай дебитно известие ?")) {
        this.prepareNewRelativeDoc();
        this.prepareDebitSaleDoc();
      }
    },
    addCreditSaleDoc() {
      if (confirm("Създай кредитно известие ?")) {
        this.relative_credit_maxValue = parseFloat(this.baseSum.toFixed(2));
        this.prepareNewRelativeDoc();
        this.prepareCreditSaleDoc();
      }
    },
    getDocumentByID() {
      this.loading = true;
      axios
        .get(`api/api_sale_controller.php?action=duty&id=${this.urlParams.id}`)
        .then(({ data } = response.data) => {
          if (!this.isValidResponse(data))
            throw new Error(
              "Грешка: Опитайте да заредите документа отново, ако грешката продължи да се повтаря се свържете с Администратор!"
            );

          this.document_data = {
            ...this.document_data,
            ...data.document_data,
          };
          this.deliverer = data.document_data.deliverer_name;
          this.doc_date = data.document_data.doc_date;
          this.doc_date_create = data.document_data.doc_date_create;
          this.doc_type = data.document_data.doc_type;
          this.search = data.document_data.client_name;
          this.client.id = data.document_data.id_client;
          this.client_name = data.document_data.client_name;
          this.client.invoice_address = data.document_data.client_address;
          this.client.invoice_ein = data.document_data.client_ein;
          this.client.invoice_ein_dds = data.document_data.client_ein_dds;
          this.client.invoice_mol = data.document_data.client_mol;
          this.client.invoice_recipient = data.document_data.client_recipient;
          this.client.note = data.document_data.note;
          this.client.invoice_layout = data.document_data.view_type;

          this.origin_document = data.origin_document;

          this.doc_rows = data.document_rows
            .filter((row) => row.is_dds !== 1)
            .map((obj) => ({
              ...obj,
              uuid: this.genUuid(),
              for_payment: true,
            }));
          this.doc_rows_dds = data.document_rows.filter(
            (row) => row.is_dds === 1
          );
          this.orders = data.orders;

          if (data.alerts.length > 0) {
            alert(data.alerts[0]);
          }
          if (!this.loaded) {
            this.loaded = true;
          }
          this.setWindowTitle();
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    },
    setClient($event) {
      if (!$event.id) return;
      this.client = $event;
      this.document_data.single_view_name = $event.invoice_single_view_name;
      this.prefferedInvoicePayment = $event.invoice_payment;
    },
    updateClient($event) {
      if (
        confirm(
          `Потвърдете избор на клиент ${$event.name} с клиентски номер: ${$event.id}`
        )
      ) {
        this.loading = true;
        axios
          .post("api/api_sale_controller.php?action=update_client", {
            doc_id: this.document_data.id,
            id_client: $event.id,
            client_ein: $event.invoice_ein,
            client_ein_dds: $event.invoice_ein_dds,
            client_mol: $event.invoice_mol,
            client_name: $event.name,
            client_recipient: $event.invoice_recipient,
            client_address: $event.invoice_address,
          })
          .then((response) => {
            this.getDocumentByID();
            this.loading = false;
          })
          .catch((error) => {
            this.loading = false;
            error?.response?.data?.error
              ? alert(`Грешка: ${error.response.data.error} !`)
              : alert(error.message);
          });
        this.showChangeClientDialog = false;
        this.searchNewClient = "";
      } else {
        this.showChangeClientDialog = false;
        this.searchNewClient = "";
      }
    },
    resetClient() {
      this.client = JSON.parse(JSON.stringify(this.default_client));
      this.document_data.client_address = ""
      this.document_data.client_ein = ""
      this.document_data.client_ein_dds = ""
      this.document_data.client_mol = ""
      this.document_data.client_name = ""
      this.document_data.client_recipient =""
      this.document_data.single_view_name = 'услуга'
      this.document_data.view_type = "extended"
      this.doc_rows = [];
    },
    allowChangeClient() {
      if(this.urlParams.id_object) return;
      if (!this.document_data.sale_doc_edit) return;
      if (
        this.document_data.doc_status === "canceled" ||
        !this.document_data.sale_doc_grant
      )
        return;
      this.showChangeClientDialog = true;
    },
    /* getTotalVatSumbByVat(vat) {
      return this.servicesForPayment
        .filter(service => service.vat === vat)
        .map(service => ((service.single_price * service.quantity) / 100) * vat)
        .reduce((sum, vatsum) => sum + vatsum, 0);
    }, */
    getForPaymentStateFromArray(arr) {
      const serviceState = [
        ...new Set(arr.map((service) => service.for_payment)),
      ];
      const state = {
        checked: serviceState.includes(true) ? true : false,
        indeterminate: serviceState.length > 1 ? true : false,
      };
      return state;
    },
    toggleAllServices($event) {
      this.doc_rows.map(
        (service) => (service.for_payment = $event.target.checked)
      );
    },
    toggleObjectServices(id, state, type = 0) {
      let objectServices = this.objectsTreeView.find(
        (obj) => obj.id_object === id
      );
      if (objectServices.monthly) {
        let monthCount = objectServices.monthly.months.length;
        state
          ? monthCount === 1
            ? this.toggleMonthlyServicesForObject(id, state, objectServices.monthly.months[0].month)
            : this.toggleMonthlyServicesForObject(id, state, objectServices.monthly.months[monthCount - 1].month)
          : this.toggleMonthlyServicesForObject(id, state, objectServices.monthly.months[0].month);
      }
      if (objectServices.singles && !type) {
        objectServices.singles.services.forEach(
          (single) => (single.for_payment = state)
        );
      }
    },
    toggleMonthlyServicesForObject(id, state, month) {
      let current_obj = this.objectsTreeView.find(
        (obj) => obj.id_object === id
      );
      let discounts = current_obj.discounts;
      let monthlys = current_obj.monthly.months;

      monthlys
        .find((monthly) => monthly.month === month)
        .services.forEach((service) => (service.for_payment = state));

      let next = monthlys.filter((monthly) => monthly.month > month);

      if (next.length) {
        next.forEach((monthly) =>
          monthly.services.forEach((service) => (service.for_payment = false))
        );
      }

      let prev = monthlys.filter(
        (service) =>
          service.month < month && service.for_payment.checked === false
      );

      if (prev.length && state === true) {
        prev.forEach((monthly) =>
          monthly.services.forEach((service) => (service.for_payment = true))
        );
      }

      let obj = this.objectsTreeView.find((obj) => obj.id_object === id);
      let objMonthsForPayment = obj.monthly.months.filter(
        (month) => month.for_payment.checked === true
      );

      let objMonthsForPaymentCount = objMonthsForPayment
        .map((el) => el.month)
        .filter((el) => el >= moment().startOf("month").format("YYYY-MM-DD"))
        .length;

      if (discounts) {
        let discountMonthsCount = discounts.services[0].concession_month_count;

        if (discountMonthsCount > objMonthsForPaymentCount) {
          this.deleteObjectDiscounts(id);
        }

        if (discountMonthsCount < objMonthsForPaymentCount) {
          this.checkForNewDiscount(id, objMonthsForPaymentCount);
          return;
        }
      }
      this.checkForNewDiscount(id, objMonthsForPaymentCount);

      if(this.urlParams.id) return
      if(this.request_monthlySuffix === this.monthlySuffix) return
    },
    toggleObjectDiscount(id, state) {
      this.doc_rows
        .filter(
          (service) => service.id_object === id && service.type === "free"
        )
        .map((service) => (service.for_payment = state));
    },
    addObjectDiscounts(discounts) {
      discounts.map((service) => {
        service.single_price = this.getDiscountServiceSum(
          discounts[0].id_object,
          service.reference_service_id,
          service.percent
        );
        service.total_sum = service.single_price;
        service.total_sum_with_dds =
          service.single_price * (service.vat / 100 + 1);
        service.uuid = this.genUuid();
      });
      discounts.forEach((service) => this.doc_rows.push(service));
      this.reorderDocRowsByMonthlyDiscountsAndSingles();
    },
    deleteObjectDiscounts(id_object) {
      this.doc_rows
        .filter(
          (discounts) =>
            discounts.id_object === id_object && discounts.type === "free"
        )
        .forEach((service) =>
          Vue.delete(this.doc_rows, this.doc_rows.indexOf(service))
        );
    },
    checkForNewDiscount(id, objMonthsForPaymentCount) {
      let availableDiscounts = Object.values(this.concessions).filter(
        (concession) => concession.months_count <= objMonthsForPaymentCount
      );

      if (availableDiscounts.length) {
        availableDiscounts.sort((a, b) => a.months_count - b.months_count);
        let newDiscount = this.objectDiscountTemplates.filter((template) =>
          template.some(
            (obj) =>
              obj.id_object === id &&
              obj.concession_month_count ===
                availableDiscounts[availableDiscounts.length - 1].months_count
          )
        )[0];

        let clonedDiscount = JSON.parse(JSON.stringify(newDiscount));
        this.objectsTreeView.find((obj) => obj.id_object === id).discounts
          ? (this.deleteObjectDiscounts(id),
            this.addObjectDiscounts(clonedDiscount))
          : this.addObjectDiscounts(clonedDiscount);
      }
    },
    getDiscountServiceSum(id_object, originalServiceId, discountPercent) {
      let currentStartOfMonth = this.document_data.doc_date_create.slice(0, -2);
      currentStartOfMonth += "01";
      let services = this.doc_rows.filter(
        (service) =>
          service.id_object === id_object &&
          service.type === "month" &&
          service.month >= currentStartOfMonth &&
          service.for_payment === true &&
          service.id_service === originalServiceId
      );
      let discountSum =
        (this.getTotalSumFromArr(services) * -1 * discountPercent) / 100;
      // na pavkata formulata jhuehueheuhehu (0.83333333334 * ((20 / 100) +1)).toFixed(2)
      /* if (discountSum > 0) {
        return -Math.abs(discountSum);
      } */
      /* console.log(
        `gen discount for id_object => ${id_object}, for serivce_id => ${originalServiceId}, found elements => ${services.length}, discount sum => ${discountSum}`
      ); */
      return discountSum;
    },
    reorderDocRowsByMonthlyDiscountsAndSingles() {
      let reordered = [];

      this.idObjects.forEach((id) => {
        const objectMonthly = [];
        const objectDiscounts = [];
        const objectSingles = [];
        const objectsServices = this.doc_rows.filter(
          (obj) => obj.id_object === id
        );

        if (objectsServices.length) {
          objectsServices.forEach((service) => {
            if (service.type === "month") {
              objectMonthly.push(service);
            }
            if (service.type === "free") {
              objectDiscounts.push(service);
            }
            if (service.type === "single") {
              objectSingles.push(service);
            }
          });

          if (objectMonthly.length) {
            reordered = reordered.concat(objectMonthly);
          }
          if (objectDiscounts.length) {
            reordered = reordered.concat(objectDiscounts);
          }
          if (objectSingles.length) {
            reordered = reordered.concat(objectSingles);
          }
        }
      });
      if (!reordered.length) return;
      this.doc_rows = reordered;
    },
    createObjectTree(id_object) {
      let objectTaxes = this.doc_rows.filter(
        (object) => object.id_object === id_object
      );
      let monthly = this.createObjectMonthlyTaxes(
        objectTaxes.filter((service) => service.type === "month")
      );
      let discounts = this.createObjectDiscounts(
        objectTaxes.filter((service) => service.type === "free")
      );
      let singles = this.createObjectSingleTaxes(
        objectTaxes.filter((service) => service.type === "single")
      );

      let objectTree = {
        name: objectTaxes[0].object_name,
        id_object: id_object,
        for_payment: this.getForPaymentStateFromArray(objectTaxes),
        monthly: monthly,
        discounts: discounts,
        singles: singles,
        quantity: 1,
        measure: "бр.",
        //for_smartsot: monthly ? monthly.months[0].services.some(service => service.for_smartsot == 1) : undefined,
        totalForPayment:
          (monthly?.totalForPayment ?? 0) +
          (discounts?.totalForPayment ?? 0) +
          (singles?.totalForPayment ?? 0),
        totalSum:
          (monthly?.totalForPayment ?? 0) +
            (discounts?.totalForPayment ?? 0) +
            (singles?.totalForPayment ?? 0) >
          0
            ? (monthly?.totalForPayment ?? 0) +
              (discounts?.totalForPayment ?? 0) +
              (singles?.totalForPayment ?? 0)
            : this.getTotalSumFromArr(objectTaxes),
      };
      return objectTree;
    },
    createObjectMonthlyTaxes(monthlys) {
      if (monthlys.length > 0) {
        const idObject = monthlys[0].id_object;
        let forSmartSot = monthlys[0].for_smartsot;
        let forPaymentState = this.getForPaymentStateFromArray(monthlys);
        let view_type_by_object_services =
          monthlys[0].view_type_by_object_services;
        let months = [];

        Object.entries(this.groupBy(monthlys, "month")).forEach(
          ([month, services]) =>
            months.push({
              name: services[0].view_type_detail,
              month: month,
              services: services,
              for_payment: this.getForPaymentStateFromArray(services),
              totalSum: this.getTotalSumFromArr(services),
              totalForPayment: this.getTotalSumFromArr(
                services.filter((service) => service.for_payment === true)
              ),
            })
        );

        let monthlyTree = {
          name: view_type_by_object_services,
          for_payment: this.getForPaymentStateFromArray(monthlys),
          totalSum: this.getTotalSumFromArr(monthlys),
          totalForPayment: this.getTotalSumFromArr(
            monthlys.filter((month) => month.for_payment === true)
          ),
          for_smartsot: forSmartSot,
          months: months,
          showTree: false,
        };
        return monthlyTree;
      }
      return null;
    },
    createObjectDiscounts(discounts) {
      // за доработка разделно групиране за отстъпките по месеци и услуги ?
      if (discounts.length > 0) {
        let discountName = Object.values(this.concessions).find(
          (discount) => discount.id_service === discounts[0].id_service
        ).name;

        let discountTree = {
          name: discounts[0].for_smartsot
            ? `${discountName} [ Смарт СОТ ]`
            : `${discountName}`,
          month: discounts[0].month,
          for_payment: this.getForPaymentStateFromArray(discounts),
          totalSum: this.getTotalSumFromArr(discounts),
          totalForPayment: this.getTotalSumFromArr(
            discounts.filter((discount) => discount.for_payment === true)
          ),
          services: discounts,
        };
        return discountTree;
      }
      return null;
    },
    createObjectSingleTaxes(singles) {
      if (singles.length > 0) {
        let singlesTree = {
          name: "Еднократни задължения",
          totalSum: this.getTotalSumFromArr(singles),
          totalForPayment: this.getTotalSumFromArr(
            singles.filter((single) => single.for_payment === true)
          ),
          services: singles,
        };
        return singlesTree;
      }
      return null;
    },
    createObjectsDiscountsTemplates() {
      let discountTemplates = [];
      if (this.idObjects) {
        this.idObjects.forEach((id) => {
          let objdiscounts = this.doc_rows.filter(
            (service) => service.id_object === id && service.type === "free"
          );

          if (objdiscounts.length) {
            Object.values(this.concessions).forEach((concession) => {
              let discountsClone = JSON.parse(JSON.stringify(objdiscounts));

              discountsClone.forEach((service) => {
                service.service_name =
                  service.single_price > 0
                    ? `Корекция: [ ${service.reference_service_name} ]`
                    : `${concession.name} [ ${service.reference_service_name} ]`;
                service.view_type_detail = service.for_smartsot
                  ? `${concession.name} [ Смарт СОТ ]`
                  : `${concession.name} [ ${
                      this.services.find(
                        (el) => el.id_service === service.reference_service_id
                      ).name
                    } ]`;
                service.id_service = concession.id_service;
                service.concession_month_count = concession.months_count;
                service.percent = concession.percent;
                service.single_price = 1;
                service.total_sum = 1;
              });
              discountTemplates.push(discountsClone);
            });
          }
        });
      }
      this.objectDiscountTemplates = discountTemplates;
    },
    genByMonthServiceArray(month) {
      const byMonth = [];
      const smartServices = month.services.filter(
        (service) => service.for_smartsot
      );
      const regularServices = month.services.filter(
        (service) => !service.for_smartsot
      );

      if (smartServices.length) {
        byMonth.push({
          name: smartServices[0].view_type_detail,
          month: month.month,
          services: smartServices,
          for_payment: this.getForPaymentStateFromArray(smartServices),
          totalSum: this.getTotalSumFromArr(smartServices),
          totalForPayment: this.getTotalSumFromArr(
            smartServices.filter((service) => service.for_payment)
          ),
        });
      }

      if (regularServices.length) {
        const servicesGroups = this.groupBy(regularServices, "id_service");

        Object.values(servicesGroups).forEach((group) => {
          group.forEach((service) => {
            byMonth.push({
              name: service.view_type_detail,
              month: service.month,
              services: group,
              for_payment: this.getForPaymentStateFromArray(group),
              totalSum: this.getTotalSumFromArr(group),
              totalForPayment: this.getTotalSumFromArr(
                group.filter((service) => service.for_payment)
              ),
            });
          });
        });
      }
      return byMonth.length ? byMonth : null;
    },
    genDiscountServiceArray(discounts) {
      const discountsByServiceGroups = [];
      const smartDiscounts = discounts.filter(
        (service) => service.for_smartsot
      );
      const regularDiscounts = discounts.filter(
        (service) => !service.for_smartsot
      );

      if (smartDiscounts.length) {
        discountsByServiceGroups.push({
          name: smartDiscounts[0].view_type_detail,
          month: smartDiscounts[0].month,
          services: smartDiscounts,
          for_payment: this.getForPaymentStateFromArray(smartDiscounts),
          totalSum: this.getTotalSumFromArr(smartDiscounts),
          totalForPayment: this.getTotalSumFromArr(
            smartDiscounts.filter((service) => service.for_payment)
          ),
        });
      }

      if (regularDiscounts.length) {
        const discountGroups = this.groupBy(regularDiscounts, "id_service");
        regularDiscounts.forEach((discount) => {
          discountsByServiceGroups.push({
            name: discount.view_type_detail,
            month: discount.month,
            services: [discount],
            for_payment: this.getForPaymentStateFromArray([discount]),
            totalSum: discount.total_sum,
            totalForPayment: discount.for_payment,
          });
        });
      }
      return discountsByServiceGroups.length ? discountsByServiceGroups : null;
    },
  },
  watch: {
    monthlySuffix(newValue,oldValue) {
        if(this.urlParams.id || !newValue) return;
        if(!oldValue && !this.request_monthlySuffix) {
          this.request_monthlySuffix = newValue
        }
        this.adjustMonthlySuffix()
    },
    clientHasChanged: {
      handler(newValue) {
        if (!newValue || this.urlParams.id_object || this.urlParams.id || this.urlParams.is_book) return;
        this.getClientObligations();
      },
      deep: true,
    },
  },
  computed: {
    monthlySuffix() {
      if(!this.urlParams.id && this.servicesForPayment.length && this.client.invoice_last_paid_caption) {
        const months = [ ...(new Set(this.servicesForPayment
          .filter(service => service.type === 'month')
          .map(({ month }) => month)))
        ].sort()
        
        return months.length
          ?  (months.length > 1
              ? `: до м. ${this.toMMYYY(months[months.length -1])} г. вкл.`
              : `: за м. ${this.toMMYYY(months[0])} г.`
              )
          : ''
      }
      return ''
    },
    idCashDefault() {
      return !this.loaded 
        ? null
        : this.bank_orders.find(el => el.id === this.document_data.id_cash_default) ?? null
    },
    showToolbar() {
      return this.client.id || this.doc_type === "oprostena" ? true : false;
    },
    showLastPaid() {
      return this.urlParams.id || !this.client.id || this.urlParams.is_book
        ? false
        : true;
    },
    showVat() {
      if(!this.urlParams.id) {
        return this.doc_type !== "oprostena" ? true : false
      }
      return this.doc_type !== "oprostena" || this.document_data.doc_type !== "oprostena"
        ? true
        : false;
    },
    allowNewRows() {
      return (!this.urlParams.id && this.client.id) || (!this.urlParams.id && this.doc_type === "oprostena") || this.is_new_relative_doc
        ? true
        : false;
    },
    allowRelativeDocuments() {
      if(!this.loaded) return false;
      if(this.document_data.doc_status === 'canceled') return false;
      if (this.document_data.doc_type === "oprostena" || this.document_data.doc_type === "kvitanciq" || this.document_data.doc_type === "debitno izvestie" || this.document_data.doc_type === "kreditno izvestie") return false;
      return true;
    },
    allowChangeViewType() {
      if (!this.loaded) return false;
      return this.document_data.doc_type === "debitno izvestie" || this.document_data.doc_type === "kreditno izvestie"
        ? false
        : true;
    },
    docTitle() {
      return this.urlParams.id && this.docType
        ? this.docType.abbr
        : this.urlParams.is_book
          ? "Фактура от кочан приход"
          : "Продажба приход";
    },
    docType() {
      if (this.loaded) {
        const suffix = this.document_data.doc_num ?? "";
        if (this.document_data.doc_type === "kvitanciq") {
          return {
            name: "Проформа фактура",
            abbr: `Проформа ${suffix}`,
          };
        }
        if (this.document_data.doc_type === "kreditno izvestie") {
          return {
            name: "Кредитно известие",
            abbr: `К.И. ${suffix}`,
          };
        }
        if (this.document_data.doc_type === "debitno izvestie") {
          return {
            name: "Дебитно известие",
            abbr: `Д.И. ${suffix}`,
          };
        }
        if (this.document_data.doc_type === "faktura") {
          return {
            name: "Фактура",
            abbr: !this.document_data.is_book
              ? `Ф-ра ${suffix}`
              : `Ф-ра от кочан ${suffix}`,
          };
        }
        if (this.document_data.doc_type === "oprostena") {
          return {
            name: "Квитанция",
            abbr: `Кв. ${suffix}`,
          };
        }
      }
    },
    clientPrefferedInvoicePayment() {
      if (this.prefferedInvoicePayment) {
        let invoicePayment = "клиента предпочита ";
        if (this.prefferedInvoicePayment === "cash") {
          invoicePayment += "фактура в брой";
        }
        if (this.prefferedInvoicePayment === "bank") {
          invoicePayment += "фактура по банка";
        }
        if (this.prefferedInvoicePayment === "cashless") {
          invoicePayment += "фактура в брой";
        }
        if (this.prefferedInvoicePayment === "receipt") {
          invoicePayment += "квитанция";
        }
        return invoicePayment;
      } else {
        return "";
      }
    },
    idObjects() {
      if (this.loaded && this.doc_rows.length > 0) {
        return [...new Set(this.doc_rows.map(({ id_object }) => id_object))];
      } else return null;
    },
    objectsTreeView() {
      if (this.loaded && this.doc_rows.length) {
        let tree = [];
        for (let idObject of this.idObjects) {
          if (idObject !== 0) {
            tree.push(this.createObjectTree(idObject));
          } else {
            this.doc_rows
              .filter(
                (service) => service.id_object === 0 && service.type === "free"
              )
              .forEach((service) => tree.push(service));
          }
        }
        return tree;
      } else return null;
    },
    byMonths() {
      if (this.objectsTreeView) {
        let objectsByMonths = [];

        this.objectsTreeView.forEach((obj) => {
          if (obj.monthly) {
            obj.monthly.months.forEach((month) => {
              objectsByMonths = [
                ...objectsByMonths,
                ...this.genByMonthServiceArray(month),
              ];
            });
          }
          if (obj.discounts) {
            objectsByMonths = [
              ...objectsByMonths,
              ...this.genDiscountServiceArray(obj.discounts.services),
            ];
          }
          if (obj.singles && obj.singles.services.length) {
            obj.singles.services.forEach((single) =>
              objectsByMonths.push(single)
            );
          }
          if (obj.id_object === 0 && obj.type === "free") {
            objectsByMonths.push(obj);
          }
        });
        return objectsByMonths;
      } else {
        return null;
      }
    },
    byServices() {
      if (this.doc_rows.length) {
        let byServices = [];
        let smartMonthlyServices = [];
        let regularMonthlyServices = [];
        let discounts = [];
        let singles = [];
        let freeSales = [];

        for (let service of this.doc_rows) {
          if (
            service.for_smartsot &&
            service.id_object &&
            service.type === "month"
          ) {
            smartMonthlyServices.push(service);
          }
          if (
            !service.for_smartsot &&
            service.id_object &&
            service.type === "month"
          ) {
            regularMonthlyServices.push(service);
          }
          if (service.id_object && service.type === "free") {
            discounts.push(service);
          }
          if (service.id_object && service.type === "single") {
            singles.push(service);
          }
          if (!service.id_object && service.type === "free") {
            freeSales.push(service);
          }
        }

        if (smartMonthlyServices.length) {
          byServices.push({
            for_payment: this.getForPaymentStateFromArray(smartMonthlyServices),
            measure: "бр.",
            name: smartMonthlyServices[0].view_type_by_services,
            quantity: 1,
            services: smartMonthlyServices,
            single_price: this.getTotalSumFromArr(smartMonthlyServices),
            totalSum: this.getTotalSumFromArr(smartMonthlyServices),
            type: "month",
          });
        }

        if (regularMonthlyServices.length) {
          const regularMonthlyGroups = this.groupBy(
            regularMonthlyServices,
            "id_service"
          );

          Object.values(regularMonthlyGroups).forEach((group) => {
            byServices.push({
              for_payment: this.getForPaymentStateFromArray(group),
              name: group[0].view_type_by_services,
              services: group,
              measure: "бр.",
              quantity: 1,
              single_price: this.getTotalSumFromArr(group),
              totalSum: this.getTotalSumFromArr(group),
              type: "month",
            });
          });
        }

        if (discounts.length) {
          byServices.push({
            for_payment: this.getForPaymentStateFromArray(discounts),
            measure: "бр.",
            name: discounts[0].view_type_by_services,
            quantity: 1,
            services: discounts,
            single_price: this.getTotalSumFromArr(discounts),
            totalSum: this.getTotalSumFromArr(discounts),
            type: "free",
          });
        }

        if (singles.length) {
          byServices = [...byServices, ...singles];
        }

        if (freeSales.length) {
          byServices = [...byServices, ...freeSales];
        }

        return byServices;
      } else return null;
    },
    endPoint() {
      const duty_date = moment(this.duty_date)
        .startOf("month")
        .format("YYYY-MM-DD");
      return this.urlParams.id_object
        ? `/api/api_sale_controller.php?action=duty&id_object=${this.urlParams.id_object}`
        : this.client.id
        ? `/api/api_sale_controller.php?action=duty&id_client=${this.client.id}&deliverer_name=${this.deliverer}&duty_date=${duty_date}`
        : null;
    },
    actionEndPoint() {
      if (!this.urlParams.id && !this.is_new_relative_doc && !this.annulment) {
        return "api/api_sale_controller.php?action=store";
      }
      if (this.urlParams.id && this.is_new_relative_doc && !this.annulment) {
        return "api/api_sale_controller.php?action=make_advice";
      }
      if (this.urlParams.id && !this.is_new_relative_doc && !this.annulment) {
        return "api/api_sale_controller.php?action=update";
      }
      if (this.annulment) {
        return "api/api_sale_controller.php?action=annulment";
      }
    },
    clientHasChanged() {
      return this.client.id ? true : false;
    },
    selectedDeliverer() {
      return this.deliverer !== "изберете"
        ? this.deliverers
            .map((deliverer) => deliverer.name)
            .indexOf(this.deliverer)
        : this.deliverer;
    },
    selectedFirms() {
      if (!this.loaded || this.deliverer === "изберете") return null;
      return this.firms.filter(
        (firm) =>
          firm.idn ===
          this.deliverers.filter((el) => el.name === this.deliverer)[0].idn
      );
    },
    serviceForEditType() {
      let freeNew = {
        type: "free",
        edit: false,
      };
      let freeEdit = {
        type: "free",
        edit: true,
      };
      let month = {
        type: "month",
        edit: true,
      };

      //ако е нова свободна продажба
      if (!Object.keys(this.serviceForEdit).length > 0) return freeNew;

      //ако е съществуваща свободна продажба
      if (
        Object.keys(this.serviceForEdit).length > 0 &&
        this.serviceForEdit.type === "free"
      )
        return freeEdit;

      if (
        Object.keys(this.serviceForEdit).length > 0 &&
        this.serviceForEdit.type === "month"
      )
        return month;

      return false;
    },
    servicesForPayment() {
      return this.doc_rows.filter((row) => row.for_payment === true);
    },
    allRowsTotal(){
      if(!this.urlParams.id || this.is_new_relative_doc) {
        return   this.doc_rows.reduce((total,service) => total + service.total_sum, 0)
      }
      return this.document_data.total_sum - this.vatSum;
    },
    baseSum() {
      if(!this.urlParams.id || this.is_new_relative_doc) { 
        return (this.servicesForPayment.reduce((total, service) => total + service.total_sum_with_dds, 0) - this.vatSum)
      }
      return parseFloat(this.document_data.total_sum - this.vatSum);
    },
    vatSum() {
      if(!this.urlParams.id || this.is_new_relative_doc) { 
        return this.vatTypesTotal.reduce((acc, vat) => acc + vat.sum, 0);
      }
      return this.doc_rows_dds.reduce((acc, vat) => acc + vat.total_sum, 0);
    },
    vatTypesTotal() {
      
      const vatTypes = [
        ...new Set(this.servicesForPayment.map((service) => service.vat)),
      ];
      const vatTotals = [];

      for (const vat of vatTypes) {
        let servicesTotalsWithVat = this.servicesForPayment
            .filter((service) => service.vat === vat)
            .reduce((acc, service) => acc + service.total_sum_with_dds, 0)
        
        const vatCoefficient = (vat / 100 + 1);
        const sum = servicesTotalsWithVat - (servicesTotalsWithVat / vatCoefficient)
        
        vatTotals.push({
          vat: vat,
          sum: sum
        });
      }
      return vatTotals;
    },
    vatTitle() {
      let title = "";
      for (const vat of this.vatTypesTotal) {
        title += `[ ддс ${vat.vat}% : ${vat.sum} ]`;
      }
      return title;
    },
    totalSum() {
      if(!this.urlParams.id || this.is_new_relative_doc) {
        return this.doc_type !== "oprostena" || this.document_data.doc_type !== "oprostena"
          ? parseFloat(this.baseSum) + parseFloat(this.vatSum)
          : parseFloat(this.baseSum)
      } 
      
      return this.document_data.total_sum
      
    },
    relativeId() {
      return this.document_data.doc_type === "debitno izvestie" ||
        this.document_data.doc_type === "kreditno izvestie"
        ? this.document_data.id_advice
        : this.urlParams.id;
    },
  },
};
</script>
<style lang="scss">
.header-collapse {
  min-height: 32px;
}
.header-full {
  min-height: 152px;
}
.justify-self-center {
  justify-self: center;
}
::selection {
  background-color: #338fff;
  color: #fff;
}
.header {
  display: grid;
  grid-template-columns: 2fr 1fr 2fr;
  grid-template-rows: min-content;
  grid-column-gap: 12px;
  //min-height: 152px;
  overflow-x: auto;
  & .client,
  .deliverer {
    display: grid;
    grid-template-columns: 62px 113px 42px 113px;
    grid-template-rows: min-content;
    grid-gap: 8px;
    align-items: center;
  }
  & .client {
    justify-self: start;
  }
  & .doc {
    display: grid;
    grid-template-columns: 54px minmax(126px, auto);
    grid-template-rows: min-content;
    grid-gap: 8px;
    justify-self: center;
    align-items: center;
  }
  & .deliverer {
    justify-self: end;
  }
}
.footer {
  display: grid;
  grid-template-columns: 2fr 1fr 2fr;
  grid-template-rows: min-content;
  grid-column-gap: 8px;
  min-height: min-content;
  min-height: 112px;
  overflow-x: auto;
  align-items: start;
  & .f-left,
  .f-middle,
  .f-right {
    display: grid;
    grid-gap: 8px;
    align-items: center;
  }
  & .f-left {
    grid-template-columns: 62px 284px;
    grid-template-rows: min-content;
  }
  & .f-middle {
    grid-template-columns: 54px minmax(126px, auto);
    grid-template-rows: min-content;
    justify-self: center;
  }
  & .f-right {
    grid-template-columns: 62px 113px 42px 113px;
    justify-self: end;
  }
}

.grid-data {
  display: grid;
  grid-template-columns: 1fr;
  grid-template-rows: min-content;
  min-height: 81px;
  border-radius: 2px;

  border: 1px solid #ebf1fa;
  overflow: auto;
  & .grid-row {
    display: grid;
    grid-column-gap: 8px;
    //padding: 6px 8px;
    padding: 5px 8px;
    width: 100%;
    grid-template-columns:
      minmax(24px, 24px)
      minmax(35px, 35px)
      minmax(150px, 100%)
      minmax(150px, 100%)
      minmax(68px, 68px)
      minmax(68px, 68px)
      minmax(68px, 68px);
    grid-auto-rows: min-content;
    align-items: center;
    border: 1px solid transparent;
    border-bottom-color: #ebf1fa;
    background-color: #fff;
    &:focus-within {
      background: #f5f5f5;
    }
    &:first-child {
      height: auto;
    }
    &:not(:first-child):hover {
      background: #f9fafb;
      border-color: #858585;
      border-radius: 0.125rem;
      & .obj-link {
        visibility: visible;
      }
    }
    &:last-child {
      border-bottom: 1px solid transparent;
    }
    & .check {
      justify-self: center;
    }
    & .rownum {
      justify-self: center;
    }
    & .quantity,
    .single,
    .total {
      justify-self: end;
      & > div {
        text-align: right;
      }
    }
  }
  & .headers {
    position: sticky;
    top: 0;
    font-size: 10px;
    align-items: center;
    padding: 8px;
    z-index: 20;
    color: #fff;
    background: #8da2fb;
    box-shadow: rgba(0, 0, 0, 0.255) 0px 1.6px 3.6px,
      rgba(0, 0, 0, 0.216) 0px 0px 2.9px;
    user-select: none;
    border: unset;
  }
  & .row-num {
    width: 35px;
    height: 35px;
    align-self: center;
    display: flex;
    align-items: center;
    justify-content: center;
    justify-self: center;
    background-color: #fff;
    border: 1px solid #c5cbd4;
    color: #8da2fb;
    font-size: 10px;
    border-radius: 999px;
    user-select: none;
  }
}

.custom-label {
  font-size: 12px;
  color: #5c6bc0;
  user-select: none;
  //font-weight: 500;
}
.custom-input {
  font-size: 0.75rem;
  padding: 6px 8px;
  appearance: none;
  //color: #546e7a;
  //color: #6d758f;
  color: #37474f;

  width: 100%;
  background-color: inherit;
  border: 1px solid #a2aab4;
  border-radius: 0.125rem;
  min-height: 2rem;
  transition: all 0.2s ease-in-out;
  box-shadow: 0 0 0 1px #a2aab4 inset;

  &:hover {
    border-color: #4b5563;
    box-shadow: 0 0 0 1px #4b5563 inset;
  }
  &:focus {
    outline: none;
    border-color: #8da2fb;
    box-shadow: 0 0 0 1px #8da2fb inset;
  }
  &:focus-within {
    outline: none;
    border-color: #8da2fb;
    box-shadow: 0 0 0 1px #8da2fb inset;
  }
}

//
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.last-paid {
  width: 62px;
}
.last-paid-picker {
  width: 113px;
}

.primary-info {
  font-size: 13px;
  //color: #546e7a;
  //color: #6d758f;
  color: #37474f;
  line-height: 1.25rem;
}
.secondary-info {
  //color: #78909c;
  //color: #A2AAB4;
  //color: #8b95a2;
  color: #5c6bc0;
  font-size: 11px;
  text-transform: lowercase;
}

.soft-shadow {
  /* box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12); */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.row-checkbox {
  z-index: 0;
  position: relative;
}

/* Input */
.row-checkbox > input {
  appearance: none;
  z-index: -1;
  position: absolute;
  box-shadow: none;
  outline: none;
  opacity: 0;
  pointer-events: none;
}

/* Span */
.row-checkbox > span {
  display: flex;
  width: 24px;
  cursor: pointer;
  align-items: center;
  justify-content: center;
}

/* Box */
.row-checkbox > span::before {
  content: "";
  display: inline-block;
  box-sizing: border-box;
  margin: 3px 1px;
  border: solid 2px;
  border-color: #c5cbd4;
  border-radius: 2px;
  width: 18px;
  height: 18px;
  vertical-align: top;
  transition: border-color 0.2s, background-color 0.2s;
}

/* Checkmark */
.row-checkbox > span::after {
  content: "";
  display: block;
  position: absolute;
  top: 4px;
  left: 3px;
  width: 12px;
  height: 5px;
  border: solid 2px transparent;
  border-right: none;
  border-top: none;
  transform: translate(3px, 4px) rotate(-45deg);
}

/* Checked, Indeterminate */
.row-checkbox > input:checked,
.row-checkbox > input:indeterminate {
  background-color: rgb(var(--pure-material-primary-rgb, 33, 150, 243));
}

.row-checkbox > input:checked + span::before,
.row-checkbox > input:indeterminate + span::before {
  border-color: #c5cbd4;
}

.row-checkbox > input:checked + span::after,
.row-checkbox > input:indeterminate + span::after {
  border-color: #718096;
}

.row-checkbox > input:indeterminate + span::after {
  border-left: none !important;
  border-color: #718096;
  transform: translate(3px, 4px);
}

.check-indeterminate + span::after {
  border-left: none !important;
  border-color: #718096;
  transform: translate(3px, 4px) !important;
}

/* Hover, Focus */
.row-checkbox:hover > input {
  opacity: 0.04;
}

.row-checkbox > input:focus {
  opacity: 0.12;
}

.row-checkbox:hover > input:focus {
  opacity: 0.16;
}

/* Disabled */
.row-checkbox > input:disabled {
  opacity: 0;
  background-color: transparent;
}

.row-checkbox > input:disabled + span {
  color: rgba(var(--pure-material-onsurface-rgb, 0, 0, 0), 0.38);
  cursor: initial;
}

.row-checkbox > input:disabled + span::before {
  border-color: currentColor;
}

.row-checkbox > input:checked:disabled + span::before,
.row-checkbox > input:indeterminate:disabled + span::before {
  border-color: #f5f5f5;
  background-color: #f5f5f5;
}
.row-checkbox > input:checked:disabled + span::after,
.row-checkbox > input:indeterminate:disabled + span::after {
  border-color: #f5f5f5;
}

.col-end-end {
  grid-column-end: -1;
}

/*
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-corner {
  background: #2196f3;
}
::-webkit-scrollbar-thumb {
  background: #2196f3;
  border-radius: 15px;
  border: 2px solid transparent;
  cursor: grab;
  background-clip: padding-box;
}
::-webkit-scrollbar-track {
  background: #3e4251;
  border-color: #585858;
  border-style: solid;
}  */
//v2
/*
::-webkit-scrollbar {
    width: 16px
}

::-webkit-scrollbar-thumb {
    background: #dadce0;
    background-clip: padding-box;
    border: 4px solid transparent;
    border-radius: 8px;
    box-shadow: none;
    min-height: 50px
}
::-webkit-scrollbar-track {
    background: none;
    border: none
}

::-webkit-scrollbar-track:hover {
    background: none;
    border: none
}  */
</style>
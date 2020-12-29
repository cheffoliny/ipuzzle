<template>
  <div
    v-if="loaded"
    class="flex flex-col h-screen antialiased bg-white border-t-4 border-orange-500 pt-3 pb-2 px-3"
  >
    <div class="header" :class="headerCollapse ? 'header-collapse' : 'header-full'">
      <div class="client">
        <label class="custom-label deliverer-label" for="client">клиент:</label>
        <select
          v-if="!urlParams.id"
          id="client"
          class="custom-input col-start-2 col-end-5"
          v-model="client"
          :disabled="disableClientChange"
        >
          <option>изберете</option>
          <option
            v-for="(client, index) in clients"
            :value="client.name"
            :key="index"
            v-text="client.name"
          ></option>
        </select>
        <div v-else class="custom-input col-start-2 col-end-5 truncate" v-text="document_data.client_name"></div>

        <div v-show="!headerCollapse" class="custom-label">адрес:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="client_address"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="client === 'изберете' ? '' : clients[selectedClient].address"
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="client_address"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="document_data.client_address"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">ин:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="client_in"
          class="custom-input truncate"
          v-text="client === 'изберете' ? '' : clients[selectedClient].ein"
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="client_in"
          class="custom-input truncate"
          v-text="document_data.client_ein"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">по ддс:</div>
        <div
          v-show="!headerCollapse"
          v-if="!urlParams.id"
          id="client_indds"
          class="custom-input truncate"
          v-text="client === 'изберете' ? '' : clients[selectedClient].ein_dds"
        ></div>
        <div
          v-show="!headerCollapse"
          v-else
          id="client_indds"
          class="custom-input truncate"
          v-text="document_data.client_ein_dds"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">мол:</div>
        <div
          v-if="!urlParams.id"
          v-show="!headerCollapse"
          id="client_mol"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="client === 'изберете' ? '' : clients[selectedClient].mol"
        ></div>
        <div
          v-else
          v-show="!headerCollapse"
          id="client_mol"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="document_data.client_mol"
        ></div>
      </div>

      <div class="doc">
        <div class="custom-label">номер:</div>
        <input v-if="document_data.doc_status !== 'canceled'"
          class="custom-input book-doc-num"
          type="number"
          step="1"
          min="0"
          @keydown="filterKey"
          @input="filterInput"
          v-model="document_data.doc_num"
        />
        <div v-else class="custom-input truncate">{{document_data.doc_num}}</div>

        <template v-if="!urlParams.id">
          <label v-show="!headerCollapse" class="custom-label" for="doc_type">вид:</label>
          <select v-show="!headerCollapse" id="doc_type" class="custom-input" v-model="doc_type">
            <option value="faktura">Фактура</option>
            <option value="kvitanciq">Проформа фактура</option>
            <option value="oprostena">Квитанция</option>
          </select>
        </template>
        <template v-else>
          <div v-show="!headerCollapse" class="custom-label">вид:</div>
          <div v-show="!headerCollapse" class="custom-input">{{docType.name}}</div>
        </template>

        <label v-show="!headerCollapse" class="custom-label" for="created_date">издаване:</label>
        <datepicker
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

        <label v-show="!headerCollapse" class="custom-label" for="event_date">събитие:</label>
        <datepicker
          v-show="!headerCollapse"
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
        <label class="custom-label" for="deliverer">доставчик:</label>
        <Suggest
          id="deliverer"
          name="deliverer"
          v-if="urlParams.id === null"
          api="api/api_sale_suggest.php?action=client"
          v-model="search"
          :result_id.sync="deliverer.id"
          :autofocus="!deliverer.id || !urlParams.id ? true : false"
          @clear-data="resetDeliverer()"
          @select-client="setDeliverer($event)"
        ></Suggest>
        <Suggest
          id="deliverer"
          name="deliverer"
          v-else-if="showChangeDelivererDialog"
          api="api/api_sale_suggest.php?action=client"
          v-model="searchNewDeliverer"
          :autofocus="true"
          @select-client="updateDeliverer($event)"
          @hide="searchNewDeliverer = '', showChangeDelivererDialog = false"
        ></Suggest>
        <div v-else id="deliverer" class="flex items-center custom-input col-start-2 col-end-5">
          <div
            @click="allowChangeDeliverer"
            class="w-full truncate"
            :class="search === '' ? 'min-h-18px' : ''"
            v-text="urlParams.id ? search : deliverer.name"
          ></div>

          <button
            v-if="showRemoveDelivererButton"
            title="Премахни доставчика"
            v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
            class="ml-2 focus:outline-none focus:shadow-outline"
            @click="clearDeliverer()"
          >
            <i class="fad fa-times fa-fw"></i>
          </button>
          <button
            v-if="document_data.id_deliverer"
            title="Отвори картона на клиента"
            v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
            class="pl-2 focus:outline-none"
            @click="openDeliverer"
          >
            <i class="fad fa-external-link fa-fw"></i>
          </button>
        </div>
        <div v-show="!headerCollapse" class="custom-label">адрес:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="deliverer.invoice_address"
        ></div>

        <div v-show="!headerCollapse" class="custom-label">ин:</div>
        <div v-show="!headerCollapse" class="custom-input truncate" v-text="deliverer.invoice_ein"></div>

        <div v-show="!headerCollapse" class="custom-label">по ддс:</div>
        <div v-show="!headerCollapse" class="custom-input truncate" v-text="deliverer.invoice_ein_dds"></div>

        <div v-show="!headerCollapse" class="custom-label">мол:</div>
        <div
          v-show="!headerCollapse"
          class="custom-input col-start-2 col-end-5 truncate"
          v-text="deliverer.invoice_mol"
        ></div>
      </div>
    </div>
    <!--
    <div v-if="client !== 'изберете'" class="flex w-full items-center my-2">
      <div
        v-if="document_data.doc_status === 'canceled'"
        class="flex items-center justify-center w-auto h-8 bg-pink-500 text-white text-xs px-2 rounded-sm tracking-wide font-medium"
      >АНУЛИРАН</div>

      <button
        v-if="allowNewRows"
        title="Добави ред"
        v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
        @click="showFreeSaleComponent = true"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 text-sm mr-2 ml-auto"
      >
        <i class="far fa-fw fa-plus"></i>
      </button>

      <button
        :title="headerCollapse ? 'Разшири' : 'Смали'"
        v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
        :class="allowNewRows ? '' : 'ml-auto'"
        class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 text-sm"
        @click="headerCollapse = !headerCollapse"
      >
        <i :class="headerCollapse ? 'fal fa-arrows-v fa-fw' : 'fal fa-arrow-to-top fa-fw'"></i>
      </button>
    </div>-->

     <div v-if="showToolbar" 
     :class="document_data.is_advice || document_data.id_advice || is_new_relative_doc ? 'justify-between' : 'justify-end'"
     class="flex w-full items-center my-2">
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
            @click="openRelativeDocument(origin_document.id || null, 'buy')"
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
            @click="openRelativeDocument(relative_document.id, 'buy')"
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
      <div class="flex items-center flex-wrap gap-2">
        <div
          v-if="document_data.doc_status === 'canceled'"
          class="flex items-center justify-center w-auto h-8 bg-pink-500 rounded-sm text-white text-xs px-2 rounded-sm tracking-wide font-medium"
        >
          АНУЛИРАН
        </div>

        <button
          v-if="urlParams.id && allowRelativeDocuments"
          title="Дебитно известие"
          v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
          @click="addDebitBuyDoc"
          class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >
          <i class="fad fa-layer-plus fa-lg fa-fw"></i>
        </button>

        <button
          v-if="urlParams.id && allowRelativeDocuments"
          title="Кредитно известие"
          v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
          @click="addCreditBuyDoc"
          class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:bg-indigo-400 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >
          <i class="fad fa-layer-minus fa-lg fa-fw"></i>
        </button>

        <button
          title="Добави ред"
          v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
          v-if="allowNewRows"
          @click="showFreeSaleComponent = true"
          class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400 shadow-custom hover:text-white hover:bg-indigo-400 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 text-sm"
        >
          <i class="far fa-fw fa-plus"></i>
        </button>
        
        <button
          :title="headerCollapse ? 'Разшири' : 'Смали'"
          v-tippy="{ trigger: 'mouseenter', placement: 'bottom', arrow: true }"
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
    </div>

    <div id="grid" v-if="doc_rows.length" class="grid-data mb-2 border-b border-gray-200 shadow-sm">
      <!-- headers -->
      <div class="grid-row headers-buy shadow">
        <div v-if="!urlParams.id" class="grid-cell check">
          <i class="fal fa-square fa-fw"></i>
        </div>
        <div :class="urlParams.id ? 'col-span-2' : '' " class="grid-cell text-center">№</div>
        <div class="grid-cell firm-region">фирма / регион</div>
        <div class="grid-cell service-nomenclature">фонд / номенклатура</div>
        <div class="grid-cell period">за месец</div>
        <div class="grid-cell total">сума</div>
        <div class="grid-cell action text-center">...</div>
      </div>

      <template v-if="doc_rows.length">
        <div class="grid-row" v-for="(row, index) in doc_rows" :key="row.uuid">
          <!-- row num -->
          <div class="grid-cell row-num col-span-2">{{index +1}}</div>
          <!-- firm / region -->
          <div class="grid-cell">
            <div class="flex primary-info">
              <div
                :title="row.firm"
                class="primary-info font-medium w-full truncate focus:outline-none"
              >{{row.firm}}</div>
            </div>
            <!-- v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}" -->
            <div
              :title="row.region"
              
              class="secondary-info uppercase truncate tracking-wide focus:outline-none"
            >{{row.region}}</div>
          </div>
          <!-- fund / nomenclature -->
          <div class="grid-cell">
            <template v-if="row.id_direction !== 0">
              <div
                class="primary-info font-medium w-full truncate focus:outline-none"
                :title="row.fund"
              >{{row.fund}}</div>
              <div
                :title="row.nomenclature"
                v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
                class="secondary-info uppercase truncate tracking-wide focus:outline-none"
              >{{row.nomenclature}}</div>
            </template>
            <template v-else>
              <div class="primary-info font-medium w-full truncate focus:outline-none">ддс трансфер</div>
              <div
                :title="row.nomenclature"
                v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
                class="secondary-info uppercase truncate tracking-wide focus:outline-none"
              >{{row.nomenclature}}</div>
            </template>
          </div>
          <!-- month -->
          <div class="grid-cell text-center">
            <div class="primary-info month">{{row.month | date}}</div>
          </div>
          <!-- sum -->
          <div class="grid-cell text-right">
            <div class="primary-info truncate">{{row.total_sum | price}}</div>
          </div>
          <!-- actions -->
          <div class="grid-cell">
            <div class="flex items-center justify-around">
              <button
                v-if="document_data.doc_status !== 'canceled'"
                title="Редактирай"
                v-tippy="{ trigger : 'mouseenter', placement : 'bottom', arrow : true}"
                @click="editService(row)"
                type="button"
                class="w-6 h-6 text-gray-700 focus:outline-none focus:shadow-outline hover:text-blue-500"
              >
                <i class="fad fa-edit fa-fw"></i>
              </button>
              <button
                v-if="document_data.doc_status !== 'canceled'"
                title="Клонирай"
                v-tippy="{ trigger : 'mouseenter', placement : 'bottom', arrow : true}"
                @click="cloneService(row)"
                type="button"
                class="w-6 h-6 text-gray-700 focus:outline-none focus:shadow-outline hover:text-blue-500"
              >
                <i class="fad fa-copy fa-fw"></i>
              </button>
            </div>
          </div>
          <!-- note -->
          <div
            v-if="row.note"
            :title="row.note"
            class="grid-cell text-left primary-info col-start-3 col-end-end w-full truncate self-center border border-dashed border-gray-500 px-2 mt-1"
          >{{row.note}}</div>
        </div>
      </template>
    </div>

    <div class="flex-1"></div>

    <freeSaleBuyComponent
      v-if="showFreeSaleComponent"
      :vat_transfer="allowVatTransfer"
      :vat_transfer_nomenclature="vatTransferNomenclature"
      :vat_mode="doc_rows.length && allowVatTransfer ? true : false"
      :client_id_office_dds="clients[selectedClient].id_office_dds"
      :firms="selectedFirms"
      :regions="regions"
      :funds="funds"
      :nomenclatures="nomenclatures"
      :nomenclature_groups="nomenclature_groups"
      :date="document_data.doc_date_create"
      :service="serviceForEdit"
      @closeFreeSale="closeService"
      @addService="addService"
      @updateService="updateService"
      @deleteService="deleteService"
    />

    <payment-form
      v-if="showPaymentForm && loaded"
      :doc_status="document_data.doc_status"
      :doc_type="document_data.doc_type"
      @closePaymentForm="showPaymentForm = false"
      @reload="(showPaymentForm = false, getDocumentByID())"
      :doc_id="urlParams.id"
      :bank_accounts="bank_accounts"
      :id_cash_default="idCashDefault"
      :orders="orders"
      mode="buy"
      :doc_sum="document_data.total_sum"
      :paid_sum="document_data.orders_sum"
    />

    <relative-documents-list
      mode="buy"
      :id_sale_doc="relativeId"
      :exclude_id="document_data.id"
      v-if="showRelativeDocumentsList"
      @closeRelativeDocuemntsList="showRelativeDocumentsList = false"
    />

    <div v-if="doc_rows.length" class="footer mb-2 footer-buy">
      <div class="f-left">
        <textarea
          autocomplete="off"
          spellcheck="false"
          placeholder="бележка..."
          class="resize-none w-full h-full col-start-1 col-end-3 custom-input buy-doc-note"
          v-model.lazy="document_data.note"
        ></textarea>
      </div>

      <div class="f-middle">
        <template v-if="urlParams.id">
          <div class="custom-label">дължими:</div>
          <div class="custom-input text-right">
            {{(document_data.total_sum - document_data.orders_sum) | price }} лв.
          </div>
          <div class="custom-label">платени:</div>
          <div class="custom-input text-right">
            {{document_data.orders_sum | price }} лв.
          </div>
        </template>
      </div>

      <div class="f-right">
          <label v-if="showVat" class="custom-label col-start-3 col-end-4" for="vatSum">ддс:</label>
          <currency-input
            autocomplete="off"
            v-if="showVat"
            name="vatSum"
            id="vatSum"
            class="custom-input"
            v-model="vat"
            @input="syncDocSum"
            :currency="null"
          />
          <label class="custom-label col-start-3 col-end-4" for="totalSum">тотал:</label>
          <currency-input
            autocomplete="off"
            name="totalSum"
            id="totalSum"
            class="custom-input"
            v-model="total"
            :currency="null"
          />
      </div>
    </div>

    <div
      v-if="doc_rows.length"
      class="flex flex-wrap justify-between items-center w-full pt-2 border-t border-gray-200"
    >
      <div class="flex flex-shrink-0 h-8 items-center text-indigo-500 truncate mr-2">
        <i class="fad fa-info-circle text-2xl mr-2"></i>
      </div>
      <div v-if="urlParams.id" class="flex flex-col truncate text-xss text-cool-gray-500 mr-2">
        <span :title="document_data.created" class="flex mr-2">
          <span class="w-16 mr-2">създал :</span>
          <span class="truncate">{{document_data.created}}</span>
        </span>

        <span :title="document_data.updated" class="flex mr-2">
          <span class="w-16 mr-2">редактирал :</span>
          <span class="truncate">{{document_data.updated}}</span>
        </span>
      </div>
      <div v-if="doc_rows.length" class="flex flex-wrap w-auto ml-auto">
        <button
          v-if="urlParams.id && !is_new_relative_doc"
          :disable="loading"
          @click="showPaymentForm = true"
          class="w-28 h-8 p-2 shadow-custom rounded-sm bg-white text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2"
        >плащане</button>
        <button
          v-if="urlParams.id && !is_new_relative_doc && document_data.doc_status !== 'canceled'"
          :disable="loading"
          @click="cancelDoc"
          class="w-28 h-8 p-2 shadow-custom rounded-sm bg-gray-500 text-white text-xss uppercase font-medium tracking-wide hover:bg-gray-600 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2"
        >анулирай</button>
        <button
          v-if="!urlParams.id"
          :disable="loading"
          @click="storeClientObligations"
          class="w-28 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >запиши</button>
        <button
          v-if="urlParams.id && document_data.doc_status !== 'canceled'"
          :disable="loading"
          @click="updateDoc"
          class="w-28 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >потвърди</button>
      </div>
    </div>
    <Loader :fillScreen="true" v-if="loading" />
  </div>
  <Loader v-else preloader :fillScreen="true"></Loader>
</template>

<script>
import Loader from "./Loader.vue";
import Suggest from "./SuggestDeliverer.vue";
import Datepicker from "./Datepicker.vue";
import BaseDialog from "./BaseDialog.vue";
import freeSaleBuyComponent from "./freeSaleBuyComponent.vue";
import Nomenclature from "./Nomenclature.vue";
import PaymentForm from "./PaymentForm.vue";
import RelativeDocumentsList from "./RelativeDocumentsList.vue";
import VueCurrencyInput from "vue-currency-input";
import { uuidMixin } from "./uuidMixin"
import { dateMixin } from "./dateMixin"
import { utilityMixin } from "./utilityMixin"
import { openRelDocMixin } from "./openRelDocMixin"
import mixins from "./mixins";

export default {
  name: "BuyDoc",
  components: {
    Suggest,
    Datepicker,
    Loader,
    BaseDialog,
    VueCurrencyInput,
    freeSaleBuyComponent,
    PaymentForm,
    Nomenclature,
    RelativeDocumentsList,
  },
  mixins: [
    uuidMixin,
    dateMixin,
    utilityMixin,
    mixins,
    openRelDocMixin,
  ],
  data() {
    return {
      showChangeDelivererDialog: false,
      showPaymentForm: false,
      showFreeSaleComponent: false,
      showRelativeDocumentsList: false,
      freeSaleComponentMode: "",
      headerCollapse: false,
      relative_document: {},
      origin_document: {},
      is_new_relative_doc: false,
      relative_credit_maxValue: 0,
      bank_accounts: [],
      search: "",
      searchNewDeliverer: "",
      deliverer: {
        id: null,
        name: "",
        address: "",
        invoice_address: "",
        invoice_ein: "",
        invoice_ein_dds: "",
        invoice_mol: "",
        invoice_layout: "",
        name: "",
        phone: "",
      },
      default_deliverer: {
        id: null,
        name: "",
        invoice_address: "",
        invoice_ein: "",
        invoice_ein_dds: "",
        invoice_mol: "",
        invoice_recipient: "",
        invoice_layout: "",
        invoice_payment: "",
        phone: "",
        note: "",
      },
      doc_date_create: "",
      doc_date: "",
      doc_rows: [],
      document_data: [],
      client: "изберете",
      clients: [],
      selectedFirm: null,
      serviceForEdit: {},
      firms: [],
      regions: [],
      doc_type: "faktura",
      loaded: false,
      loading: false,
      doc_rows_dds: [],
      orders: [],
      funds: [],
      nomenclatures: [],
      nomenclature_groups: [],
      annulment: false,
      vat: 0,
      total: 0,
    };
  },
  methods: {
    cloneService(row) {
      let clone = JSON.parse(JSON.stringify(row))
      clone.uuid = this.genUuid()
      this.doc_rows.push(clone)
      this.syncDocSum()
    },
    syncDocSum() {
      if(this.vat) {
        this.total = (this.vat + this.getTotalSumFromArr(this.doc_rows))
      } else {
        this.total = this.getTotalSumFromArr(this.doc_rows)
      }
    },
    addService(service) {
      this.doc_rows.push(service);
      this.syncDocSum()
      this.showFreeSaleComponent = false;
    },
    editService(service) {
      if (this.document_data.doc_status !== "canceled") {
        this.serviceForEdit = service;
        this.showFreeSaleComponent = true;
      } else {
        alert("Грешка: Анулиран документ не подлежи на редакция!");
      }
    },
    updateService(service) {
      this.closeService();
      let objIndex = this.doc_rows.findIndex((el) => el.uuid === service.uuid);
      this.$set(this.doc_rows, objIndex, service);
      this.syncDocSum()
    },
    deleteService(service) {
      this.closeService();
      let objIndex = this.doc_rows.findIndex((el) => el.uuid === service.uuid);
      this.doc_rows.splice(objIndex, 1);
      if(!this.doc_rows.length) {
        if(this.doc_rows_dds.length) {
          this.doc_rows_dds = []
        }
        this.vat = 0
        this.total = 0
      }
      this.syncDocSum()
    },
    storeClientObligations() {
      if (!this.urlParams.id || this.is_new_relative_doc) {
        if (!this.doc_rows.length) {
          alert("ГРЕШКА: не може да се издаде празен документ добавете ред!");
          return;
        }
      }

      if (
        this.doc_type !== "oprostena" &&
        !parseInt(this.document_data.doc_num)
      ) {
        alert(
          `ГРЕШКА: не може да се издаде документ различен от квитанция с нулев номер!`
        );
        return;
      }

      if (this.doc_type !== "oprostena" && !this.deliverer.id) {
        alert(
          `ГРЕШКА: не може да се издаде документ различен от квитанция без клиент!`
        );
        return;
      }

      this.loading = true;
      this.document_data.doc_date = this.doc_date;
      this.document_data.doc_date_create = this.doc_date_create;
      this.document_data.doc_type = this.doc_type;
      this.document_data.client_name = this.clients[this.selectedClient].name;
      this.document_data.client_address = this.clients[
        this.selectedClient
      ].address;
      this.document_data.client_ein = this.clients[this.selectedClient].ein;
      this.document_data.client_ein_dds = this.clients[
        this.selectedClient
      ].ein_dds;
      this.document_data.client_mol = this.clients[this.selectedClient].mol;
      this.document_data.client_recipient = this.clients[
        this.selectedClient
      ].mol;
      this.document_data.id_deliverer = this.deliverer.id;
      this.document_data.deliverer_name = this.deliverer.name;
      this.document_data.deliverer_address = this.deliverer.invoice_address;
      this.document_data.deliverer_ein = this.deliverer.invoice_ein;
      this.document_data.deliverer_ein_dds = this.deliverer.invoice_ein_dds;
      this.document_data.deliverer_mol = this.deliverer.invoice_mol;

      axios
        .post(this.actionEndPoint, {
          document_data: this.document_data,
          document_rows: this.doc_rows,
          vatSum: this.vat,
          totalSum: this.total
        })
        .then(({ data } = response) => {
          this.loading = false;
          window.location.replace(
            `page.php?page=buy_new&id=${data.document_data.id}`
          );
        })
        .catch((error) => {
          if (this.annulment) this.annulment = false;
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    },
    initBuyDoc() {
      axios
        .get("api/api_buy_controller.php?action=init")
        .then(({ data } = response.data) => {
          this.bank_accounts = data.bank_accounts;
          this.clients = data.clients;
          this.client = data.default_client;
          this.document_data = data.document_data;
          this.firms = this.arrSortByPropName(data.firms, "name");
          this.regions = this.arrSortByPropName(data.regions, "region");
          this.funds = this.arrSortByPropName(data.funds, "name");
          this.nomenclatures = this.arrSortByPropName(
            data.nomenclatures,
            "name"
          );
          this.nomenclature_groups = this.arrSortByPropName(
            data.nomenclature_groups,
            "name"
          );
          this.doc_date = data.document_data.doc_date;
          this.doc_date_create = data.document_data.doc_date_create;

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
          console.log(error.response);
          alert(error.response.data.error);
        });
    },
    getDocumentByID() {
      this.loading = true;
      axios
        .get(`api/api_buy_controller.php?action=load&id=${this.urlParams.id}`)
        .then(({ data } = response.data) => {
          this.document_data = {
            ...this.document_data,
            ...data.document_data,
          };
          this.doc_date = data.document_data.doc_date;
          this.doc_date_create = data.document_data.doc_date_create;
          this.search = data.document_data.deliverer_name;
          this.deliverer.id = data.document_data.id_deliverer;
          this.deliverer.name = data.document_data.deliverer_name;
          this.deliverer.invoice_address = data.document_data.deliverer_address;
          this.deliverer.invoice_ein = data.document_data.deliverer_ein;
          this.deliverer.invoice_ein_dds = data.document_data.deliverer_ein_dds;
          this.deliverer.invoice_mol = data.document_data.deliverer_mol;
          this.doc_type = data.document_data.doc_type;

          this.origin_document = data.origin_document;

          this.doc_rows = data.document_rows
            .filter((row) => row.is_dds !== 1)
            .map((obj) => ({
              ...obj,
              uuid: this.genUuid(),
            }));
          this.doc_rows_dds = data.document_rows.filter(
            (row) => row.is_dds === 1
          );
          this.vat = this.doc_rows_dds.length ? this.doc_rows_dds[0].total_sum : 0
          this.total = this.doc_rows.length ? this.document_data.total_sum : 0
          this.doc_rows
            .filter((el) => el.is_dds > 1)
            .forEach((row) => (row.id_nomenclature_expense = this.vatTransferNomenclature.id));

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
          console.log(error.response);
          alert(`Грешка: ${error.response.data.error}`);
        });
    },
    prepareNewRelativeDoc() {
      this.relative_document = JSON.parse(JSON.stringify(this.document_data));
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
      this.document_data.note = "";
      this.document_data.view_type = "extended";
      this.document_data.is_advice = 0;
      this.document_data.id_advice = 0;
      this.document_data.exported = 0;
      this.document_data.is_hide = 0;
      this.document_data.created = "";
      this.document_data.created_time = "";
      this.document_data.created_user = 0;
      this.document_data.updated = "";
      this.document_data.updated_time = "";
      this.document_data.updated_user = 0;
      this.vat = 0
      this.total = 0
    },
    prepareDebitBuyDoc() {
      this.doc_type = "debitno izvestie";
      this.document_data.doc_type = "debitno izvestie";
      this.setWindowTitle();
      window.name = `new_buy_debit_doc${this.urlParams.id}`;
    },
    prepareCreditBuyDoc() {
      this.doc_type = "kreditno izvestie";
      this.document_data.doc_type = "kreditno izvestie";
      this.setWindowTitle();
      window.name = `new_buy_credit_doc${this.urlParams.id}`;
    },
    addDebitBuyDoc() {
      if (confirm("Създай дебитно известие ?")) {
        this.prepareNewRelativeDoc();
        this.prepareDebitBuyDoc();
      }
    },
    addCreditBuyDoc() {
      if (confirm("Създай кредитно известие ?")) {
        this.relative_credit_maxValue = this.baseSum;
        this.prepareNewRelativeDoc();
        this.prepareCreditBuyDoc();
      }
    },
    setDeliverer($event) {
      if (!$event.id) return;
      this.deliverer = $event;
    },
    updateDeliverer($event) {
      if (
        confirm(
          `Потвърдете избор на доставчик ${$event.name} с клиентски номер: ${$event.id}`
        )
      ) {
        this.setDeliverer($event);
        this.search = $event.name;
        this.searchNewDeliverer = "";
        this.showChangeDelivererDialog = false;
        this.document_data.id_deliverer = $event.id;
        this.document_data.deliverer_name = $event.name;
        this.document_data.deliverer_address = $event.invoice_address;
        this.document_data.deliverer_ein = $event.invoice_ein;
        this.document_data.deliverer_ein_dds = $event.invoice_ein_dds;
        this.document_data.deliverer_mol = $event.invoice_mol;
      }
    },
    openDeliverer() {
      dialogClientInfo(this.deliverer.id);
    },
    clearDeliverer() {
      this.document_data.id_deliverer = 0;
      this.document_data.deliverer_name = "";
      this.document_data.deliverer_address = "";
      this.document_data.deliverer_ein = "";
      this.document_data.deliverer_ein_dds = "";
      this.document_data.deliverer_mol = "";
      this.search = "";
      this.resetDeliverer();
    },
    resetDeliverer() {
      this.deliverer = JSON.parse(JSON.stringify(this.default_deliverer));
      this.showChangeDelivererDialog = true;
    },
    allowChangeDeliverer() {
      if (this.document_data.doc_status === "canceled") return;
      this.showChangeDelivererDialog = true;
    },
    openRelativeDocumentsList() {
      this.showRelativeDocumentsList = true;
    }
  },
  computed: {
    idCashDefault() {
      return !this.loaded 
        ? null
        : this.bank_accounts.find(el => el.id === this.document_data.id_cash_default) ?? null
    },
    showToolbar() {
      return this.client !== 'изберете' || this.doc_type === "oprostena" ? true : false;
    },
    showRemoveDelivererButton() {
      if(!this.urlParams.id) {
        return true
      }
      else if (this.urlParams.id && this.doc_type === 'oprostena' && this.search && this.document_data.doc_status !== 'canceled') {
        return true
      }
       else {
        return false
      }
    },
    allowNewRows() {
      if (!this.loaded) return false;

      if (this.urlParams.id && this.document_data.doc_status === "canceled") {
        return false;
      }
      if (!this.deliverer.id) {
        return this.doc_type === "oprostena" ? true : false;
      }
      return true;
    },
    allowRelativeDocuments() {
      if(!this.loaded) return false;
      if(this.document_data.doc_status === 'canceled') return false;
      if (this.document_data.doc_type === "oprostena" || this.document_data.doc_type === "kvitanciq" || this.document_data.doc_type === "debitno izvestie" || this.document_data.doc_type === "kreditno izvestie") return false;
      return true;
    },
    allowVatTransfer() {
      if (this.doc_type !== "oprostena") {
        return false;
      } else if (this.doc_type === "oprostena" && this.doc_rows_dds.length) {
        return false;
      } else {
        let rowsNomenclatureIds = [
          ...new Set(
            this.doc_rows.map(
              ({ id_nomenclature_expense }) => id_nomenclature_expense
            )
          ),
        ];
        return rowsNomenclatureIds.every(
          (id) => id === this.vatTransferNomenclature.id
        );
      }
    },
    disableClientChange() {
      return this.doc_rows.length ? true : false;
    },
    docTitle() {
      return this.urlParams.id && this.docType
        ? `[-] ${this.docType.abbr}`
        : "Продажба разход";
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
        if (this.document_data.doc_type === "faktura") {
          return {
            name: "Фактура",
            abbr: `Ф-ра ${suffix}`,
          };
        }
        if (this.document_data.doc_type === "oprostena") {
          return {
            name: "Квитанция",
            abbr: `Кв. ${suffix}`,
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
      }
    },
    actionEndPoint() {
      if (!this.urlParams.id && !this.is_new_relative_doc && !this.annulment) {
        return "api/api_buy_controller.php?action=store";
      }
      if (this.urlParams.id && this.is_new_relative_doc && !this.annulment) {
        return "api/api_buy_controller.php?action=make_advice";
      }
      if (this.urlParams.id && !this.is_new_relative_doc && !this.annulment) {
        return "api/api_buy_controller.php?action=update";
      }
      if (this.annulment) {
        return "api/api_buy_controller.php?action=annulment";
      }
    },
    selectedClient() {
      return this.client !== "изберете"
        ? this.clients.map((client) => client.name).indexOf(this.client)
        : this.client;
    },
    selectedFirms() {
      if (!this.loaded || this.client === "изберете") return null;
      let firms = !this.document_data.buy_doc_grant
        ? this.firms.filter((firm) => firm.idn === this.clients[this.selectedClient].ein)
        : this.firms

      if (firms.length) {
        return this.arrSortByPropName(firms, "name");
      }
      return null;
    },
    showVat() {
     if(!this.urlParams.id) {
        return this.doc_type !== "oprostena" ? true : false
      }
      return this.doc_type !== "oprostena" || this.document_data.doc_type !== "oprostena"
        ? true
        : false;
    },
    vatTransferNomenclature() {
      if (this.nomenclatures.length) {
        return this.nomenclatures.find(
          (nomenclature) => nomenclature.vat_transfer === 1
        );
      }
      return null;
    },
    relativeId() {
      return this.document_data.doc_type === "debitno izvestie" ||
        this.document_data.doc_type === "kreditno izvestie"
        ? this.document_data.id_advice
        : this.urlParams.id;
    },
  },
  mounted() {
    this.initBuyDoc();
  },
};
</script>
<style lang="scss" scoped>
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
.footer-buy {
  min-height: 72px;
}
.buy-doc-note {
  min-height: 72px !important;
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
      minmax(14px, 14px)
      minmax(14px, 14px)
      minmax(150px, 100%)
      minmax(150px, 100%)
      minmax(70px, 70px)
      minmax(80px, 80px)
      minmax(62px, 62px);
    grid-auto-rows: min-content;
    align-items: center;
    //align-items: start;
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
    & .period {
      text-align: center;
    }
    & .total {
      justify-self: end;
      & > div {
        text-align: right;
      }
    }
  }
  & .headers-buy {
    position: sticky;
    top: 0;
    font-size: 10px;
    align-items: center;
    padding: 8px;
    z-index: 20;
    color: #fff;
    background: #ff5a1f;
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
    color: #0d79bb;
    font-size: 10px;
    border-radius: 999px;
    user-select: none;
  }
}

.custom-label {
  font-size: 12px;
  color: #2d3e52;
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
    border-color: #0d79bb;
    box-shadow: 0 0 0 1px #0d79bb inset;
  }
  &:focus-within {
    outline: none;
    border-color: #0d79bb;
    box-shadow: 0 0 0 1px #0d79bb inset;
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
  color: #2d3e52;
  font-size: 11px;
  text-transform: lowercase;
}
.month {
  background: #2d3e52;
  color: #ffffff;
  border-radius: 4px;
  padding: 4px 6px;
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
.min-h-18px {
  min-height: 18px;
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
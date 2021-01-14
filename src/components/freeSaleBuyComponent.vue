<template>
  <baseDialog static>
    <template #header>
      <div class="flex items-center p-3 rounded-t-sm bg-indigo-500 flex-shrink-0">
        <div
          class="text-xs tracking-wide text-white font-medium truncate uppercase"
          v-text="edit ? 'Редакция на ред' : 'Добавяне на ред'"
        ></div>
        <button
          v-if="vat_transfer && !vat_mode"
          class="flex items-center ml-auto focus:outline-none focus:shadow-outline px-2"
          type="button"
          @click="toggleVatTransfer"
        >
          <span class="text-xs tracking-wide text-white font-medium uppercase mr-2">.: ддс :.</span>
          <i
            :class="enableVatTransfer ? 'fa-check-square' : 'fa-square'"
            class="fad fa-fw text-white"
          ></i>
        </button>
        <button
          @click="closeServiceComponent"
          :class="vat_transfer && !vat_mode ? 'ml-2 ' : 'ml-auto'"
          class="text-sm focus:outline-none focus:shadow-outline"
        >
          <i class="fal fa-times fa-fw"></i>
        </button>
      </div>
    </template>

    <template #body>
      <div class="flex flex-col bg-white overflow-y-auto p-3 pb-0">
        <!-- фирма -->
        <div class="flex items-center justify-between mb-2">
          <template v-if="vat_mode || enableVatTransfer">
            <div class="custom-label w-24 flex-shrink-0">фирма:</div>
            <div class="custom-input" v-text="serviceTemplate.firm"></div>
          </template>
          <template v-else>
            <label class="custom-label w-24 flex-shrink-0" for="selectfirm">фирма:</label>
            <select
              id="selectfirm"
              class="custom-input"
              v-model="serviceTemplate.id_firm"
              @change="changeFirm"
            >
              <option v-for="firm in firms" :key="firm.id_firm" :value="firm.id_firm">{{firm.name}}</option>
            </select>
          </template>
        </div>
        <!-- региони към фирмата -->
        <div v-if="serviceTemplate.id_firm" class="flex items-center justify-between mb-2">
          <template v-if="vat_mode || enableVatTransfer">
            <div class="custom-label w-24 flex-shrink-0">регион:</div>
            <div class="custom-input" v-text="serviceTemplate.region"></div>
          </template>
          <template v-else>
            <label class="custom-label w-24 flex-shrink-0" for="selectregion">регион:</label>
            <select
              id="selectregion"
              class="custom-input"
              v-model="serviceTemplate.id_office"
              @change="changeRegion"
            >
              <option
                v-for="region in firmRegions"
                :key="region.id_office"
                :value="region.id_office"
              >{{region.region}}</option>
            </select>
          </template>
        </div>

        <template v-if="vat_mode || enableVatTransfer">
          <div class="flex items-center justify-between mb-2">
            <div class="custom-label w-24 flex-shrink-0">номенклатура:</div>
            <div class="custom-input" v-text="vat_transfer_nomenclature.name"></div>
          </div>
        </template>

        <!-- направления -->
        <div
          v-if="serviceTemplate.id_office && !vat_mode && !enableVatTransfer"
          class="flex items-center justify-between mb-2"
        >
          <label class="custom-label w-24 flex-shrink-0" for="selectDirection">направление :</label>
          <select
            id="selectDirection"
            class="custom-input"
            v-model="serviceTemplate.id_direction"
            @change="changeDirection"
          >
            <option v-for="direction in regionDirections" :key="direction.id" :value="direction.id">{{direction.name}}</option>
          </select>
        </div>
        <!-- групи разходни номенклатури към направление-->
        <div v-if="serviceTemplate.id_direction" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="selectfundservicegroup">група :</label>
          <select
            id="selectfundservicegroup"
            class="custom-input"
            v-model="id_nomenclature_group"
            @change="changeNomenclatureGroup"
          >
            <option
              v-for="group in nomenclatureGroups"
              :key="group.id"
              :value="group.id"
            >{{group.name}}</option>
          </select>
        </div>
        <!-- разходни номенклатури -->
        <div v-if="id_nomenclature_group != null" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="selectservice">номенклатура :</label>
          <select
            id="selectservice"
            class="custom-input"
            v-model="serviceTemplate.id_nomenclature_expense"
            @change="changeNomenclature"
          >
            <option
              v-for="nomenclature in directionGroupNomenclatures"
              :key="nomenclature.id"
              :value="nomenclature.id"
            >{{nomenclature.name}}</option>
          </select>
        </div>

        <!-- сума -->
        <div v-if="selectedNomenclature" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="total_sum">сума:</label>
          <currency-input
            autocomplete="off"
            name="total_sum"
            id="total_sum"
            class="custom-input"
            v-model="serviceTemplate.total_sum"
            @input="syncSinglePrice"
            :currency="null"
          />
        </div>

        <div v-if="selectedNomenclature" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="for_month">за месец:</label>
          <datepicker
            id="for_month"
            autocomplete="off"
            spellcheck="false"
            placeholder="xx.xx.xxxx"
            wrapperClass="w-full"
            inputClass="custom-input"
            format="MMMM YYYY"
            :minimumView="'month'"
            :maximumView="'year'"
            :highlighted="highlighted"
            v-model="serviceTemplate.month"
          />
        </div>

        <div class="flex items-center justify-between">
          <label class="custom-label w-24 flex-shrink-0" for="note">бележка :</label>
          <input type="text" id="note" class="custom-input" v-model="serviceTemplate.note" />
        </div>
      </div>
    </template>

    <template #footer>
      <div class="flex items-center justify-end bg-white px-3 py-2 flex-shrink-0">
        <button
          @click="closeServiceComponent()"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-white text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2"
        >затвори</button>
        <button
          v-if="edit"
          @click="deleteService()"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-gray-500 text-white text-xss uppercase font-medium tracking-wide hover:bg-gray-600 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200 mr-2"
        >изтрий</button>
        <button
          v-if="!edit"
          @click="addService()"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >добави</button>
        <button
          v-if="edit"
          @click="updateService()"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >потвърди</button>
      </div>
    </template>
  </baseDialog>
</template>

<script>
import BaseDialog from "./BaseDialog.vue";
import Datepicker from "./Datepicker.vue";
import VueCurrencyInput from "vue-currency-input";
import { uuidMixin } from "./uuidMixin"
import { utilityMixin } from "./utilityMixin"
import { dateMixin } from "./dateMixin"
import mixins from "./mixins";

export default {
  name: "freeBuy",
  components: { BaseDialog, Datepicker, VueCurrencyInput },
  mixins: [
    uuidMixin,
    utilityMixin,
    dateMixin,
    mixins],
  props: {
    firms: {
      type: Array,
      required: true,
    },
    regions: {
      type: Array,
      required: true,
    },
    directions: {
      type: Array,
      required: true,
    },
    nomenclatures: {
      type: Array,
      required: true,
    },
    nomenclature_groups: {
      type: Array,
      required: true,
    },
    client_id_office_dds: null,
    vat_transfer: false,
    vat_transfer_nomenclature: null,
    vat_mode: false,
    date: "",
    service: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      edit: false,
      enableVatTransfer: false,
      defaultServiceTemplate: {
        firm: "",
        direction: "",
        id: 0,
        id_buy_doc: 0,
        id_direction: 0,
        id_firm: 0,
        id_nomenclature_expense: 0,
        nomenclature: "",
        id_object: 0,
        id_office: 0,
        id_order: 0,
        id_person: 0,
        id_salary_row: 0,
        is_dds: 0,
        measure: "бр.",
        month: "0000-00-00",
        note: "",
        object: "",
        paid_date: "",
        paid_sum: 0,
        quantity: 1,
        region: "",
        single_price: 0,
        total_sum: 0,
        updated_time: "0000-00-00 00:00:00",
        updated_user: 0,
        uuid: "",
      },
      serviceTemplate: {
        firm: "",
        direction: "",
        id: 0,
        id_buy_doc: 0,
        id_direction: 0,
        id_firm: 0,
        id_nomenclature_expense: 0,
        nomenclature: "",
        id_object: 0,
        id_office: 0,
        id_order: 0,
        id_person: 0,
        id_salary_row: 0,
        is_dds: 0,
        measure: "бр.",
        month: "0000-00-00",
        note: "",
        object: "",
        paid_date: "",
        paid_sum: 0,
        quantity: 1,
        region: "",
        single_price: 0,
        total_sum: 0,
        updated_time: "0000-00-00 00:00:00",
        updated_user: 0,
        uuid: "",
      },
      id_nomenclature_group: null,
    };
  },
  created() {
    this.prepareTemplate();
  },
  mounted() {
    if (this.edit && !this.directionGroupNomenclatures) {
      if (
        this.service.id_nomenclature_expense !==
        this.vat_transfer_nomenclature.id
      ) {
        this.closeServiceComponent();
        alert("ГРЕШКА: избрания ред не подлежи на редакция!");
      }
      if (
        this.service.id_nomenclature_expense ===
        this.vat_transfer_nomenclature.id
      ) {
        this.enableVatTransfer = true;
      }
    }
    if (this.vat_mode && !this.edit) {
      this.toggleVatTransfer();
    }
  },
  methods: {
    syncSinglePrice() {
      this.serviceTemplate.single_price = this.serviceTemplate.total_sum
    },
    allowChangeObject() {
      this.showChangeObjectDialog = true;
    },
    toggleVatTransfer() {
      this.enableVatTransfer = !this.enableVatTransfer;
      if (this.enableVatTransfer) {
        this.serviceTemplate = JSON.parse(
          JSON.stringify(this.defaultServiceTemplate)
        );
        this.serviceTemplate.id_nomenclature_expense = this.vat_transfer_nomenclature.id;
        this.serviceTemplate.id_office = this.client_id_office_dds;
        this.serviceTemplate.region = this.selectedRegion.region;
        this.serviceTemplate.id_firm = this.selectedRegion.id_firm;
        this.serviceTemplate.firm = this.selectedFirm.name;
      } else {
        this.serviceTemplate = JSON.parse(
          JSON.stringify(this.defaultServiceTemplate)
        );
      }
    },
    prepareTemplate() {
      if (
        Object.keys(this.service).length === 0 &&
        this.service.constructor === Object
      ) {
        this.serviceTemplate.uuid = this.genUuid();
        this.serviceTemplate.month = JSON.parse(JSON.stringify(this.date));
        this.defaultServiceTemplate.uuid = this.genUuid();
        this.defaultServiceTemplate.month = JSON.parse(
          JSON.stringify(this.date)
        );
      } else {
        this.edit = true;
        this.serviceTemplate = JSON.parse(JSON.stringify(this.service));
        this.id_nomenclature_group = this.selectedNomenclature.id_group;
      }
    },
    addService() {
      if (this.validateService()) {
        this.$emit(
          "addService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      } else {
        alert("Грешка: Некоректни данни!");
      }
    },
    updateService() {
      if (this.validateService()) {
        this.$emit(
          "updateService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      } else {
        alert("Грешка: Некоректни данни!");
      }
    },
    deleteService() {
      if (confirm("Потвърдете")) {
        this.$emit(
          "deleteService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      }
    },
    validateService() {
      let isValid = [];
      if (!this.serviceTemplate.id_firm) {
        isValid.push(false);
      } else if (!this.serviceTemplate.id_office) {
        isValid.push(false);
      } else if (this.serviceTemplate.id_nomenclature_expense === 0 || this.serviceTemplate.id_nomenclature_expense === null) {
        isValid.push(false);
      } else if (this.serviceTemplate.month == "") {
        isValid.push(false);
      }
      return isValid.includes(false) ? false : true;
    },
    closeServiceComponent() {
      this.$emit("closeFreeSale");
    },
    changeFirm() {
      this.serviceTemplate.firm = this.selectedFirm.name;
      this.resetRegionsAndServices();
    },
    resetRegionsAndServices() {
      this.serviceTemplate.id_office = 0;
      this.serviceTemplate.region = "";
      this.serviceTemplate.id_direction = 0;
      this.id_nomenclature_group = null;
      if (!this.serviceTemplate.id_nomenclature_expense) return;
      this.serviceTemplate.id_nomenclature_expense = null;
    },
    changeRegion() {
      if (!this.selectedRegion) {
        this.serviceTemplate.region = "";
      } else {
        this.serviceTemplate.region = this.selectedRegion.region;
      }
    },
    changeDirection() {
      this.id_nomenclature_group = null;
      this.serviceTemplate.direction = this.selectedDirection.name
      if (!this.serviceTemplate.id_nomenclature_expense) return;
      this.serviceTemplate.id_nomenclature_expense = null;
      this.serviceTemplate.nomenclature = "";
    },
    changeNomenclature() {
      this.serviceTemplate.nomenclature = this.selectedNomenclature ? this.selectedNomenclature.name : null
    },
    changeNomenclatureGroup() {
      this.serviceTemplate.id_nomenclature_expense = null;
    },
  },
  computed: {
    firmRegions() {
      if (this.serviceTemplate.id_firm) {
        let tmp = this.regions.filter(
          (region) => region.id_firm === this.serviceTemplate.id_firm
        );

        return tmp.length ? this.arrSortByPropName(tmp, "region") : null;
      } else {
        return null;
      }
    },
    regionDirections() {
      if (this.serviceTemplate.id_office) {
        let tmp = this.directions.filter(
          (direction) => direction.id_office === this.serviceTemplate.id_office
        );
        return tmp.length ? this.arrSortByPropName(tmp, "name") : null;
      } else {
        return null;
      }
    },
    nomenclatureGroups() {
      if (this.serviceTemplate.id_direction) {
        let nomenclatureGroupsIds = [
          ...new Set(this.directionNomenclatures.map(({ id_group }) => id_group)),
        ];
        let tmp = this.nomenclature_groups.filter((group) =>
          nomenclatureGroupsIds.includes(group.id)
        );
        return tmp.length ? this.arrSortByPropName(tmp, "name") : null;
      }
      return null;
    },
    directionGroupNomenclatures() {
      if (this.serviceTemplate.id_direction && this.id_nomenclature_group != null) {
        let tmp = this.directionNomenclatures.filter(
          (el) => el.id_group === this.id_nomenclature_group
        );
        return tmp.length ? tmp : null;
      }
      return null;
    },
    directionNomenclatures() {
      if (this.serviceTemplate.id_direction) {
        let tmp = this.nomenclatures.filter((nomenclature) =>
          nomenclature.vat_transfer != 1
        );
        return tmp.length ? this.arrSortByPropName(tmp, "name") : null;
      }
      return null;
    },
    selectedFirm() {
      if (this.serviceTemplate.id_firm) {
        return this.firms.find(
          (firm) => firm.id_firm === this.serviceTemplate.id_firm
        );
      } else {
        return null;
      }
    },
    selectedRegion() {
      return this.serviceTemplate.id_office
        ? this.regions.find(
            (region) => region.id_office === this.serviceTemplate.id_office
          )
        : null;
    },
    selectedDirection() {
      if (this.regionDirections && this.serviceTemplate.id_direction) {
        return this.regionDirections.find(
          (direction) => direction.id === this.serviceTemplate.id_direction
        );
      }
      return null;
    },
    selectedNomenclature() {
      return this.serviceTemplate.id_nomenclature_expense
        ? this.nomenclatures.find(
            (nomenclature) =>
              nomenclature.id === this.serviceTemplate.id_nomenclature_expense
          )
        : null;
    },
  }
};
</script>

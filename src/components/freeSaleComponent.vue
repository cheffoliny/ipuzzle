<template>
  <baseDialog static>
    <template #header>
      <div class="flex items-center  p-3 rounded-t-sm bg-indigo-500 flex-shrink-0">
        <div
          class="text-xs tracking-wide text-white font-medium truncate uppercase"
        >свободна продажба</div>
        <button
          @click="closeServiceComponent"
          class="ml-auto text-sm focus:outline-none focus:shadow-outline"
        >
          <i class="fal fa-times fa-fw"></i>
        </button>
      </div>
    </template>

    <template #body>
      <div class="flex flex-col bg-white overflow-y-auto p-3 pb-0">
        <div class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="selectfirm">фирма:</label>
          <select
            id="selectfirm"
            class="custom-input"
            v-model="serviceTemplate.id_firm"
            @change="changeFirm"
          >
            <option v-for="firm in firms" :key="firm.id_firm" :value="firm.id_firm">{{firm.name}}</option>
          </select>
        </div>

        <div v-if="serviceTemplate.id_firm" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="selectregion">регион:</label>
          <select
            id="selectregion"
            class="custom-input"
            v-model="serviceTemplate.id_office"
            @change="changeRegion"
          >
            <option
              v-for="region in selectedRegions"
              :key="region.id_office"
              :value="region.id_office"
            >{{region.region}}</option>
          </select>
        </div>

        <div v-if="serviceTemplate.id_firm" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="selectservice">услуга:</label>
          <select
            id="selectservice"
            class="custom-input"
            v-model="serviceTemplate.id_service"
            @change="changeService"
          >
            <option
              v-for="service in selectedServices"
              :key="service.id_service"
              :value="service.id_service"
            >{{service.name}}</option>
          </select>
        </div>

        <div v-if="serviceTemplate.id_service" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="service_name">наименование:</label>
          <input
            type="text"
            name="service_name"
            id="service_name"
            class="custom-input"
            v-model="serviceTemplate.object_name"
          />
        </div>

        <div v-if="selectedService" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="single_price">ед. цена:</label>
          <currency-input
            autocomplete="off"
            name="single_price"
            id="single_price"
            class="custom-input"
            v-model="serviceTemplate.single_price"
            :currency="null"
            :value-range="sumMinMax"
            :allow-negative="allowNegativeServiceSum"
            @blur="fixNullValue()"
            :precision="4"
          />
        </div>

        <div v-if="selectedService" class="flex items-center justify-between mb-2">
          <label class="custom-label w-24 flex-shrink-0" for="quantity">{{serviceTemplate.measure}}:</label>

          <currency-input
            autocomplete="off"
            name="quantity"
            id="quantity"
            class="custom-input"
            v-model="serviceTemplate.quantity"
            :currency="null"
            :allow-negative="false"
            @change="fixQuantity()"
          />
        </div>

        <div v-if="selectedService" class="flex items-center justify-between">
          <label class="custom-label w-24 flex-shrink-0" for="from_date">дата:</label>
          <datepicker
            id="from_date"
            autocomplete="off"
            spellcheck="false"
            placeholder="xx.xx.xxxx"
            wrapperClass="w-full"
            inputClass="custom-input"
            :mondayFirst="true"
            :fullMonthName="true"
            :highlighted="highlighted"
            format="DD.MM.YYYY"
            v-model="serviceTemplate.month"
          />
        </div>

        <hr class="my-3" />

        <div v-if="selectedService" class="flex items-center justify-end gap-2">
          <div class="flex flex-col items-end border p-1 px-2">
            <span
              class="w-full text-right text-black text-smplus font-medium"
            >{{currentSum.total_sum | price}} лв.</span>
            <span class="w-full text-right text-orange-500 text-xssplus">без ддс</span>
          </div>
          <div class="flex flex-col items-end border p-1 px-2">
            <span
              class="w-full text-right text-black text-smplus font-medium"
            >{{currentSum.total_sum_with_dds | price}} лв.</span>
            <span class="w-full text-right text-orange-500 text-xssplus">с ддс</span>
          </div>
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
import { dateMixin } from "./dateMixin"
import { utilityMixin } from "./utilityMixin"

export default {
  name: "freeSale",
  components: { BaseDialog, Datepicker, VueCurrencyInput },
  mixins: [
    uuidMixin,
    dateMixin,
    utilityMixin
  ],
  props: {
    edit: false,
    regions: {
      type: Array,
      required: true
    },
    firms: {
      type: Array,
      required: true
    },
    services: {
      type: Array,
      required: true
    },
    date: "",
    user_office_id:{
      type: Number,
      required: true
    },
    service: {
      type: Object,
      default: () => ({})
    },
    current_document: {
      type: Object,
      default: () => ({})
    },
    relative_document: {
      type: Object,
      default: () => ({})
    },
    relative_credit_maxValue: {
      type: Number,
      default: 0,
      required: false
    }
  },
  data() {
    return {
      serviceTemplate: {},
      userDefaults:{
        init: false,
        firm: "",
        id_firm: null,
        region: "",
        id_office: null,
        service_name: "",
        id_service: null,
      }
    };
  },
  created() {
    this.setUserDefaults()
  },
  methods: {
    prepareTemplate() {
      if (
        Object.keys(this.service).length === 0 &&
        this.service.constructor === Object
      ) {
        this.serviceTemplate = {
          uuid: this.genUuid(),
          firm: this.userDefaults.firm,
          for_payment: true,
          id_duty: 0,
          id_firm: this.userDefaults.id_firm,
          id_object: 0,
          id_office: this.userDefaults.id_office,
          id_service: null,
          measure: "",
          month: JSON.parse(JSON.stringify(this.date)),
          object_name: "Свободна продажба",
          payed: 0,
          quantity: 0,
          region: this.userDefaults.region,
          service_name: "",
          single_price: 0,
          total_sum: 0,
          total_sum_with_dds: 0,
          type: "free",
          vat: 0
        };
      } else {
        this.serviceTemplate = JSON.parse(JSON.stringify(this.service));
      }
    },
    setUserDefaults() {
      if(!this.firms.length || !this.regions.length) return
      const region = this.regions.find(region => region.id_office === this.user_office_id);
      this.userDefaults.id_firm = region ? region.id_firm : null
      this.userDefaults.firm = region ? region.firm : ""
      this.userDefaults.id_office = region ? region.id_office : null
      this.userDefaults.region = region ? region.region : ""
      this.prepareTemplate()
    },
    addService() {
      this.fixTotals();
      if (this.validateService()) {
        this.$emit(
          "addService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      } else {
        alert("Грешка: Некоректни данни за услуга");
      }
    },
    updateService() {
      this.fixTotals();
      if (this.validateService()) {
        this.$emit(
          "updateService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      } else {
        alert("Грешка: Некоректни данни за услуга");
      }
    },
    deleteService() {
      if (confirm("сигурни ли сте че искате да изтриете услугата ?")) {
        this.$emit(
          "deleteService",
          JSON.parse(JSON.stringify(this.serviceTemplate))
        );
      }
    },
    validateService() {
      let isValid = [];
      if (this.serviceTemplate.firm == "") {
        isValid.push(false);
      } else if (this.serviceTemplate.id_firm == null) {
        isValid.push(false);
      } else if (this.serviceTemplate.id_office == null) {
        isValid.push(false);
      } else if (this.serviceTemplate.id_service == null) {
        isValid.push(false);
      } else if (this.serviceTemplate.measure == "") {
        isValid.push(false);
      } else if (this.serviceTemplate.month == "") {
        isValid.push(false);
      } else if (this.serviceTemplate.object_name == "") {
        isValid.push(false);
      } else if (this.serviceTemplate.quantity <= 0) {
        isValid.push(false);
      } else if (this.serviceTemplate.service_name == "") {
        isValid.push(false);
      } else if (this.serviceTemplate.vat == 0) {
        isValid.push(false);
      } else if (this.current_document.doc_type == 'kreditno izvestie') {
         if(this.currentSum.total_sum > this.sumMinMax.max || this.currentSum.total_sum > this.relative_document.total_sum) {
           isValid.push(false);
         }
      }
      return isValid.includes(false) ? false : true;
    },
    fixNullValue() {
      if(this.serviceTemplate.single_price !== null) return
      this.serviceTemplate.single_price = 0
    },
    fixQuantity() {
      if(this.serviceTemplate.quantity !== null) return
      this.serviceTemplate.quantity = 1
    },
    fixTotals() {
      const vat = (this.serviceTemplate.vat / 100 + 1)
      this.serviceTemplate.total_sum = this.serviceTemplate.single_price * this.serviceTemplate.quantity
      this.serviceTemplate.total_sum_with_dds = parseFloat((this.serviceTemplate.total_sum *  vat).toFixed(2))  
    },
    closeServiceComponent() {
      this.$emit("closeFreeSale");
    },
    resetRegionsAndServices() {
      this.serviceTemplate.id_office = null;
      this.serviceTemplate.id_service = null;
      (this.serviceTemplate.measure = ""),
        (this.serviceTemplate.month = JSON.parse(JSON.stringify(this.date))),
        (this.serviceTemplate.object_name = "Свободна продажба"),
        (this.serviceTemplate.quantity = 0),
        (this.serviceTemplate.region = ""),
        (this.serviceTemplate.service_name = ""),
        (this.serviceTemplate.single_price = 0),
        (this.serviceTemplate.total_sum = 0),
        (this.serviceTemplate.total_sum_with_dds = 0),
        (this.serviceTemplate.vat = 0);
    },
    changeFirm(e) {
      if (!this.edit) return;
      this.serviceTemplate.id_firm = parseInt(e.target.value);
      this.resetRegionsAndServices();
    },
    changeRegion(e) {
      //console.log(e.target.value);
    },
    changeService() {
    }
  },
  computed: {
    allowNegativeServiceSum() {
      if(this.current_document.doc_type === 'debitno izvestie') return false
      return true
    },
    sumMinMax() {
      if(this.current_document.doc_type === 'kreditno izvestie') {
        const cMax = this.edit ? this.relative_credit_maxValue + this.service.total_sum : this.relative_credit_maxValue
        return {
          min:0,
          max: cMax
        }
      }
    },
    selectedRegions() {
      return !this.serviceTemplate.id_firm
        ? null
        : this.regions.filter(
            region => region.id_firm === this.serviceTemplate.id_firm
          );
    },
    selectedServices() {
      let temp = !this.serviceTemplate.id_firm
        ? null
        : this.services.filter(
            service => service.id_firm === this.serviceTemplate.id_firm
          );
      if(temp) {
        this.arrSortByPropName(temp, 'name')
      }
      return temp;
    },
    selectedFirm() {
      return this.serviceTemplate.id_firm
        ? this.firms.find(firm => firm.id_firm === this.serviceTemplate.id_firm)
        : null;
    },
    selectedRegion() {
      return this.serviceTemplate.id_office
        ? this.regions.find(
            region => region.id_office === this.serviceTemplate.id_office
          )
        : null;
    },
    selectedService() {
      return this.serviceTemplate.id_service
        ? this.services.find(
            service => service.id_service === this.serviceTemplate.id_service
          )
        : null;
    },
    currentSum() {
      const vat = (this.serviceTemplate.vat / 100 + 1);
      let total_sum = 0;
      let total_sum_with_dds = 0;

      total_sum = this.serviceTemplate.single_price * this.serviceTemplate.quantity
      total_sum_with_dds = total_sum * vat
      return {
        total_sum : total_sum,
        total_sum_with_dds : total_sum_with_dds
      }
    }
  },
  watch: {
    selectedFirm() {
      if (!this.selectedFirm && !this.edit) return;
    
      this.serviceTemplate.firm = this.selectedFirm.name;
      
      if (!this.edit) {
        if(this.userDefaults.init) {
          this.resetRegionsAndServices();
        }
      }
      this.userDefaults.init = true
    },
    selectedRegion() {
      if (!this.selectedFirm && !this.edit) return;
      if (!this.selectedRegion) return;
      this.serviceTemplate.region = this.selectedRegion.region;
    },
    selectedService() {
      if (!this.selectedService) return;
        this.serviceTemplate.service_name = this.selectedService.name;
      if (this.edit) {
        this.serviceTemplate.object_name = this.service.object_name;
        this.serviceTemplate.service_name = this.service.service_name;
        this.serviceTemplate.single_price = this.service.single_price;
        this.serviceTemplate.quantity = this.service.quantity;
      }
      //this.serviceTemplate.region = this.selectedRegion.region;
      if (!this.edit) {
        this.serviceTemplate.single_price = this.selectedService.price;
      }
      if (!this.edit) {
        this.serviceTemplate.quantity = this.selectedService.quantity;
      }
      this.serviceTemplate.measure = this.selectedService.measure;
      this.serviceTemplate.vat = this.selectedService.vat;
    }
  }
};
</script>

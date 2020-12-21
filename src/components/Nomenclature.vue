<template>
  <baseDialog static>
    <template #header>
      <div class="flex items-center  p-3 rounded-t-sm bg-indigo-500 flex-shrink-0">
        <div class="text-xs tracking-wide text-white font-medium truncate uppercase">направление / услуга</div>
        <button @click="close()" class="ml-auto text-sm focus:outline-none focus:shadow-outline">
          <i class="fal fa-times fa-fw"></i>
        </button>
      </div>
    </template>

    <template #body>
      <div class="flex flex-col bg-white overflow-y-auto p-3">

        <div class="flex items-center justify-between mb-2">
          <div class="custom-label w-24 flex-shrink-0">фирма:</div>
          <div class="custom-input">{{service.firm}}</div>
        </div>

        <div class="flex items-center justify-between mb-2">
          <div class="custom-label w-24 flex-shrink-0">регион:</div>
          <div class="custom-input">{{service.region}}</div>
        </div>

        <div class="flex items-center justify-between mb-2">
          <div class="custom-label w-24 flex-shrink-0">услуга:</div>
          <div class="custom-input">{{selectedService}}</div>
        </div>

        <div class="flex items-center justify-between mb-2">
          <div class="custom-label w-24 flex-shrink-0">ддс:</div>
          <div class="custom-input">{{service.vat}} %</div>
        </div>

        <div :class="{'mb-2': service.for_smartsot}" class="flex items-center justify-between">
          <div class="custom-label w-24 flex-shrink-0">мерна единица:</div>
          <div class="custom-input">{{service.measure}}</div>
        </div>

        <div v-if="service.for_smartsot" class="flex items-center justify-between">
          <div class="custom-label w-24 flex-shrink-0">смарт сот:</div>
          <div class="custom-input">да</div>
        </div>
        
      </div>
    </template>

    <template #footer>
      <div class="flex items-center justify-end bg-white px-3 pb-2 flex-shrink-0">
        <button
          @click="close()"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-white text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >затвори</button>
      </div>
    </template>
  </baseDialog>
</template>

<script>
import BaseDialog from "./BaseDialog.vue";


export default {
  name: "Nomenclature",
  components: { BaseDialog},
  props: {
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
    service: {
      type: Object,
      default: () => ({})
    }
  },
  methods: {
      close() {
          this.$emit('closeNomenclature')
      }
  },
  computed: {
      selectedService() {
          return this.services.find(service => service.id_service === this.service.id_service).name
      }
  }
  
};
</script>

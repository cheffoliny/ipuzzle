window.axios = require('axios');
window.Vue = require('vue');
window.moment = require('moment')
import VueTippy, { TippyComponent } from "vue-tippy";

import 'moment/locale/bg';

Vue.use(VueTippy, {
  directive: "tippy", // => v-tippy
  flipDuration: 0,
  popperOptions: {
    modifiers: {
      preventOverflow: {
        enabled: true
      }
    }
  }
});

//Vue.component("tippy", TippyComponent);

Vue.component('SaleDoc', require('./components/SaleNew.vue').default);
Vue.component('BuyDoc', require('./components/BuyNew.vue').default);
new Vue({
  el: '#app',
});

<template>
  <baseDialog id="orders" static :loading="loading">
    <template #header>
      <div class="flex items-center p-3 rounded-t-sm bg-indigo-500 flex-shrink-0">
        <div class="text-xs tracking-wide text-white font-medium truncate uppercase">ордери</div>
        <button
          @click="closePaymentForm"
          class="ml-auto text-sm text-white focus:outline-none focus:shadow-outline"
        >
          <i class="fal fa-times fa-fw"></i>
        </button>
      </div>
    </template>
    <template #body>
      <div class="flex flex-col bg-white overflow-y-auto">
        <ul class="list-reset">
          <template v-if="orders.length">
            <li
              v-for="order in orders"
              :key="order.id"
              class="h-12 flex items-center text-smplus text-gray-600 truncate px-2 border-b border-dashed border-gray-300"
              :class="order.order_status === 'active' ? 'text-gray-700' : (order.order_status === 'opposite' ? 'text-blue-500' : 'text-pink-500')"
            >
              <button
                @click="openOrder(order.id)"
                :class="order.order_status === 'active' ? 'bg-gray-600 hover:bg-gray-700' : (order.order_status === 'opposite' ? 'bg-blue-500 hover:bg-blue-600' : 'bg-pink-500 hover:bg-pink-600')"
                class="w-20 text-smplus text-center mr-2 text-white rounded-full px-2 py-1 focus:outline-none focus:shadow-outline"
              >{{order.num}}</button>

              <div class="w-20 mr-2 text-center">{{toDDMMYYYY(order.order_date)}} г.</div>

              <div
                :title="order.bank_account"
                class="truncate mr-2 text-left"
              >{{order.bank_account}}</div>

              <div :title="order.order_sum" class="w-24 ml-auto text-right">{{order.order_sum.toFixed(2)}} лв.</div>

              <i :title="order.user" 
              v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
              class="text-center fad fa-user mx-2 fa-fw text-gray-700"></i>

              <button
                v-if="order.order_status === 'active'"
                @click="cancelOrder(order)"
                class="w-24 h-8 p-2 shadow-custom select-none rounded-sm bg-white-400 text-gray-700 text-xss uppercase font-medium tracking-wide hover:bg-pink-600 hover:text-white focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
              >анулирай</button>
              <div
                v-else-if="order.order_status === 'opposite'"
                class="w-24 h-8 p-2 text-xss uppercase font-medium tracking-wide text-center"
              >насрещен</div>
              <div
                v-else
                class="w-24 h-8 p-2 text-xss uppercase font-medium tracking-wide text-center"
              >анулиран</div>
            </li>
          </template>
        </ul>
      </div>
    </template>
    <template #footer>
      <div
        v-if="paid_sum !== doc_sum && doc_status !== 'canceled'"
        class="flex flex-shrink-0 items-center bg-white px-3 py-2"
      >
        <label class="custom-label" for="bank_account_type">плащане:</label>
        <div class="flex items-center flex-shrink-0 w-18 mx-2">
          <select
            id="bank_account_type"
            class="custom-input"
            :disabled="doc_sum === paid_sum"
            v-model="bank_account_type"
            @change="changeBankAccountType"
          >
            <option value="cash">в брой</option>
            <option value="bank">по банка</option>
          </select>
        </div>
        <label class="custom-label" for="bank_account_id">сметка:</label>
        <div class="flex items-center mx-2">
          <select 
            ref="bank_account_id"
            id="bank_account_id"
            class="custom-input"
            :disabled="!bank_account_type"
            v-model="bank_account_id"
          >
            <option
              v-for="account in bankAccounts"
              :value="account.id"
              :key="account.id"
              v-text="account.name"
            ></option>
          </select>
        </div>
        <label class="custom-label ml-auto mr-2" for="order_sum">сума:</label>
        <div class="flex w-24 flex-shrink-0 items-center mr-2">
          <currency-input
            autocomplete="off"
            v-if="bank_account_id"
            :disabled="!bank_account_type && !bank_account_id"
            name="order_sum"
            id="order_sum"
            class="custom-input text-right"
            v-model="order_sum"
            :currency="null"
            :allow-negative="false"
            :value-range="orderValueMinMax"
          />
          <div v-else class="custom-input"></div>
        </div>

        <button
          :disabled="!validateOrder()"
          @click="addOrder"
          :class="{'cursor-not-allowed': !validateOrder()}"
          class="w-24 h-8 p-2 shadow-custom rounded-sm bg-indigo-400 text-white text-xss uppercase font-medium tracking-wide hover:bg-indigo-500 focus:outline-none focus:shadow-outline transition ease-in-out duration-200"
        >добави</button>
      </div>
    </template>
  </baseDialog>
</template>

<script>
import BaseDialog from "./BaseDialog.vue";
import VueCurrencyInput from "vue-currency-input";
import { dateMixin } from "./dateMixin"
export default {
  name: "PaymentForm",
  components: { BaseDialog, VueCurrencyInput },
  mixins: [
    dateMixin,
  ],
  props: {
    mode: {
      type: String,
      default: "sale",
    },
    doc_id: null,
    doc_type: null,
    doc_status: null,
    doc_sum: null,
    paid_sum: null,
    orders: null,
    bank_accounts: null,
    id_cash_default: null,
  },
  data() {
    return {
      bank_account_type: null,
      bank_account_id: null,
      order_sum: 0,
      loading: false,
    };
  },
  created() {
    this.initPaymentForm()
  },
  methods: {
    initPaymentForm() {
      this.paid_sum == 0
        ? (this.order_sum = this.doc_sum)
        : (this.order_sum = this.doc_sum - this.paid_sum);

      if (this.id_cash_default?.id > 0) {
        this.bank_account_type = this.id_cash_default.type
        this.bank_account_id = this.id_cash_default.id
      } else {
        this.setBankAccount('bank')
      }
    },
    setBankAccount(type) {
      this.bank_account_type = type;
      this.bank_account_id = this.bankAccounts.length ? this.bankAccounts[0].id : null
    },
    changeBankAccountType() {
      this.setBankAccount(this.bank_account_type)
      this.$refs.bank_account_id.focus()
    },
    addOrder() {
      if (this.validateOrder()) {
        this.loading = true;
        
        if(this.mode === 'buy' && this.doc_type === 'kreditno izvestie') {
          this.order_sum *= -1
        }

        axios
          .post(`${this.endPoint}?action=make_order`, {
            doc_id: this.doc_id,
            bank_account_id: this.bank_account_id,
            order_sum: this.order_sum,
          })
          .then((response) => {
            this.loading = false;
            this.$emit("reload");
          })
          .catch((error) => {
            this.loading = false;
            error?.response?.data?.error ? alert(`Грешка: ${error.response.data.error} !`) : alert(error)
          });
      } else {
        alert("Грешка при валидация на данните");
      }
    },
    cancelOrder(order) {
      if (confirm(`Анулиране ордер ${order.num} на стойност ${order.order_sum} лв.?`)) {
        this.loading = true;
        axios
          .post(`${this.endPoint}?action=annulment_order`, {
            id: order.id,
          })
          .then((response) => {
            this.loading = false;
            this.$emit("reload");
          })
          .catch((error) => {
            this.loading = false;
            error?.response?.data?.error ? alert(`Грешка: ${error.response.data.error} !`) : alert(error)
          });
      }
    },
    openOrder(id) {
      dialog_win(`order_inventory&id=${id}`, 800, 600, 1, "order_inventory");
    },
    closePaymentForm() {
      this.$emit("closePaymentForm");
    },
    validateOrder() {
      const isValid = [];
      if (!this.bank_account_type) {
        isValid.push(false);
      }

      if (!this.bank_account_id) {
        isValid.push(false);
      }

      if (!this.mode === "buy") {
        if (this.order_sum > this.doc_sum) {
          isValid.push(false);
        }
      }

      if (this.mode === "sale") {
        if (this.order_sum > this.doc_sum) {
          isValid.push(false);
        }
      }

      if (this.paid_sum === this.doc_sum) {
        isValid.push(false);
      }

      if(this.order_sum === null) {
        isValid.push(false);
      }

      return isValid.includes(false) ? false : true;
    },
  },
  computed: {
    orderValueMinMax() {
      return {
        min:0,
        max: this.doc_sum - this.paid_sum
      }
    },
    bankAccounts() {
      return this.bank_account_type
        ? this.bank_accounts.filter(bank_account => bank_account.type === this.bank_account_type)
        : null;
    },
    endPoint() {
      return this.mode === "sale"
        ? "api/api_sale_controller.php"
        : "api/api_buy_controller.php";
    },
  },
  
};
</script>
<template>
  <baseDialog id="reldocs" static :loading="loading">
    <template #header>
      <div class="flex items-center p-3 rounded-t-sm bg-indigo-500 flex-shrink-0">
        <div
          class="text-xs tracking-wide text-white font-medium truncate uppercase"
        >Релативни документи</div>
        <button
          @click="closeRelativeDocuemntsList()"
          class="ml-auto text-sm text-white focus:outline-none focus:shadow-outline"
        >
          <i class="fal fa-times fa-fw"></i>
        </button>
      </div>
    </template>

    <template #body>
      <div class="flex flex-col bg-white overflow-y-auto">
        <ul class="list-reset">
          <template v-if="relative_documents.length">
            <li
              v-for="document in relative_documents"
              :key="document.id"
              class="flex items-center px-3 py-1 space-x-2 border-b border-dashed border-gray-300 overflow-auto"
            >
              <div class="flex flex-shrink-0 items-center justify-center w-8 h-8 bg-white rounded-sm text-indigo-400">
                <i :class="getDocType(document.doc_type) == 'Дебитно' ? 'fa-layer-plus' : getDocType(document.doc_type) == 'Кредитно' ? 'fa-layer-minus' : 'fa-file-invoice' "
                class="fad fa-lg fa-fw"></i>
              </div>

              <div class="flex flex-col w-24">
                <div title="вид" class="primary-info font-medium">{{getDocType(document.doc_type)}}</div>
                <span title="статус" class="secondary-info">{{getDocStatus(document)}}</span>
              </div>
              
              <div class="flex flex-col w-20">
                <button 
                  title="номер"
                  class="primary-info focus:outline-none focus:shadow-outline text-center border rounded-sm hover:bg-gray-100"
                  @click="openRelativeDocument(document.id, mode)"
                >{{document.doc_num}}</button>
                <div title="дата" class="secondary-info text-right">{{toDDMMYYYY(document.doc_date)}} г.</div>
              </div>
              <div class="flex flex-col w-20">
                <span title="сума" class="primary-info text-right">
                   {{document.total_sum | price}}
                </span>
                <span class="secondary-info text-right">лв.</span>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </template>

    <template #footer></template>
  </baseDialog>
</template>

<script>
import BaseDialog from "./BaseDialog.vue";
import { dateMixin } from "./dateMixin"
import { utilityMixin } from "./utilityMixin"
import { openRelDocMixin } from "./openRelDocMixin"
export default {
  name: "RelativeDocumentsList",
  components: { BaseDialog },
  mixins: [
    dateMixin,
    utilityMixin,
    openRelDocMixin
  ],
  props: {
    mode: {
      type: String,
      default: 'sale'
    },
    id_sale_doc: null,
    exclude_id: 0,
  },
  data() {
    return {
      relative_documents: [],
      loaded: false,
      loading: false
    };
  },
  methods: {
    getDocType(doc_type) {
      if (doc_type === "kreditno izvestie") {
        return "Кредитно";
      } else if (doc_type === "debitno izvestie") {
        return "Дебитно";
      } else return "Фактура";
    },
    getDocStatus(document){
      if(document.doc_status === 'canceled') {
        return 'анулиран'
      }
      if(document.total_sum === document.orders_sum) {
        return 'платен'
      }
      if(document.orders_sum > 0 && document.orders_sum < document.total_sum) {
        return 'частично погасен'
      }
      return 'непогасен'
    },
    closeRelativeDocuemntsList() {
      this.$emit("closeRelativeDocuemntsList");
    },
    getRelations() {
      this.loading = true;
      const api = this.mode === 'sale'
       ? `api/api_sale_controller.php?action=get_relations&id=${this.id_sale_doc}`
       : `api/api_buy_controller.php?action=get_relations&id=${this.id_sale_doc}`
      axios
        .get(api)
        .then(({ data } = response.data) => {
          this.relative_documents = data.relations.filter(doc => doc.id !== this.exclude_id);
          this.loaded = true;
          this.loading = false;
        })
        .catch((error) => {
          this.loading = false;
          error?.response?.data?.error
            ? alert(`Грешка: ${error.response.data.error} !`)
            : alert(error.message);
        });
    }
  },
  created() {
    this.getRelations();
  }
};
</script>
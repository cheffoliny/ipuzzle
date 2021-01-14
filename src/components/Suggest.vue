<template>
  <div class="flex items-center relative col-start-2 col-end-5 custom-input">
    <input
      :id="id"
      class="w-full focus:outline-none"
      ref="input"
      type="text"
      autocomplete="off"
      spellcheck="false"
      :name="name"
      :tabindex="1"
      :value="value"
      :error="error ? error : false"
      @blur="onBlur()"
      @focus="inputFocus=true"
      @input="updateValue($event.target.value)"
      @keypress="fetchSuggestData($event)"
      @keyup.delete="fetchSuggestData($event)"
      @keydown.esc="suggestClearData()"
      @keydown.enter.prevent="findAndSet()"
      @keydown.up="suggestMarkPrev()"
      @keydown.down="suggestMarkNext()"
      @keydown.tab.prevent="suggestMarkNext()"
    />
    <button
      :disabled="!result_id"
      class="ml-2 focus:outline-none focus:shadow-outline"
      @click="openClient()"
      title="Отвори картона на клиента"
      v-tippy="{ trigger : 'mouseenter', placement : 'bottom',arrow : true}"
    >
      <span v-if="performing_query">
        <i class="fad fa-circle-notch fa-spin fa-fw text-blue-500"></i>
      </span>
      <span v-else-if="!performing_query && result_id">
        <i class="fad fa-external-link fa-fw"></i>
      </span>
      <span v-else>
        <i class="fad fa-search fa-fw"></i>
      </span>
    </button>

    <div
      id="ssres"
      v-show="results"
      :class="results ? 'st-group st-group-show' : 'st-group tralala' "
    >
      <div
        v-for="(object, index) in results"
        class="result-item"
        :class="highlightCurrentObject(index)"
        :tabindex="index+1"
        :id="index"
        @click="suggestSelectObject(object)"
        @keydown.enter.prevent="suggestSelectObject(object)"
        @keydown.esc="suggestClearData()"
        :key="index"
      >
        {{object.name}}
        <div class="distinct_tag truncate">
          <span v-if="object.invoice_address" class="text-gray-600 tracking-wide">адрес:</span>
          {{ object.invoice_address }}
        </div>
        <div class="distinct_tag truncate" >
          <span v-if="object.invoice_ein" class="text-gray-600 tracking-wide">булстат:</span>
          {{ object.invoice_ein }}
        </div>
        <div class="distinct_tag">
          <span class="text-gray-600 tracking-wide">клиентски номер:</span>
          {{ object.id_wf }}
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import debounce from "lodash/debounce";

export default {
  name: "InputSuggest",

  props: {
    id: { type: String },
    name: { type: String },
    value: { required: true },
    api: { required: true, type: String },
    error: {},
    autofocus: false,
    params: {
      type: Object,
      default() {
        return {};
      },
    },
    result_id: "",
  },
  data() {
    return {
      selected_result: "",
      results: [],
      result_index: "",
      inputFocus: false,
      performing_query: false,
      old_query_term: "",
    };
  },
  methods: {
    openClient() {
      dialogClientInfo(this.result_id);
    },
    findAndSet() {
      if (!this.results.length) return;
      if (this.results && this.results.length > 0 && this.result_index >= 0) {
        for (let index in this.results) {
          if (index == this.result_index) {
            this.updateValue(this.results[index].name);
            this.$emit("update:result_id", this.results[index].id);
            this.$emit("select-client", this.results[0]);
            this.selected_result = this.results[index].id;
            this.results = [];
            this.old_query_term = "";
            this.result_index = "";
            this.$refs.input.blur();
          }
        }
      }
      if (this.results && this.results.length > 0 && !this.result_index) {
        this.updateValue(this.results[0].name);
        this.$emit("update:result_id", this.results[0].id);
        this.selected_result = this.results[0].id;
        this.$emit("select-client", this.results[0]);
        this.results = [];
        this.old_query_term = "";
        this.result_index = "";
        this.$refs.input.blur();
      }
    },
    highlightCurrentObject(x) {
      let css_cls = "";
      x === this.result_index ? (css_cls = "key-marked") : "";
      return css_cls;
    },
    onBlur() {
      //console.log('blur')
      if (
        this.selected_result !== "" &&
        typeof this.selected_result !== "undefined"
      ) {
        //console.log('sr not empty return')
        this.inputFocus = false;
        return;
      } else if (!this.selected_result) {
        //console.log('sr empty clear')
        !this.results.length
          ? (this.updateValue(""), this.$emit("hide"))
          : false;
        this.old_query_term ? (this.old_query_term = "") : false;
        this.inputFocus = false;
      }
    },
    updateValue(value) {
      this.$emit("input", value);
      this.clearErrors();
    },
    fetchSuggestData: debounce(function ($event) {
      if ($event.key == "Enter") {
        return false;
      }
      if (this.selected_result || this.result_index) {
        this.selected_result = "";
        this.result_index = "";
      }

      if (this.value === "") {
        this.suggestClearData();
        return;
      } else if (!this.searchTermChanged) {
        return;
      } else {
        this.performing_query = true;
        axios
          .get(`${this.api}&value=${this.value}`)
          .then((response) => {
            this.results = response.data;
            if(this.results.length) {
              this.results.forEach(result => {
                this.adjustSomeProps(result)
              });
            }
            this.old_query_term = this.value.trim();
            this.performing_query = false;
          })
          .catch((error) => {
            this.performing_query = false;
            error?.response?.data?.error
              ? alert(`Грешка: ${error.response.data.error} !`)
              : alert(error);
          });
      }
    }, 450),
    suggestMarkNext() {
      if (this.results.length > 0 && this.result_index === "") {
        this.result_index = 0;
      } else if (
        this.results.length > 0 &&
        this.result_index < this.results.length
      ) {
        let real_i = this.results.length;
        real_i--;
        if (this.result_index == real_i) {
          this.result_index = 0;
          document.getElementById(this.result_index).scrollIntoView();
          return;
        } else {
          this.result_index++;
          document.getElementById(this.result_index).scrollIntoView();
        }
      }
    },
    suggestMarkPrev() {
      if (
        this.results.length > 0 &&
        this.result_index !== "" &&
        this.result_index > 0
      ) {
        this.result_index--;

        document.getElementById(this.result_index).scrollIntoView();
      } else if (this.results.length > 0) {
        let real_i = this.results.length;
        real_i--;
        this.result_index = real_i;
        document.getElementById(this.result_index).scrollIntoView();
      }
    },
    suggestSelectObject(selected_object) {
      this.selected_result = selected_object.id;
      this.$emit("input", selected_object.name);
      this.$emit("update:result_id", selected_object.id);
      this.$emit("select-client", selected_object);
      this.results = [];
      this.result_index = "";
    },
    // experiments browse current object testing stackoverflow ECMAScript 2016 fancy object prop function loop
    suggestBrowseCurrentObject(obj) {
      console.log("browse current obj");
      let vm = this;
      Object.keys(obj).map((e) => {
        console.log(`key = ${e}  value = ${obj[e]}`);
      });
    },
    suggestClearData() {
      this.results = [];
      this.selected_result = "";
      this.updateValue("");
      this.$emit("update:result_id", "");
      this.$emit("clear-data");
      this.result_index = "";
      this.$refs.input.focus();
      this.old_query_term = "";
    },
    clearErrors() {
      if (this.error && this.error != "") {
        this.$emit("update:error", "");
        return;
      }
    },
    adjustSomeProps(obj) {
      obj.id = parseInt(obj.id)
      obj.invoice_last_paid_caption = parseInt(obj.invoice_last_paid_caption)
      obj.is_company = parseInt(obj.is_company)
      obj.id_city = parseInt(obj.id_city)
    }
  },
  computed: {
    searchTermChanged() {
      return this.old_query_term === this.value.trim() ? false : true;
    },
    infoBar() {
      if (this.results.length > 0) {
        return this.normalizedParams.info_msg.match;
      } else if (
        !this.performing_query &&
        this.inputFocus &&
        this.results.length === 0 &&
        this.value != "" &&
        !this.selected_result
      ) {
        return this.normalizedParams.info_msg.notfound;
      } else {
        return this.normalizedParams.info_msg.help_block;
      }
    },
    normalizedParams() {
      return {
        model: null,
        model_distinct_by: null,
        query_field: null,
        query_limit: 10,
        query_method: null,
        wrapper: "col-12",
        addon_icon: null,
        allow_clear: true,
        info_msg: {
          match: "намерени",
          notfound: "няма намерени резултати",
          help_block: "...",
        },
        ...this.params,
      };
    },
  },
  created() {
    this.selected_result = this.result_id;
  },
  mounted() {
    this.autofocus ? this.$refs.input.focus() : false;
  },
};
</script>
<style scoped>
input {
  background-color: inherit;
}
.distinct_tag {
  font-size: 11px;
  color: #7986cb;
}
.has-action {
  transition: all 0.3s ease;
}
.has-action:hover {
  color: #ff5722;
  cursor: pointer;
}
.result-item {
  user-select: none;
  padding: 8px;
  font-size: 12px;
  border-bottom: 1px solid #eaeaea;
  border-radius: 0;
  font-weight: 500;
  color: #3f51b5;
}
.result-item:hover,
.result-item:focus {
  cursor: pointer;
  background-color: #fafafa;
  outline: none;
}
.result-obj {
  color: #676767;
  white-space: nowrap;
}
.key-marked {
  cursor: pointer;
  color: #2196f3;
  background-color: #efefef;
}
.st-group {
  max-height: 350px;
  overflow: auto;
  background-color: #fff;
  z-index: 1000;
  width: 100%;
  transition: all 0.3s ease;
  position: fixed;
  top: 50px;
  left: 82px;
  opacity: 1;
  box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.16), 0 2px 10px 0 rgba(0, 0, 0, 0.12);
  max-width: 284px;
}
/* .st-group-show {

} */
.st-item {
  border-radius: 0;
  padding: 0;
}
.st-item:hover {
  background-color: #efefef;
  cursor: pointer;
}
#st-group-test:focus {
  background-color: #efefef;
  outline: none;
}
.tralala {
  font-weight: 500;
  background-color: #e0e0e0;
}
.wtf {
  font-weight: 500 !important;
  background-color: #e0e0e0 !important;
}
.help-if-addon {
  margin-left: 3rem !important;
}
.wrapper {
  margin: 5px 0 10px;
}
.input-container {
  background: none;
  border-radius: 0;
  box-shadow: none;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
}
.input-block {
  padding-left: 0;
  padding-right: 0;
  padding-top: 0.7rem;
  padding-bottom: 0.2rem;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
}
.input-block input {
  width: 100%;
  height: 18px;
  padding: 0;
  font-size: 1rem;
  border: none;
  background: none;
  border-radius: 0;
  box-shadow: none;
  -webkit-box-flex: 1;
  -ms-flex: 1;
  flex: 1;
  line-height: initial;
  font-weight: bold;
  color: #676767;
  overflow: hidden;
  resize: none;
}
.input-block input:focus {
  color: rgba(0, 0, 0, 0.87);
  outline: none;
}
.input-block input:disabled {
  cursor: not-allowed;
}
.input-clear-icon {
  color: #bdbdbd;
  cursor: pointer;
}
.input-clear-icon:hover {
  color: #616161;
}
.help-if-addon {
  margin-left: 2.5rem !important;
}
.help-text {
  color: #868e96;
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: help;
}
.error-text {
  color: #fff;
  background-color: #e91e63;
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-transform: uppercase;
  font-weight: bold;
  user-select: none;
  cursor: help;
}
.char-counter {
  font-weight: bold;
  font-size: 0.85rem;
  color: #2196f3;
}
.t-blue {
  color: #2196f3;
}
button:disabled {
  cursor: not-allowed;
}
</style>
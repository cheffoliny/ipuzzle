export default {
  methods: {
    getTotalSumFromArr(arr) {
      return arr
        .map((service) => service.single_price * service.quantity)
        .reduce((sum, service_price) => sum + service_price, 0);
    },
    setWindowTitle() {
      document.title = this.docTitle;
    },
    closeService() {
      this.showFreeSaleComponent = false;
      this.resetServiceForEdit();
    },
    resetServiceForEdit() {
      this.serviceForEdit = JSON.parse(JSON.stringify({}));
    },
    cancelDoc() {
      if (confirm("Анулирай документа ?")) {
        this.annulment = true;
        this.storeClientObligations();
      }
    },
    updateDoc() {
      if(this.is_new_relative_doc) {
        this.storeClientObligations();
      }
      else if (confirm("Потвърди промените?")) {
        this.storeClientObligations();
      }
    },
    isValidResponse(json) {
      return typeof (json) == 'string' ? false : true
    },
  },
  computed: {
    urlParams() {
      let params = {};
      let url = new URLSearchParams(location.search);
      params.id_object = url.get("id_object");
      params.page = url.get("page");
      params.id = url.get("id");
      params.is_book = parseInt(url.get("is_book")) === 1;
      return params;
    },
  },
}
export const dateMixin = {
  data() {
    return {
      // datepicker Highlight Saturday's and Sunday's
      highlighted: {
        days: [6, 0]
      }
    }
  },
  methods: {
    toMMYYY(value) {
      return `${moment(value).format("MM.YYYY")}`;
    },
    toDDMMYYYY(date) {
      return moment(date).format("DD.MM.YYYY");
    },
  },
  filters: {
    date(value) {
      return `${moment(value).format("MM.YYYY")} г.`;
    }
  }
}
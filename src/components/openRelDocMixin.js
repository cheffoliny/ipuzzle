export const openRelDocMixin = {
   methods: {
    openRelativeDocument(id, mode = 'sale') {

      if (!id) return;

      const url = mode === 'sale'
        ? `page.php?page=sale_new&id=${id}`
        : `page.php?page=buy_new&id=${id}`

      vPopUp({
        url: url,
        name: `${mode}${id}`,
        width: 960,
        height: 660,
      });
      
    },
  }
}
const app = new Vue({
  el: '#app',
  data: {
    User: null,
    Email: null
  },
  mounted() {
  },
  methods: {
    submit() {
      axios.get('https://portal.facimed.edu.br/Frame/RM/API/TOTVSEducacional/RecoverPass', {
        params: {
          alias: 'CorporeRM',
          url: 'https://portal.facimed.edu.br/Frame/Web/App/Edu/PortalEducacional/Login/#',
          usuario: this.User,
          email: this.Email
        }
      })
      toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "0",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
      }
      toastr.success(`Se uma conta corresponder à <strong>${this.Email}</strong>, você deverá receber um e-mail com instruções de como redefinir sua senha rapidamente.`)

      this.User = null
      this.Email = null
    }
  }
})

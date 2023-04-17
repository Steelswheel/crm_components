
//  /bitrix/js/main/core/core.js :: function onCustomEvent(eventObject, eventName, eventParams, secureParams) :: console.log(eventName,eventObject);

import Vue from 'vue';
import store from './store/store';
Vue.config.productionTip = false;
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

import 'vue-select/dist/vue-select.css';

/* ElementUI */
import lang from 'element-ui/lib/locale/lang/ru-RU';
import locale from 'element-ui/lib/locale';
locale.use(lang);
import 'element-ui/lib/theme-chalk/index.css';
/* ElementUI end */

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue);
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin);

import PortalVue from 'portal-vue';

Vue.use(PortalVue);

import '@assets/main.scss';
import moment from 'moment';
window.moment = moment

import { ElementTiptapPlugin } from 'element-tiptap';

Vue.use(ElementTiptapPlugin, {
    lang: 'ru'
});

import 'element-tiptap/lib/index.css';

Vue.filter('date', function (value) {
    if (value) {
      return moment(value).format('DD.MM.YYYY');
    }

    return '';
});

Vue.filter('datetime', function (value) {
    if (value) {
        let dateFormat = value.indexOf(".") > 0
          ? "DD.MM.YYYY HH:mm:ss"
          : "YYYY-MM-DD HH:mm:ss";

        return moment(value,dateFormat).format('DD.MM.YYYY HH:mm');
    }

    return '';
})

Vue.filter('price', function (value) {
    if (!parseInt(value)) {
        return '-';
    }

    let num = parseFloat(value);

    if (num.toFixed(2).split('.')[1] > 0) {
        num = parseFloat(value).toFixed(2);
    } else {
        num = parseFloat(value).toFixed(0);
    }

    return num.replace(/(\d{1,3}(?=(?:\d\d\d)+(?!\d)))/g, "$1 ");
});

Vue.filter('priceI', function (value) {
    if (!parseInt(value)) {
        return '-';
    }

    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(value);
});

function mountedComponentVue() {
    let vueComponents = document.querySelectorAll('[data-vue-component]');

    vueComponents.forEach((i, key) => {
        i.id = `vue-component-${key}`;

        let componentStr = i.dataset.vueComponent;
        let componentAr = componentStr.split('_');
        let module;

        switch (componentAr[0]) {
            case 'edz.show': module = () => import(`./edz.show/js/${componentAr[1]}`); break;
            case 'order.pay':     module = () => import(`./order.pay/js/${componentAr[1]}`); break;
            case 'document.generate':     module = () => import(`./document.generate/js/${componentAr[1]}`); break;
            case 'work.schedule': module = () => import(`./work.schedule/js/${componentAr[1]}`); break;
            case 'eds.show':      module = () => import(`./eds.show/js/${componentAr[1]}`); break;
            case 'edz.list':      module = () => import(`./edz.list/js/${componentAr[1]}`);  break;
            case 'eds.list':      module = () => import(`./eds.list/js/${componentAr[1]}`); break;
            case 'order.bank':    module = () => import(`./order.bank/js/${componentAr[1]}`); break;
            case 'eds.create':    module = () => import(`./eds.create/js/${componentAr[1]}`); break;
            case 'sbp.list':      module = () => import(`./sbp.list/js/${componentAr[1]}`);  break;
            case 'reports.all.ReportSalePlan':   module = () => import(`./reports.all/ReportSalePlan/js/${componentAr[1]}`); break;
            case 'reports.all.PartnerMap':   module = () => import(`./reports.all/PartnerMap/js/${componentAr[1]}`); break;
            case 'reports.all.PayOnApplications':   module = () => import(`./reports.all/PayOnApplications/js/${componentAr[1]}`); break;
            case 'reports.all.SaleReport':   module = () => import(`./reports.all/SaleReport/js/${componentAr[1]}`); break;
            case 'reports.all.SaleDkp':   module = () => import(`./reports.all/SaleDkp/js/${componentAr[1]}`); break;
            case 'reports.all.SavingsReport':   module = () => import(`./reports.all/SavingsReport/js/${componentAr[1]}`); break;
            case 'reports.all.CbReport':   module = () => import(`./reports.all/CbReport/js/${componentAr[1]}`); break;
            case 'reports.all.CustomReport':   module = () => import(`./reports.all/CustomReport/js/${componentAr[1]}`); break;
            case 'lk':     module = () => import(`./lk/js/${componentAr[1]}`); break;
            case 'design.list': module = () => import(`./design.list/js/${componentAr[1]}`); break;
            case 'edp.show': module = () => import(`./edp.show/js/${componentAr[1]}`); break;
            case 'photo.view': module = () => import(`./photo.view/js/${componentAr[1]}`); break;
            case 'sber.info': module = () => import(`./sber.info/js/${componentAr[1]}`); break;
            case 'money.orders': module = () => import(`./money.orders/js/${componentAr[1]}`); break;
            case 'edp.list': module = () => import(`./edp.list/js/${componentAr[1]}`); break;
        }

        new Vue({
            store,
            render: h => h(module, {props: i.dataset})
        }).$mount(`#vue-component-${key}`);
    });
}


window.mountedComponentVue = mountedComponentVue;
mountedComponentVue();

Vue.directive('phone', {
    bind(el) {
        el.oninput = function(e) {
            if (!e.isTrusted) {
                return;
            }

            const x = this.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);

            if (!x[2] && x[1] !== '') {
                this.value = x[1] === '8' ? x[1] : '8';
            } else {
                this.value = !x[3] ? x[1] + x[2] : x[1] + '(' + x[2] + ')' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
            }
        }
    }
});
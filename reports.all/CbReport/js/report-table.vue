<template>
    <div class="cb-report b-block b-block__content">
        <el-dialog
            title="Выгружаемые файлы"
            :visible.sync="showModal"
            width="40%"
            :append-to-body="true"
        >
            <ul>
                <li>
                    Платежное поручение
                </li>
                <li>
                    Заявление на вступление в КПК
                </li>
                <li>
                    Заявление на получение займа
                </li>
                <li>
                    Договор займа
                </li>
                <li>
                    График платежей
                </li>
                <li>
                    Дополнительное соглашение к договору займа
                </li>
                <li>
                    Акт обследования недвижимого имущества
                </li>
                <li>
                    Оценка платежеспособности
                </li>
                <li>
                    Расписка на стройку
                </li>
                <li>
                    Анкета ПВК
                </li>
                <li>
                    Пояснение при родственной сделке
                </li>
                <li>
                    Свидетельство о заключении (расторжении) брака
                </li>
                <li>
                    Свидетельство МСК
                </li>
                <li>
                    Паспорт заемщика
                </li>
                <li>
                    Фото объекта
                </li>
                <li>
                    Зарегистрированный ДКП
                </li>
                <li>
                    Выписка из ЕГРН с залогом
                </li>
                <li>
                    Расписка от продавца на всю сумму по ДКП
                </li>
                <li>
                    Чек о перечислении суммы по всем траншам
                </li>
                <li>
                    Чек о перечислении всей суммы по ДЗ
                </li>
                <li>
                    Разрешение на строительство
                </li>
                <li>
                    Документы на земельный участок
                </li>
                <li>
                    Документы и фотографии, подтверждающие строительство
                </li>
                <li>
                    Платежные поручения по всем траншам
                </li>
                <li>
                    Свидетельство о рождении детей
                </li>
                <li>
                    Заключение об улучшении жилищных условий
                </li>
            </ul>
        </el-dialog>
        <div class="cb-report-filter mb-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="mr-4">
                    <div class="mb-2">
                        Дата ПФР/РСК:
                    </div>
                    <div>
                        <el-date-picker
                            v-model="date"
                            type="daterange"
                            range-separator="До"
                            start-placeholder="От"
                            end-placeholder="До"
                            value-format="dd.MM.yyyy"
                            format="dd.MM.yyyy"
                            size="small"
                        >
                        </el-date-picker>
                    </div>
                </div>
                <div class="align-self-end">
                    <img src="../../../assets/icon/info.svg" alt="info" width="30" height="30" style="cursor: pointer" @click="showModal = !showModal">
                </div>
            </div>
            <download :ids="ids" :date="date" />
        </div>

        <div id="cd-report-tabs">
            <el-tabs v-model="activeName" @tab-click="setFixedHeight">
                <el-tab-pane label="Приложение 1" name="first">
                    <el-table
                        height="600"
                        style="width: 100%; border: 1px solid #000"
                        :data="tableData"
                        :header-cell-style="{
                            textAlign: 'center',
                            fontSize: '12px',
                            padding: 0,
                            borderRight: '1px solid #000',
                            borderBottom: '1px solid #000',
                            color: '#000'
                        }"
                        :cell-style="{
                            textAlign: 'center',
                            borderRight: '1px solid #000',
                            borderBottom: '1px solid #000'
                        }"
                    >
                        <el-table-column fixed prop="MANAGER" label="ФИО менеджера" width="300"></el-table-column>
                        <el-table-column fixed prop="NUMBER" label="№"></el-table-column>
                        <el-table-column fixed prop="CB_LIST_CONTRIBUTOR" label="Ф.И.О." width="300">
                            <template #default="{row}">
                                <a :href="`/b/edz/?deal_id=${row.ID}&show`" target="_blank">
                                    {{ row.CB_LIST_CONTRIBUTOR }}
                                </a>
                            </template>
                        </el-table-column>
                        <el-table-column label="Данные о Заемщике НФО">
                            <el-table-column label="Паспортные данные">
                                <el-table-column prop="CB_LIST_PASSPORT_SER" label="серия" width="120"></el-table-column>
                                <el-table-column prop="CB_LIST_PASSPORT_NUMBER" label="номер" width="140"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Адрес заемщика">
                                <el-table-column prop="CB_LIST_REGISTER_PLACE" label="Место регистрации" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_FACT_PLACE" label="Место фактического проживания" width="300"></el-table-column>
                            </el-table-column>
                            <el-table-column prop="CB_LIST_KPK_INTRO_DATE" label="дата вступления в членство кпк (ддммгг)" width="140"></el-table-column>
                            <el-table-column prop="CB_LIST_KPK_EXIT_DATE" label="дата прекращения членства в кпк (ддммгг)" width="160"></el-table-column>
                        </el-table-column>
                        <el-table-column label="Сведения о движении денежных средств НФО">
                            <el-table-column label="Поступление  денежных средств (МСК) от УФК (ОПФР)">
                                <el-table-column prop="CB_LIST_PDF_MONEY_DATE" label="Дата" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_PDF_MONEY_SUM" label="Сумма, руб." width="150"></el-table-column>
                                <el-table-column prop="CB_LIST_PFR_DEPART" label="Отделение ПФР" width="150"></el-table-column>
                                <el-table-column prop="CB_LIST_PFR_INN" label="ИНН ПФР" width="150"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Внесение Заемщиком паевого взноса">
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PAY_DATE" label="Дата" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PAY_SUM_CASHLESS" label="Сумма безналичных денежных средств, руб." width="200"></el-table-column>
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PAY_SUM_SPOT" label="Сумма наличных денежных средств, руб." width="200"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Договор займа, заключенный с Заемщиком">
                                <el-table-column prop="CB_LIST_LOAN_CONTRACT_DATE" label="Дата" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_LOAN_CONTRACT_SUM" label="Сумма, руб." width="150"></el-table-column>
                                <el-table-column prop="CB_LIST_LOAN_CONTRACT_RATE" label="Годовая % ставка по договору займа" width="155"></el-table-column>
                                <el-table-column prop="CB_LIST_LOAN_CONTRACT_PERIOD" label="Срок, на который заключен договор займа, мес" width="150"></el-table-column>
                                <el-table-column prop="CB_LIST_LOAN_CONTRACT_TYPE" label="Вид обеспечения по договору займа" width="152"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Сведения о выдаче займа">
                                <el-table-column prop="CB_LIST_LOAN_ISSUANCE_DATE" label="Дата выдачи" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_LOAN_ISSUANCE_SUM" label="Сумма безналичных денежных средств, руб." width="200"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Оплата заемщиком процентов по договору займа">
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PERCENT_DATE" label="Дата" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PERCENT_SUM_CASHLESS" label="Сумма безналичных денежных средств, руб. (указывается общая сумма)" width="200"></el-table-column>
                                <el-table-column prop="CB_LIST_CONTRIBUTOR_PERCENT_SUM_SPOT" label="Сумма наличных денежных средств, руб. (указывается общая сумма)" width="200"></el-table-column>
                            </el-table-column>
                        </el-table-column>
                        <el-table-column label="Сведения о сделке с недвижимым имуществом, приобретаемым за счет средств МСК">
                            <el-table-column label="Договор купли-продажи (долевого участия) недвижимого имущества">
                                <el-table-column prop="CB_LIST_DKP_NUMBER" label="№ договора купли-продажи (договора долевого участия)" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_DKP_DATE" label="Дата договора купли-продажи (договора долевого участия)" width="170"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_NAME" label="Ф.И.О. продавца недвижимости / Наименование застройщика" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_PASSPORT" label="Паспортные данные продавца недвижимости / ИНН застройщика" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_RESISTER_PLACE" label="Адрес регистрации продавца" width="200"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_FACT_ADDRESS" label="Адрес фактического проживания продавца" width="200"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_ENTER_KPK_DATE" label="Дата вступления продавца в членство КПК (ддммгг)" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_EXIT_KPK_DATE" label="Дата прекращения членства продавца в КПК (ддммгг)"  width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_OWNERSHIP_DATE" label="Дата, с которой продавец  являлся собственником недвижимости (в отношении договора долевого участия в графе указывается 'ДДУ')"  width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_ESTATE_ADDRESS" label="Адрес объекта приобретенной недвижимости за счет средств МСК" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_DEAL_SUM" label="Сумма сделки,  руб." width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_SHARE" label="Доля приобоетаемого имущества, %" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_KADAS_NUMBER" label="Кадастровый номер объекта"  width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_KADAS_COST" label="Кадастровая стоимость объекта" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_SQUARE" label="Площадь приобретаемого объекта, м2" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_SELLER_REPRESENTATIVE_FIO" label="ФИО представителя продавца" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_BUYER_REPRESENTATIVE_FIO" label="ФИО представителя покупателя" width="300"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Сведения о переходе права собственности на объект недвижимости / перераспределении приобретенной недвижимости в обшедолевую собственность на всех членов семьи"  width="200">
                                <el-table-column prop="CB_LIST_DOC_NAME" label="Наименование правоустанавливающего документа" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_TRANSFER_OF_OWNERSHIP_DATE" label="Дата перехода права собственности" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_HOMEOWNERS_FIO" label="Ф.И.О. лица (лиц) собственников жилья" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_FINAL_DATE" label="Окончательный срок распределения долей в общедолевую собственность" width="140"></el-table-column>
                            </el-table-column>
                        </el-table-column>
                        <el-table-column label="Сведения о займе на строительство">
                            <el-table-column label="Сведения о праве собственности Заемщика на землю в случае предоставления займа на строительство">
                                <el-table-column prop="CB_LIST_GROUND_DOC" label="Дата/ номер документа" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_GROUND_ADDRESS" label="Адрес земельного участка" width="160"></el-table-column>
                                <el-table-column prop="CB_LIST_GROUND_KADAS_NUMBER" label="Кадастровый номер" width="160"></el-table-column>
                            </el-table-column>
                        </el-table-column>
                        <el-table-column label="Работа НФО">
                            <el-table-column prop="CB_LIST_SIGNER_FIO" label="ФИО лица, подписавшего договор займа от имени кооператива" width="300"></el-table-column>
                            <el-table-column label="Сведения о лице, проводившем проверку недвижимости на пригодность для жилья / осмотр земельного участка для ведения строительства">
                                <el-table-column prop="CB_LIST_NFO_FIO" label="ФИО сотрудника НФО" width="300"></el-table-column>
                                <el-table-column prop="CB_LIST_NON_NFO_FIO" label="ФИО третьего лица (агент, партнер и т.п.), не состоящего в трудовых отношениях с НФО" width="300"></el-table-column>
                            </el-table-column>
                            <el-table-column label="Информация о результатах выездной проверки НФО недвижимости на пригодность для жилья (земельного участка)">
                                <el-table-column prop="CB_LIST_NFO_COMISSION_DATE" label="дата" width="140"></el-table-column>
                                <el-table-column prop="CB_LIST_NFO_COMISSION_RESULT" label="результат" width="160"></el-table-column>
                            </el-table-column>
                            <el-table-column prop="CB_LIST_FAMILY_SHARE" label="Доля имущества, которая стала принадлежать семье (заемщик,супруг, дети) после сделки-купли-продажи с участием средств МСК  (в том числе включая долю имущества, принадлежавшую до сделки)" width="160"></el-table-column>
                            <el-table-column prop="CB_LIST_KPK_COMISSION" label="Проверка КПК на перераспределение приобретаемой недвижимости в общую долевую собственность на детей (результат, дата, доля)" width="160"></el-table-column>
                            <el-table-column prop="CB_LIST_REMOVAL_OF_ENCUMBRANCE_DATE" label="Дата снятия КПК обременения на заложенный объект недвижимого имущества" width="140"></el-table-column>
                            <el-table-column prop="CB_LIST_6001_CODE" label="Информация о направлении сведений по сделке в уполномоченный орган с кодом 6001 (да/нет)" width="160"></el-table-column>
                            <el-table-column prop="CB_LIST_DUBIOUS_OPERATION" label="Код вида признака сомнительной операции" width="160"></el-table-column>
                            <el-table-column prop="CB_LIST_SEND_MESSAGES_DATE" label="Дата отправки сообщений" width="160"></el-table-column>
                            <el-table-column prop="CB_LIST_OTHER_INFO" label="Иная значимая информация (при необходимости)" width="160"></el-table-column>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
                <el-tab-pane label="Приложение 3" name="third">
                    <el-table
                        border
                        height="600"
                        style="width: 100%; border: 1px solid #000"
                        :data="tableData"
                        :header-cell-style="{
                            textAlign: 'center',
                            fontSize: '12px',
                            padding: 0,
                            backgroundColor: '#F5F7FA',
                            borderRight: '1px solid #000',
                            borderBottom: '1px solid #000',
                            color: '#000'
                        }"
                        :cell-style="{
                            textAlign: 'center',
                            borderRight: '1px solid #000',
                            borderBottom: '1px solid #000'
                        }"
                    >
                        <el-table-column fixed prop="CB_LIST_3_NUMBER" label="№ п/п"></el-table-column>
                        <el-table-column fixed prop="CB_LIST_CONTRIBUTOR" label="ФИО клиента" width="300">
                            <template #default="{row}">
                                <a :href="`/b/edz/?deal_id=${row.ID}&show`" target="_blank">
                                    {{ row.CB_LIST_CONTRIBUTOR }}
                                </a>
                            </template>
                        </el-table-column>
                        <el-table-column prop="CB_LIST_3_PASSPORT" label="Данные паспорта" width="300"></el-table-column>
                        <el-table-column prop="CB_LIST_3_INN" label="ИНН" width="120"></el-table-column>
                        <el-table-column prop="CB_LIST_3_REGISTER_PLACE" label="Адрес регистрации" width="250"></el-table-column>
                        <el-table-column prop="CB_LIST_3_CONTRACT_DATE" label="Дата установления договорных отношений" width="140"></el-table-column>
                        <el-table-column prop="CB_LIST_3_IDENTIFY_DATE" label="Дата идентификации" width="140"></el-table-column>
                        <el-table-column prop="CB_LIST_3_OPERATIONS_START_DATE" label="Дата начала активных операций" width="140"></el-table-column>
                        <el-table-column prop="CB_LIST_3_RETRY_IDENTIFICATION_DATE" label="Дата повторной идентификации" width="140"></el-table-column>
                        <el-table-column prop="CB_LIST_3_RISK_LEVEL" label="Уровень риска, присвоенный на дату предоставления информации" width="170"></el-table-column>
                        <el-table-column prop="CB_LIST_3_TARGET" label="Заявленная цель установления договорных отношений при первичном обращении" width="200"></el-table-column>
                        <el-table-column prop="CB_LIST_3_FACT_OPERATIONS" label="Фактически осуществляемые операции" width="200"></el-table-column>
                        <el-table-column prop="CB_LIST_3_REVISION_RESULT_EXTR" label="Результат проверки по Перечню экстремистов, Перечню ФРОМУ и Решениям" width="200"></el-table-column>
                        <el-table-column prop="CB_LIST_3_REVISION_RESULT_PASS" label="Результат проверки паспортных данных по перечню недействительных паспортов" width="200"></el-table-column>
                        <el-table-column prop="CB_LIST_3_OTHER_INFO" label="Иная значимая информация" width="200"></el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </div>
        <el-pagination
            hide-on-single-page
            background
            @size-change="setLimit"
            @current-change="setPageNumber"
            :current-page.sync="pageNumber"
            :page-sizes="[50, 100, 200, 500]"
            :page-size="limit"
            :pager-count="11"
            layout="sizes, prev, pager, next"
            :total="pageCount"
            v-if="pageCount > 0"
            class="mt-4"
        />
    </div>
</template>

<script>
import { Dialog, Pagination, Table, TableColumn, DatePicker, Tabs, TabPane, Loading } from 'element-ui';
import { BX_POST } from '@app/API';
import Download from './download';

export default {
    name: 'report-table',
    components: {
        Download,
        'el-dialog': Dialog,
        'el-pagination': Pagination,
        'el-table': Table,
        'el-table-column': TableColumn,
        'el-date-picker': DatePicker,
        'el-tabs': Tabs,
        'el-tab-pane': TabPane
    },
    data() {
        return {
            showModal: false,
            limit: 50,
            pageCount: 0,
            tableData: [],
            date: [],
            activeName: 'first',
            ids: [],
            pageNumber: 1
        }
    },
    watch: {
        date(value) {
            if (value.length > 0) {
                this.pageNumber = 1;

                this.getData(this.pageNumber);
            }
        }
    },
    methods: {
        setLimit(value) {
            this.limit = value;
            this.pageNumber = 1;

            if (this.date.length > 0) {
                this.getData(this.pageNumber);
            }
        },
        setPageNumber(value) {
            this.pageNumber = value;

            if (this.date.length > 0) {
                this.getData(this.pageNumber);
            }
        },
        getData(pageNumber) {
            let load = Loading.service({
                target: '#cd-report-tabs',
                fullscreen: true,
                background: '000'
            });

            this.pageNumber = pageNumber;

            BX_POST('vaganov:reports.all', 'getData', {
                startDate: this.date[0],
                endDate: this.date[1],
                pageNumber: pageNumber,
                limit: this.limit
            })
            .then(r => {
                this.pageCount = r.PAGES_COUNT;
                this.tableData = r.ROWS;
                this.ids = r.IDs;

                document.querySelector('#pane-first').querySelector('.el-table__body-wrapper').scrollTop = 0;
                document.querySelector('#pane-third').querySelector('.el-table__body-wrapper').scrollTop = 0;
            })
            .catch(e => console.log(e))
            .finally(() => {
                load.close();
            });
        },
        setFixedHeight() {
            let fixed = document.querySelector('.cb-report').querySelector('.el-table__fixed');

            fixed.style.height = (fixed.offsetHeight - 20) + 'px';
        }
    },
    mounted() {
        setTimeout(() => {
            this.setFixedHeight();
        }, 0);
    }
}
</script>

<style>
    .cb-report .el-table .cell {
        word-break: break-word;
    }

    .cb-list {
      visibility: hidden;
      overflow: hidden;
    }

    .cb-list .cb-list-flex-content {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .cb-list .icon {
      background-size: 100%;
      width: 25px;
      height: 25px;
      display: inline-block;
      margin-right: 5px;
      background-repeat: no-repeat;
    }

    .cb-list .icon-birthday {
      background-image: url(/local/img/birthday.png);
      width: 20px;
      height: 20px;
      margin: 2px;
    }

    .cb-list .cb-list-grid .main-ui-filter-search {
      margin: 0 10px 20px 0;
      border: 1px solid #d8d8d8;
    }

    #cb-list .main-grid-table, #cb-list-3 .main-grid-table {
      border-collapse: collapse;
    }

    #cb-list .main-grid-table tr, #cb-list-3 .main-grid-table tr {
      border: 0;
    }

    #cb-list .main-grid-table th, #cb-list-3 .main-grid-table th {
      background-color: #65e55a;
    }

    #cb-list .main-grid-table th.grey, #cb-list-3 .main-grid-table th.grey {
      background-color: #b0b0b0;
    }

    #cb-list .main-grid-table td, #cb-list-3 .main-grid-table td {
      border: 1px #d8d8d8 solid!important;
    }

    #cb-list .main-grid-table th, #cb-list-3 .main-grid-table th {
      border-top: 1px #d8d8d8 solid!important;
      border-right: 1px #d8d8d8 solid!important;
      border-bottom: 1px #d8d8d8 solid!important;
    }

    #cb-list .main-grid-table td:last-child,
    #cb-list .main-grid-table th:last-child,
    #cb-list-3 .main-grid-table td:last-child,
    #cb-list-3 .main-grid-table th:last-child {
      border-right: none!important;
    }

    #cb-list .main-grid-table .main-grid-head-title,
    #cb-list-3 .main-grid-table .main-grid-head-title {
      text-overflow: unset;
      font-weight: bolder;
      font-size: 10px;
      padding: 0;
      text-align: center;
      color: #000000;
    }

    #cb-list .main-grid-table .main-grid-cell-head-container,
    #cb-list-3 .main-grid-table .main-grid-cell-head-container {
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0;
      font-weight: bolder;
      min-width: unset;
    }

    .cb-list .main-grid-cell-content {
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 4;
      overflow: hidden;
      height: 67px;
      max-width: 200px;
      margin: 5px auto;
      font-size: 12px;
    }

    .cb-list .main-grid-cell-content[data-open="true"] {
      display: block;
      height: auto;
    }

    #cb-list .main-grid-table .main-grid-cell-head-container,
    #cb-list .main-grid-table .main-grid-cell-content,
    #cb-list .main-grid-table .main-grid-head-title,
    #cb-list-3 .main-grid-table .main-grid-cell-head-container,
    #cb-list-3 .main-grid-table .main-grid-cell-content,
    #cb-list-3 .main-grid-table .main-grid-head-title {
      text-align: center;
    }

    #cb-list .main-grid-table .main-grid-head-title,
    #cb-list-3 .main-grid-table .main-grid-head-title {
      overflow: visible;
    }

    #cb-list .main-grid-table .main-grid-cell-content,
    #cb-list-3 .main-grid-table .main-grid-cell-content {
      min-height: 53px;
    }

    #cb-list .main-grid-table .main-grid-cell-head-container .main-grid-control-sort,
    #cb-list-3 .main-grid-table .main-grid-cell-head-container .main-grid-control-sort {
      left: 0;
    }

    #cb-list .main-grid-table .main-grid-cell:not(:first-child),
    #cb-list-3 .main-grid-table .main-grid-cell:not(:first-child) {
      max-width: 10%;
    }

    .cb-list .main-grid-cell-content {
      margin: 9px 9px 9px;
    }

    .cb-list .cb-list-active-row * {
      background-color: #f6f8f9;
      transition: background-color 1s;
    }

    .cb-list .cb-list-wrap {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .cb-list .main-grid-cell-checkbox:first-child .main-grid-cell-content, .main-grid-cell-checkbox:first-child .main-grid-cell-head-container {
      padding-left: 0;
    }

    .cb-list .cb-list-button {
      display: inline-block;
      padding: 10px 20px;
      border: 1px solid #ddd;
      margin: 5px 10px;
      border-radius: 2px;
      text-transform: uppercase;
      transition: background-color 0.4s;
      font-size: 12px;
      line-height: 100%;
      letter-spacing: .5px;
      color: #000;
      font-weight: bold;
    }

    #cb-list .cb-list-switcher-1 .main-grid-head-title,
    #cb-list .cb-list-switcher-2 .main-grid-head-title,
    #cb-list .cb-list-switcher-3 .main-grid-head-title,
    #cb-list .cb-list-switcher-4 .main-grid-head-title,
    #cb-list-3 .cb-list-switcher-1 .main-grid-head-title,
    #cb-list-3 .cb-list-switcher-2 .main-grid-head-title,
    #cb-list-3 .cb-list-switcher-3 .main-grid-head-title,
    #cb-list-3 .cb-list-switcher-4 .main-grid-head-title {
      font-size: 28px;
      line-height: 2.1;
      height: 100%;
      cursor: pointer;
    }

    #cb-list .main-grid-table th[data-visibility='hidden'],
    #cb-list .main-grid-table td[data-visibility='hidden'],
    #cb-list-3 .main-grid-table th[data-visibility='hidden'],
    #cb-list-3 .main-grid-table td[data-visibility='hidden'] {
      border: none!important;
    }

    #cb-list th[data-visibility='hidden'],
    #cb-list td[data-visibility='hidden'],
    #cb-list-3 th[data-visibility='hidden'],
    #cb-list-3 td[data-visibility='hidden'] {
      visibility: hidden;
      width: 0;
      min-width: 0;
    }

    #cb-list th[data-visibility='hidden'] > *,
    #cb-list td[data-visibility='hidden'] > *,
    #cb-list-3 th[data-visibility='hidden'] > *,
    #cb-list-3 td[data-visibility='hidden'] > * {
      padding: 0;
      min-width: 0;
      width: 0;
      margin: 0;
    }

    #cb-list .cb-list-switcher-1,
    #cb-list .cb-list-switch-1 {
      background-color: #facded!important;
    }

    #cb-list .cb-list-switcher-2,
    #cb-list .cb-list-switch-2 {
      background-color: #cecdfa!important;
    }

    #cb-list .cb-list-switcher-3,
    #cb-list .cb-list-switch-3 {
      background-color: #bef7c4!important;
    }

    #cb-list .cb-list-switcher-4,
    #cb-list .cb-list-switch-4 {
      background-color: #fdff96!important;
    }

    .cb-list .cb-list-tabs {
      width: 100%;
      background-color: #fff;
    }

    .cb-list .cb-list-content .cb-list-tabs-tab[data-active="false"] {
      display: none;
    }

    .cb-list .cb-list-tabs .cb-list-tabs-button {
      height: 60px;
      line-height: 60px;
      margin: 0;
      border-bottom: 2px solid transparent;
      padding: 0 20px;
      list-style: none;
      cursor: pointer;
      position: relative;
      transition: border-color .3s, background-color .3s, color .3s;
      box-sizing: border-box;
      white-space: nowrap;
    }

    .cb-list .cb-list-tabs .cb-list-tabs-button[data-active="true"] {
      border-bottom: 2px solid #409EFF;
      color: #303133;
    }
</style>
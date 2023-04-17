<template>
  <div class="dkp">
    <div class="dkp-filter d-flex align-items-center mb-2">
      <div class="dkp-filter-item mr-4">
        <div class="dkp-filter-item-title mb-2">
          Дата
        </div>
        <el-date-picker
            v-model="date"
            value-format="dd.MM.yyyy"
            format="dd.MM.yyyy"
            type="daterange"
            size="mini"
            unlink-panels
            range-separator="До"
            start-placeholder="Начало"
            end-placeholder="Конец"
            :picker-options="{
            firstDayOfWeek: 1
          }"
        />
      </div>
      <div class="dkp-filter-item">
        <div class="dkp-filter-item-title mb-2">
          Фильтр по отделам продаж
        </div>
        <el-select
            v-model="selectedDepartments"
            collapse-tags
            clearable
            placeholder="Отдел продаж"
        >
          <el-option
              v-for="(name, id) in departments"
              :value="id"
              :label="name"
              :key="name"
          >
            {{ name }}
          </el-option>
        </el-select>
      </div>
    </div>
    <bar
        v-if="loaded"
        :chartOptions="chartOptions"
        :chartData="chartData"
        :height="500"
    />
  </div>
</template>

<script>
    import bar from './type/bar';
    import { BX_POST } from '@app/API';
    import { DatePicker, Select, Option } from 'element-ui';

    export default {
      name: 'dkp',
      components: {
        bar,
        'el-date-picker': DatePicker,
        'el-select': Select,
        'el-option': Option
      },
      data() {
        return {
          selectedDepartments: [],
          departments: [],
          loaded: false,
          date: [],
          chartData: {
            labels: [],
            datasets: [
              {
                label: 'ДКП НА ВСЕХ',
                data: [],
                backgroundColor: 'rgba(42, 119, 235, 0.75)'
              },
              {
                label: 'ДКП НА ЗАЕМЩИКА',
                data: [],
                backgroundColor: 'rgba(235, 42, 65, 0.75)'
              }
            ],
            deals: []
          },
          chartOptions: {
            plugins: {
              // Change options for ALL labels of THIS CHART
              datalabels: {
                color: '#000',
                font: {
                  weight: 'bold',
                  size: '14'
                }
              }
            },
            events: ['click'],
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              xAxes: [{
                stacked: true
              }],
              yAxes: [{
                stacked: true,
                ticks: {
                  precision: 0,
                  min: 0
                }
              }]
            },
            tooltips: {
              enabled: false,
              custom(tooltipModel) {
                let tooltipEl = document.querySelector('#chartjs-tooltip');

                if (!tooltipEl) {
                  tooltipEl = document.createElement('div');
                  tooltipEl.id = 'chartjs-tooltip';

                  let label = document.createElement('div');
                  label.classList.add('chartjs-tooltip-label');
                  tooltipEl.append(label);

                  let body = document.createElement('div');
                  body.classList.add('chartjs-tooltip-body', 'mt-2');
                  tooltipEl.append(body);

                  document.body.appendChild(tooltipEl);
                }

                let tooltipLabel = tooltipEl.querySelector('.chartjs-tooltip-label');
                let tooltipBody = tooltipEl.querySelector('.chartjs-tooltip-body');

                if (tooltipModel.body) {
                  let dataPoints = tooltipModel.dataPoints[0];

                  tooltipLabel.innerHTML = tooltipModel.body[0].lines;

                  tooltipBody.innerHTML = '';

                  //datasetIndex 0 - с ДКП, 1 - без ДКП
                  if (dataPoints.datasetIndex === 0) {
                    this._data.deals[dataPoints.index].WITH_DKP.forEach(item => {
                      tooltipBody.insertAdjacentHTML('beforeend', item);
                    });
                  } else {
                    this._data.deals[dataPoints.index].WITHOUT_DKP.forEach(item => {
                      tooltipBody.insertAdjacentHTML('beforeend', item);
                    });
                  }
                }

                if (tooltipModel.opacity === 0) {
                  tooltipEl.style.opacity = 0;

                  return;
                }

                // `this` will be the overall tooltip
                const position = this._chart.canvas.getBoundingClientRect();

                // Display, position, and set styles for font
                tooltipEl.style.position = 'absolute';
                tooltipEl.style.opacity = 1;
                tooltipEl.style.ftSize = '10px';
                tooltipEl.style.fontFamily = tooltipModel._bodyFontFamily;
                tooltipEl.style.left = tooltipModel.caretX + (position.left / 5) + 'px';
                tooltipEl.style.top = position.top + tooltipModel.caretY + 'px';
                tooltipEl.style.padding = tooltipModel.yPadding + 'px ' + tooltipModel.xPadding + 'px';
              }
            }
          }
        }
      },
      watch: {
        selectedDepartments(value) {
          this.loaded = false;

          this.chartData.deals = [];
          this.chartData.datasets[0].data = [];
          this.chartData.datasets[1].data = [];
          this.chartData.labels = [];

          BX_POST('vaganov:reports.all', 'saleDkpDkp', {
            startDate: this.date[0] ? this.date[0] : '',
            endDate: this.date[1] ? this.date[1] : '',
            departments: value
          }).then((response) => {
            for (let depatrmentId in response.COUNT) {
              for (let id in response.COUNT[depatrmentId]) {
                this.chartData.labels.push(response.COUNT[depatrmentId][id].NAME);
                this.chartData.datasets[0].data.push(response.COUNT[depatrmentId][id].WITH_DKP);
                this.chartData.datasets[1].data.push(response.COUNT[depatrmentId][id].WITHOUT_DKP);
                this.chartData.deals.push(response.DEALS[depatrmentId][id]);
              }
            }
          }, (error) => {
            console.log(error);
          }).finally(() => {
            this.loaded = true;
          });
        },
        date(value) {
          this.loaded = false;

          this.chartData.deals = [];
          this.chartData.datasets[0].data = [];
          this.chartData.datasets[1].data = [];
          this.chartData.labels = [];

          BX_POST('vaganov:reports.all', 'saleDkpDkp', {
            startDate: value ? value[0] : '',
            endDate: value ? value[1] : '',
            departments: this.selectedDepartments.length > 0 ? this.selectedDepartments : ''
          }).then((response) => {
            for (let depatrmentId in response.COUNT) {
              for (let id in response.COUNT[depatrmentId]) {
                this.chartData.labels.push(response.COUNT[depatrmentId][id].NAME);
                this.chartData.datasets[0].data.push(response.COUNT[depatrmentId][id].WITH_DKP);
                this.chartData.datasets[1].data.push(response.COUNT[depatrmentId][id].WITHOUT_DKP);
                this.chartData.deals.push(response.DEALS[depatrmentId][id]);
              }
            }
          }, (error) => {
            console.log(error);
          }).finally(() => {
            this.loaded = true;
          });
        }
      },
      mounted() {
        BX_POST('vaganov:reports.all', 'saleDkpDkp').then((response) => {
          this.departments = response.DEPARTMENTS;

          for (let depatrmentId in response.COUNT) {
            for (let id in response.COUNT[depatrmentId]) {
              this.chartData.labels.push(response.COUNT[depatrmentId][id].NAME);
              this.chartData.datasets[0].data.push(response.COUNT[depatrmentId][id].WITH_DKP);
              this.chartData.datasets[1].data.push(response.COUNT[depatrmentId][id].WITHOUT_DKP);
              this.chartData.deals.push(response.DEALS[depatrmentId][id]);
            }
          }
        }, (error) => {
          console.log(error);
        }).finally(() => {
          this.loaded = true;
        });
      }
    }
</script>

<style>
  .dkp {
    padding: 20px;
    background-color: #fff;
  }

  .dkp-filter-item-title {
    font-size: 12px;
    font-weight: bold;
  }

  #chartjs-tooltip {
    font-size: 12px;
    line-height: 100%;
    background-color: #000000ab;
    padding: 10px;
    border-radius: 5px;
    color: #fff;
    max-width: 200px;
  }

  #chartjs-tooltip .chartjs-tooltip-link {
    color: #fff;
  }

  #chartjs-tooltip .chartjs-tooltip-label {
    font-weight: bold;
    font-size: 14px;
    line-height: 120%;
  }

  #chartjs-tooltip .chartjs-tooltip-body {
    max-height: 120px;
    overflow: auto;
  }

  #chartjs-tooltip .chartjs-tooltip-body::-webkit-scrollbar {
    width: 5px;
  }

  #chartjs-tooltip .chartjs-tooltip-body::-webkit-scrollbar-track {
    background-color: #00000075;        /* цвет дорожки */
  }

  #chartjs-tooltip .chartjs-tooltip-body::-webkit-scrollbar-thumb {
    background-color: #fff;    /* цвет плашки */
    border-radius: 20px;       /* закругления плашки */
    border: 3px solid #fff;  /* padding вокруг плашки */
  }
</style>
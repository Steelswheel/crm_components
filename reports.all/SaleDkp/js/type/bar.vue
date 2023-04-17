<script>
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { Bar, mixins } from 'vue-chartjs';

export default {
  name: 'bar',
  extends: Bar,
  mixins: [mixins.reactiveProp],
  props: {
    chartData: {
      type: Object,
      default: null
    },
    chartOptions: {
      type: Object,
      default: null
    }
  },
  mounted () {
    this.addPlugin(ChartDataLabels);
    this.renderChart(this.chartData, this.chartOptions);
  }
}
</script>

<style scoped>

</style>
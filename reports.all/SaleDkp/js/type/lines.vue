<script>
import { Line } from 'vue-chartjs';

export default {
  name: 'lines',
  extends: Line,
  props: {
    chartData: {
      type: Object,
      default: null
    },
    options: {
      type: Object,
      default: null
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options);
  }
}
</script>

<style scoped>

</style>
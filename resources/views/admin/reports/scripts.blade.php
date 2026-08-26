<script>
    function reportsPage() {
        return {
            tab: localStorage.getItem('reportsTab') || 'daily',

            setTab(value) {
                this.tab = value;
                localStorage.setItem('reportsTab', value);
            },
        }
    }
</script>

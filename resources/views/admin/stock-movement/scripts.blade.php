<script>
    function movementPage() {
        return withPagination({
            stockHeaders: @json($stockHeaders),
            stockmovements: @json($stockmovements),

            searchQuery: '',
            filterType: '',
            categoryFilter: '',
            filterUser: '',

            currentPage: 1,
            perPage: 20,

            reasonOpen: false,
            reasonResults: [],
            allReasons: ['All Reasons', 'Restock', 'Customer Return', 'Damaged', 'Stock Count Correction',
                'Transfer',
                'Initial Stock', 'Lost or Stolen', 'Loss: Theft', 'Accident', 'Other'
            ],

            get filteredRecords() {
                let result = [...this.stockmovements];
                
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(m =>
                        (m.product?.name || '').toLowerCase().includes(q) ||
                        (m.product?.category?.name || '').toLowerCase().includes(q) ||
                        (m.user?.name || '').toLowerCase().includes(q) ||
                        (m.reason || '').toLowerCase().includes(q) ||
                        (m.reference || '').toLowerCase().includes(q)
                    );
                }

                if (this.categoryFilter) {
                    result = result.filter(
                        m => m.product?.category?.name === this.categoryFilter
                    );
                }

                if (this.filterUser) {
                    result = result.filter(
                        m => m.user?.name === this.filterUser
                    )
                }

                if (this.filterType) result = result.filter(m => m.type === this.filterType);

                return result;

            },

            // Inside movementPage(), add:
            toggleClearButton() {
                const btn = document.getElementById('clearSearch');
                if (btn) btn.style.display = this.searchQuery.length > 0 ? 'block' : 'none';
            },
        });
    }
</script>

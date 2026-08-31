<script>
    function movementPage() {
        return {
            movements: @json($movements),
            searchQuery: '',
            filterType: '',
            categoryFilter: '',

            currentPage: 1,
            perPage: 20,

            reasonOpen: false,
            reasonResults: [],
            allReasons: ['All Reasons', 'Restock', 'Customer Return', 'Damaged', 'Stock Count Correction', 'Transfer',
                'Initial Stock', 'Lost or Stolen', 'Loss: Theft', 'Accident', 'Other'
            ],

            get filteredMovements() {
                let result = [...this.movements];
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    result = result.filter(m =>
                        (m.product?.name || '').toLowerCase().includes(q) ||
                        (m.product?.category?.name || '').toLowerCase().includes(q) ||
                        (m.reason || '').toLowerCase().includes(q) ||
                        (m.reference || '').toLowerCase().includes(q)
                    );
                }

                if (this.categoryFilter) {
                    result = result.filter(
                        m => m.product?.category?.name === this.categoryFilter
                    );
                }

                if (this.filterType) result = result.filter(m => m.type === this.filterType);

                return result;

            },

            // Inside movementPage(), add:
            toggleClearButton() {
                const btn = document.getElementById('clearSearch');
                if (btn) btn.style.display = this.searchQuery.length > 0 ? 'block' : 'none';
            },

            get totalPages() {
                return Math.ceil(this.filteredMovements.length / this.perPage);
            },
            get paginatedMovements() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredMovements.slice(start, start + this.perPage);
            },
            get showingText() {
                const start = (this.currentPage - 1) * this.perPage + 1;
                const end = Math.min(this.currentPage * this.perPage, this.filteredMovements.length);
                return `Showing ${start}-${end} of ${this.filteredMovements.length} entries`;
            },
            get pageNumbers() {
                const pages = [];
                for (let i = 1; i <= this.totalPages; i++) {
                    if (i === 1 || i === this.totalPages || (i >= this.currentPage - 2 && i <= this.currentPage +
                            2)) pages.push(i);
                    else if (pages[pages.length - 1] !== '...') pages.push('...');
                }
                return pages;
            },
            applyFilters() {
                this.currentPage = 1;
            },
            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },
            goToPage(page) {
                if (typeof page === 'number') this.currentPage = page;
                this.$nextTick(() => {
                    const el = this.$refs.tableBody;
                    if (el) el.scrollTop = 0;
                });
            },
        };
    }
</script>

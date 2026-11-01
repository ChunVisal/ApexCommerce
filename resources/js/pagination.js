export function withPagination(page, listKey = 'filteredRecords') {
    const pagination = {
        currentPage: 1,
        perPage: 15,

        get totalPages() {
            return Math.ceil(this[listKey].length / this.perPage);
        },
        get showPagination() {
            return this[listKey].length >= this.perPage;
        },
        get paginatedRecords() {
            if (!this.showPagination) return this[listKey];
            const start = (this.currentPage - 1) * this.perPage;
            return this[listKey].slice(start, start + this.perPage);
        },
        get showingText() {
            const total = this[listKey].length;
            const start = total ? (this.currentPage - 1) * this.perPage + 1 : 0;
            const end = Math.min(this.currentPage * this.perPage, total);
            return `Showing ${start}-${end} of ${total} entries`;
        },
        get pageNumbers() {
            const pages = [];
            for (let i = 1; i <= this.totalPages; i++) {
                if (i === 1 || i === this.totalPages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) pages.push(i);
                else if (pages[pages.length - 1] !== '...') pages.push('...');
            }
            return pages;
        },
        applyFilters() { this.currentPage = 1; },
        prevPage() { if (this.currentPage > 1) this.currentPage--; },
        nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
        goToPage(page) {
            if (typeof page === 'number') this.currentPage = page;
            this.$nextTick(() => {
                const el = this.$refs.tableBody;
                if (el) el.scrollTop = 0;
            });
        },
    };

    return Object.defineProperties(page, Object.getOwnPropertyDescriptors(pagination));
}
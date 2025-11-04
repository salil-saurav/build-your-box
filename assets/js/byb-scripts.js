document.addEventListener('DOMContentLoaded', () => {

    const BYB = {
        products: [],
        box: [],
        maxCapacity: 10,
        capacityType: 'items',
        currentPage: 1,
        totalPages: 1,
        perPage: 12,

        init() {
            if (typeof bybSettings !== 'undefined') {
                this.maxCapacity = parseFloat(bybSettings.maxCapacity) || 10;
                this.capacityType = bybSettings.capacityType || 'items';
            }
            this.loadProducts();
            this.bindEvents();
            this.loadBoxContents();
            this.toggleSidebar();
        },

        bindEvents() {
            const filterWrap = document.querySelectorAll('.byb-radio-group');
            const searchInput = document.getElementById('byb-search');
            const addToCartBtn = document.getElementById('byb-add-to-cart');
            const clearBoxBtn = document.getElementById('byb-clear-box');

            if (filterWrap.length) {

                filterWrap.forEach((el) => {
                    el.addEventListener('change', () => {
                        this.currentPage = 1;
                        this.loadProducts();
                    });
                })
            }

            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('keyup', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.currentPage = 1;
                        this.loadProducts();
                    }, 500);
                });
            }

            // Event delegation for dynamic elements
            document.addEventListener('click', (e) => {

                let initialtext = ''
                let currentBtn;

                // Add to box button
                if (e.target.closest('.byb-btn-add')) {
                    e.preventDefault();
                    const btn = e.target.closest('.byb-btn-add');

                    const productId = parseInt(btn.dataset.productId);
                    const product = this.products.find(p => p.id === productId);

                    btn.innerHTML = `<div class="byb-loader add"></div>`;

                    if (product && product.type === 'variable' && product.variations.length > 0) {
                        this.showVariationSelector(product, btn);
                    } else {
                        this.addToBox(productId, 0);
                    }
                }

                // Remove from box button
                if (e.target.closest('.byb-box-item-remove')) {
                    e.preventDefault();
                    const btn = e.target.closest('.byb-box-item-remove');
                    const itemKey = btn.dataset.itemKey;

                    btn.innerHTML = `<div class="byb-loader-spinner"></div>`;

                    this.removeFromBox(itemKey);
                }

                // Close modal
                if (e.target.matches('.byb-modal-close, .byb-modal-overlay')) {
                    e.preventDefault();

                    this.closeVariationSelector();
                }

                // Variation option selection
                if (e.target.closest('.byb-variation-option')) {
                    e.preventDefault();
                    const option = e.target.closest('.byb-variation-option');
                    const productId = parseInt(option.dataset.productId);
                    const variationId = parseInt(option.dataset.variationId);
                    this.addToBox(productId, variationId);
                    this.closeVariationSelector();
                }

                // Pagination clicks
                if (e.target.closest('.byb-pagination-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.byb-pagination-btn');
                    const page = parseInt(btn.dataset.page);
                    const searchEl = document.querySelector('.byb-header');

                    if (page && page !== this.currentPage && page >= 1 && page <= this.totalPages) {
                        this.currentPage = page;
                        this.loadProducts();
                        const rectTop = searchEl.getBoundingClientRect().top + window.scrollY;
                        window.scrollTo({ top: rectTop, behavior: 'smooth' });
                    }
                }
            });

            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', () => this.addBoxToCart());
            }

            if (clearBoxBtn) {
                clearBoxBtn.addEventListener('click', () => this.clearBox());
            }
        },

        async loadProducts() {

            const category = document.querySelector('input[name="byb-category-filter"]:checked')?.value || '';
            const sort = document.querySelector('input[name="byb-sort"]:checked')?.value || '';
            const search = document.getElementById('byb-search')?.value || '';

            const productsGrid = document.getElementById('byb-products-grid');
            if (!productsGrid) return;

            this.showSkeletonLoader();

            try {
                const formData = new FormData();
                formData.append('action', 'byb_get_products');
                formData.append('nonce', byb_ajax.nonce);
                formData.append('category', category);
                formData.append('sort', sort);
                formData.append('search', search);
                formData.append('page', this.currentPage);
                formData.append('per_page', this.perPage);

                const response = await fetch(byb_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.products = data.data.products;
                    this.totalPages = data.data.pagination.total_pages;
                    this.renderProducts();
                    this.renderPagination(data.data.pagination);
                } else {
                    productsGrid.innerHTML = '<div class="byb-loading">No products found</div>';
                }
            } catch (error) {
                console.error('Error loading products:', error);
                productsGrid.innerHTML = '<div class="byb-loading">Error loading products</div>';
            }
        },

        renderProducts() {
            const grid = document.getElementById('byb-products-grid');
            if (!grid) return;

            grid.innerHTML = '';

            if (this.products.length === 0) {
                grid.innerHTML = '<div class="byb-loading">No products available</div>';
                return;
            }

            this.products.forEach(product => {
                const inBox = this.box.find(item => item.product_id === product.id);
                const cardClass = inBox ? 'byb-product-card in-box' : 'byb-product-card';
                const buttonText = product.type === 'variable' ? 'Select Options' : (inBox ? 'Added ✓' : '+ Add to Box');

                const weightDisplay = this.capacityType === 'weight'
                    ? `<div class="byb-product-weight">Weight: ${product.weight}kg</div>`
                    : '';

                const card = document.createElement('div');
                card.className = cardClass;
                card.dataset.productId = product.id;
                card.innerHTML = `
                    <img src="${product.image}" alt="${product.title}" class="byb-product-image">
                    <div class="byb-product-info">
                        <h4 class="byb-product-title">${product.title}</h4>
                        <div class="byb-product-price">${product.price_html}</div>
                        ${weightDisplay}
                        <div class="byb-product-actions">
                            <button class="byb-btn byb-btn-add" data-product-id="${product.id}">
                                ${buttonText}
                            </button>
                        </div>
                    </div>
                `;

                grid.appendChild(card);
            });
        },

        renderPagination(pagination) {
            let paginationContainer = document.getElementById('byb-pagination');

            if (!paginationContainer) {
                const productsGrid = document.getElementById('byb-products-grid');
                if (!productsGrid) return;

                paginationContainer = document.createElement('div');
                paginationContainer.id = 'byb-pagination';
                paginationContainer.className = 'byb-pagination';
                productsGrid.parentNode.insertBefore(paginationContainer, productsGrid.nextSibling);
            }

            if (pagination.total_pages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="byb-pagination-wrapper">';

            // Previous button
            if (pagination.has_prev) {
                paginationHTML += `<button class="byb-pagination-btn byb-pagination-prev" data-page="${pagination.current_page - 1}">‹ Previous</button>`;
            }

            // Page numbers
            paginationHTML += '<div class="byb-pagination-numbers">';

            const maxVisible = 5;
            let startPage = Math.max(1, pagination.current_page - Math.floor(maxVisible / 2));
            let endPage = Math.min(pagination.total_pages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            // First page
            if (startPage > 1) {
                paginationHTML += `<button class="byb-pagination-btn byb-pagination-number" data-page="1">1</button>`;
                if (startPage > 2) {
                    paginationHTML += `<span class="byb-pagination-ellipsis">...</span>`;
                }
            }

            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === pagination.current_page ? ' byb-pagination-active' : '';
                paginationHTML += `<button class="byb-pagination-btn byb-pagination-number${activeClass}" data-page="${i}">${i}</button>`;
            }

            // Last page
            if (endPage < pagination.total_pages) {
                if (endPage < pagination.total_pages - 1) {
                    paginationHTML += `<span class="byb-pagination-ellipsis">...</span>`;
                }
                paginationHTML += `<button class="byb-pagination-btn byb-pagination-number" data-page="${pagination.total_pages}">${pagination.total_pages}</button>`;
            }

            paginationHTML += '</div>';

            // Next button
            if (pagination.has_next) {
                paginationHTML += `<button class="byb-pagination-btn byb-pagination-next" data-page="${pagination.current_page + 1}">Next ›</button>`;
            }

            paginationHTML += '</div>';

            // Results info
            const startItem = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const endItem = Math.min(pagination.current_page * pagination.per_page, pagination.total_products);
            paginationHTML += `<div class="byb-pagination-info">Showing ${startItem}-${endItem} of ${pagination.total_products} products</div>`;

            paginationContainer.innerHTML = paginationHTML;
        },

        showVariationSelector(product, btn) {
            if (!product.variations || product.variations.length === 0) {
                this.showNotice('No variations available for this product', 'error');
                return;
            }

            let variationsHtml = '';
            product.variations.forEach(variation => {
                variationsHtml += `
                    <div class="byb-variation-option" data-product-id="${product.id}" data-variation-id="${variation.variation_id}">
                        <img src="${variation.image}" alt="${variation.description}" class="byb-variation-image">
                        <div class="byb-variation-details">
                            <div class="byb-variation-name">${variation.description}</div>
                            <div class="byb-variation-price">${variation.price_html}</div>
                        </div>
                        <button class="byb-btn byb-btn-add">Add to Box</button>
                    </div>
                `;
            });

            const modalHtml = `
                <div class="byb-modal-overlay"></div>
                <div class="byb-modal">
                    <div class="byb-modal-header">
                        <h3>${product.title} - Select Option</h3>
                        <button class="byb-modal-close">&times;</button>
                    </div>
                    <div class="byb-modal-body">
                        ${variationsHtml}
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            document.body.classList.add('byb-modal-open');
        },

        closeVariationSelector() {
            document.querySelectorAll('.byb-modal-overlay, .byb-modal').forEach(el => el.remove());
            document.body.classList.remove('byb-modal-open');
        },

        async addToBox(productId, variationId) {
            try {
                const formData = new FormData();
                formData.append('action', 'byb_add_to_box');
                formData.append('nonce', byb_ajax.nonce);
                formData.append('product_id', productId);
                formData.append('variation_id', variationId || 0);
                formData.append('quantity', 1);

                const response = await fetch(byb_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.loadBoxContents();
                    this.showNotice('Product added to box', 'success');
                } else {
                    this.showNotice(data.data?.message || 'Error adding product', 'error');
                }
            } catch (error) {
                console.error('Error adding to box:', error);
                this.showNotice('Error adding product', 'error');
            }
        },

        async removeFromBox(itemKey) {
            try {
                const formData = new FormData();
                formData.append('action', 'byb_remove_from_box');
                formData.append('nonce', byb_ajax.nonce);
                formData.append('item_key', itemKey);

                const response = await fetch(byb_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.loadBoxContents();
                    this.showNotice('Product removed from box', 'success');
                }
            } catch (error) {
                console.error('Error removing from box:', error);
            }
        },

        async loadBoxContents() {
            try {
                const formData = new FormData();
                formData.append('action', 'byb_get_box_contents');
                formData.append('nonce', byb_ajax.nonce);

                const response = await fetch(byb_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.box = data.data.items;
                    this.updateBoxDisplay(data.data);
                    this.renderProducts();
                }
            } catch (error) {
                console.error('Error loading box contents:', error);
            }
        },

        updateBoxDisplay(data) {
            const itemsContainer = document.getElementById('byb-box-items');
            if (!itemsContainer) return;

            itemsContainer.innerHTML = '';

            const addToCartBtn = document.getElementById('byb-add-to-cart');

            if (data.items.length === 0) {
                itemsContainer.innerHTML = '<p class="byb-empty-message">Your box is empty. Start adding products!</p>';
                if (addToCartBtn) addToCartBtn.disabled = true;
            } else {
                data.items.forEach(item => {
                    const itemEl = document.createElement('div');
                    itemEl.className = 'byb-box-item';
                    itemEl.innerHTML = `
                        <img src="${item.image || ''}" alt="${item.title}" class="byb-box-item-image">
                        <div class="byb-box-item-details">
                            <div class="byb-box-item-title">${item.title}</div>
                            <div class="byb-box-item-meta">
                                Qty: ${item.quantity} × ${item.price_html}
                            </div>
                        </div>
                        <span class="byb-box-item-remove" data-item-key="${item.id}">&Cross;</span>
                    `;
                    itemsContainer.appendChild(itemEl);
                });
                if (addToCartBtn) addToCartBtn.disabled = false;
            }

            const currentCapacity = this.capacityType === 'weight' ? data.total_weight : data.item_count;
            const capacityPercent = Math.min((currentCapacity / this.maxCapacity) * 100, 100);

            const currentCapacityEl = document.querySelectorAll('.byb-current-capacity');
            const totalPriceEl = document.querySelectorAll('.byb-total-price');

            if (currentCapacityEl.length) {
                currentCapacityEl.forEach(el => el.textContent = currentCapacity);
            }

            if (totalPriceEl.length) {
                totalPriceEl.forEach(el => el.innerHTML = data.total_price_html);
            }

            const capacityFill = document.querySelectorAll('.byb-capacity-fill');
            if (capacityFill.length) {

                capacityFill.forEach((el) => {
                    el.style.width = capacityPercent + '%';
                    el.classList.remove('byb-warning', 'byb-full');

                    if (capacityPercent >= 100) {
                        el.classList.add('byb-full');
                    } else if (capacityPercent >= 80) {
                        el.classList.add('byb-warning');
                    }
                })
            }

            if (currentCapacity > this.maxCapacity) {
                if (addToCartBtn) addToCartBtn.disabled = true;
                this.showNotice('Box capacity exceeded!', 'error');
            }
        },

        async addBoxToCart() {
            if (this.box.length === 0) {
                this.showNotice('Your box is empty', 'error');
                return;
            }

            const addToCartBtn = document.getElementById('byb-add-to-cart');
            if (!addToCartBtn) return;

            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Adding...';

            try {
                const formData = new FormData();
                formData.append('action', 'byb_add_box_to_cart');
                formData.append('nonce', byb_ajax.nonce);

                const response = await fetch(byb_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotice(data.data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.data.cart_url;
                    }, 1000);
                } else {
                    this.showNotice(data.data?.message || 'Error adding to cart', 'error');
                    addToCartBtn.disabled = false;
                    addToCartBtn.textContent = 'Add Box to Cart';
                }
            } catch (error) {
                console.error('Error adding box to cart:', error);
                this.showNotice('Error adding box to cart', 'error');
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = 'Add Box to Cart';
            }
        },

        clearBox() {
            if (!this.box.length) {
                alert('Your box is empty');
                return;
            }

            if (!confirm('Are you sure you want to clear your box?')) {
                return;
            }


            this.box.forEach(item => {
                this.removeFromBox(item.id);
            });
        },

        showNotice(message, type) {
            const noticeClass = type === 'success' ? 'byb-notice-success' : 'byb-notice-error';
            const notice = document.createElement('div');
            notice.className = `byb-notice ${noticeClass}`;
            notice.textContent = message;

            document.body.appendChild(notice);

            setTimeout(() => {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.3s';
                setTimeout(() => notice.remove(), 300);
            }, 3000);
        },

        showSkeletonLoader() {
            const productsGrid = document.getElementById('byb-products-grid');
            if (!productsGrid) return;

            productsGrid.innerHTML = '';

            // Create 9 skeleton cards (3x3 grid)
            for (let i = 0; i < 9; i++) {
                const skeleton = document.createElement('div');
                skeleton.className = 'byb-skeleton-card';
                skeleton.innerHTML = `
                    <div class="byb-skeleton-image"></div>
                    <div class="byb-skeleton-content">
                        <div class="byb-skeleton-title"></div>
                        <div class="byb-skeleton-price"></div>
                        <div class="byb-skeleton-button"></div>
                    </div>
                `;
                productsGrid.appendChild(skeleton);
            }
        },
        toggleSidebar() {
            const toggleBtn = document.getElementById('byb_slider_toggle');
            const sidebar = document.querySelector('.byb-sidebar');
            const closeBtn = document.querySelector('.byb-close-btn');

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                toggleBtn.classList.toggle('inactive');
            });

            closeBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                toggleBtn.classList.toggle('inactive');
            })
        }

    };

    if (document.querySelector('.byb-container')) {
        BYB.init();
    }
});

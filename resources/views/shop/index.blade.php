<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Loja Virtual</title>
    <meta name="description" content="E-commerce Laravel com integração WooCommerce - Demonstração técnica">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="/funcoes-produtos.js"></script>

    <style>
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .product-card img {
            transition: transform 0.3s ease;
        }
        .product-card:hover img {
            transform: scale(1.05);
        }
        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 8px 8px 0 0;
        }
        .product-image-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }
        .sale-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .featured-badge {
            background: linear-gradient(45deg, #f59e0b, #ef4444);
        }
        .price-cut {
            position: relative;
        }
        .price-cut::after {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 2px;
            background: #ef4444;
            transform: rotate(-5deg);
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .api-status {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
        }
        .api-connected {
            background: #10b981;
            color: white;
        }
        .api-error {
            background: #ef4444;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- API Status Indicator -->
    <div id="api-status" class="api-status" style="display: none;">
        <i class="fas fa-circle mr-2"></i>
        <span id="api-status-text">Conectando...</span>
    </div>

    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-shopping-bag text-2xl text-indigo-600"></i>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">{{ config('app.name') }}</h1>
                        <p class="text-xs text-gray-500">Laravel E-commerce + WooCommerce</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 text-gray-600 hover:text-indigo-600" onclick="openSearchModal()">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="p-2 text-gray-600 hover:text-indigo-600">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button class="p-2 text-gray-600 hover:text-indigo-600 relative" onclick="openCartModal()">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </button>
                    <a href="/api/docs" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-600 rounded-md text-sm hover:bg-blue-200">
                        <i class="fas fa-code mr-1"></i> API Docs
                    </a>
                </div>
            </div>
            <nav class="mt-4">
                <ul class="flex space-x-6 overflow-x-auto pb-2">
                    <li><a href="#" class="filter-btn text-indigo-600 font-medium" data-filter="all">Todos</a></li>
                    <li><a href="#" class="filter-btn text-gray-600 hover:text-indigo-600" data-filter="electronics">Eletrônicos</a></li>
                    <li><a href="#" class="filter-btn text-gray-600 hover:text-indigo-600" data-filter="clothing">Roupas</a></li>
                    <li><a href="#" class="filter-btn text-gray-600 hover:text-indigo-600" data-filter="books">Livros</a></li>
                    <li><a href="#" class="filter-btn text-gray-600 hover:text-indigo-600" data-filter="featured">Destaques</a></li>
                    <li><a href="#" class="filter-btn text-gray-600 hover:text-indigo-600" data-filter="sale">Promoções</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Nossos Produtos</h2>
                <p class="text-gray-600">Conectado à API Laravel em tempo real</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Mostrar:</span>
                    <select id="per-page-select" class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="12">12 por página</option>
                        <option value="24">24 por página</option>
                        <option value="48">48 por página</option>
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Ordenar por:</span>
                    <select id="sort-select" class="border rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="name_asc">Nome A-Z</option>
                        <option value="name_desc">Nome Z-A</option>
                        <option value="price_asc">Menor preço</option>
                        <option value="price_desc">Maior preço</option>
                        <option value="newest">Mais recentes</option>
                        <option value="featured">Destaques</option>
                    </select>
                </div>
                <button onclick="refreshProducts()" class="px-3 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 text-sm">
                    <i class="fas fa-refresh mr-1"></i> Atualizar
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="flex justify-center items-center py-20">
            <div class="text-center">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Carregando produtos da API...</p>
            </div>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden bg-red-50 border border-red-200 rounded-lg p-8 text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
            <h3 class="text-lg font-semibold text-red-800 mb-2">Erro ao carregar produtos</h3>
            <p class="text-red-600 mb-4">Não foi possível conectar com a API Laravel.</p>
            <button onclick="refreshProducts()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                <i class="fas fa-retry mr-2"></i> Tentar novamente
            </button>
        </div>

        <!-- Products Container -->
        <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 hidden">
            <!-- Products will be inserted here by JavaScript -->
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="mt-12 flex justify-center hidden">
            <!-- Pagination will be inserted here -->
        </div>

        <!-- Product Stats -->
        <div class="mt-8 bg-white rounded-lg p-6 shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Estatísticas da Loja</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600" id="total-products">-</div>
                    <div class="text-sm text-gray-600">Total de Produtos</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600" id="featured-products">-</div>
                    <div class="text-sm text-gray-600">Em Destaque</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600" id="sale-products">-</div>
                    <div class="text-sm text-gray-600">Em Promoção</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600" id="avg-price">-</div>
                    <div class="text-sm text-gray-600">Preço Médio</div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-gray-400 mb-2">Projeto de demonstração técnica</p>
                    <p class="text-gray-400">Laravel + WooCommerce Integration</p>
                    <div class="mt-4 space-y-2">
                        <div class="text-sm">
                            <i class="fas fa-code text-blue-400 mr-2"></i>
                            <span class="text-gray-400">Clean Architecture</span>
                        </div>
                        <div class="text-sm">
                            <i class="fas fa-layer-group text-green-400 mr-2"></i>
                            <span class="text-gray-400">Repository Pattern</span>
                        </div>
                        <div class="text-sm">
                            <i class="fas fa-cogs text-purple-400 mr-2"></i>
                            <span class="text-gray-400">Service Layer</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tecnologias</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">PHP 8.1+</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Laravel 10.x</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">SQLite Database</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">WooCommerce API</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">API Endpoints</h3>
                    <ul class="space-y-2">
                        <li><a href="/api/products" target="_blank" class="text-gray-400 hover:text-white">GET /api/products</a></li>
                        <li><a href="/api/docs" target="_blank" class="text-gray-400 hover:text-white">API Documentation</a></li>
                        <li><a href="/api/health" target="_blank" class="text-gray-400 hover:text-white">Health Check</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Demonstração</h3>
                    <p class="text-gray-400 mb-2">Este frontend conecta em tempo real com a API Laravel.</p>
                    <div class="flex mt-4">
                        <a href="/api/docs" target="_blank" class="bg-indigo-600 px-4 py-2 rounded-l text-white hover:bg-indigo-700">
                            <i class="fas fa-code mr-1"></i> API
                        </a>
                        <button onclick="runApiTest()" class="bg-green-600 px-4 py-2 rounded-r text-white hover:bg-green-700">
                            <i class="fas fa-play mr-1"></i> Teste
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 {{ config('app.name') }}. Projeto de demonstração técnica.</p>
                <p class="text-sm mt-2">
                    <i class="fas fa-server mr-1"></i> Servidor: {{ request()->getHost() }}
                    <i class="fas fa-database ml-4 mr-1"></i> Banco: SQLite
                    <i class="fas fa-clock ml-4 mr-1"></i> Tempo real
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Global variables
        let currentProducts = [];
        let currentFilter = 'all';
        let currentSort = 'name_asc';
        let currentPage = 1;
        let perPage = 12;
        let cart = [];

        // API Configuration
        const API_BASE = '{{ url('/api') }}';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            setupEventListeners();
            updateApiStatus('connecting');
        });

        // Setup event listeners
        function setupEventListeners() {
            // Filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    setFilter(btn.dataset.filter);
                    setActiveFilter(btn);
                });
            });

            // Sort select
            document.getElementById('sort-select').addEventListener('change', (e) => {
                currentSort = e.target.value;
                loadProducts();
            });

            // Per page select
            document.getElementById('per-page-select').addEventListener('change', (e) => {
                perPage = parseInt(e.target.value);
                currentPage = 1;
                loadProducts();
            });
        }

        // Update API status indicator
        function updateApiStatus(status, message = '') {
            const statusEl = document.getElementById('api-status');
            const textEl = document.getElementById('api-status-text');

            statusEl.style.display = 'block';
            statusEl.className = 'api-status';

            switch(status) {
                case 'connecting':
                    statusEl.classList.add('api-error');
                    textEl.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Conectando API...';
                    break;
                case 'connected':
                    statusEl.classList.add('api-connected');
                    textEl.innerHTML = '<i class="fas fa-check-circle mr-2"></i> API Conectada';
                    setTimeout(() => statusEl.style.display = 'none', 3000);
                    break;
                case 'error':
                    statusEl.classList.add('api-error');
                    textEl.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> Erro na API';
                    break;
            }
        }

        // Load products from API
        async function loadProducts() {
            showLoadingState();

            try {
                let url = `${API_BASE}/products?page=${currentPage}&per_page=${perPage}`;

                // Add filters
                if (currentFilter !== 'all') {
                    switch(currentFilter) {
                        case 'featured':
                            url += '&featured=1';
                            break;
                        case 'sale':
                            url += '&on_sale=1';
                            break;
                        case 'electronics':
                            url += '&search=smartphone,notebook,laptop';
                            break;
                        case 'clothing':
                            url += '&search=camiseta,calça,roupa';
                            break;
                        case 'books':
                            url += '&search=livro,book,clean code,laravel';
                            break;
                    }
                }

                // Add sorting
                if (currentSort !== 'name_asc') {
                    url += `&sort=${currentSort}`;
                }

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                currentProducts = data.data;

                renderProducts(currentProducts);
                renderPagination(data.meta);
                updateStats(data);
                updateApiStatus('connected');

            } catch (error) {
                console.error('Erro ao carregar produtos:', error);
                showErrorState();
                updateApiStatus('error', error.message);
            }
        }

        // Show loading state
        function showLoadingState() {
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('error-state').classList.add('hidden');
            document.getElementById('products-container').classList.add('hidden');
            document.getElementById('pagination-container').classList.add('hidden');
        }

        // Show error state
        function showErrorState() {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.remove('hidden');
            document.getElementById('products-container').classList.add('hidden');
            document.getElementById('pagination-container').classList.add('hidden');
        }

        // Show products
        function showProductsState() {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.add('hidden');
            document.getElementById('products-container').classList.remove('hidden');
            document.getElementById('pagination-container').classList.remove('hidden');
        }

        // Get product image URL
        function getProductImage(product) {
            if (product.images && product.images.length > 0) {
                return product.images[0].src;
            }
            return '/img/teste.jpg'; // Imagem padrão caso não tenha
        }

        // Get product alt text
        function getProductImageAlt(product) {
            if (product.images && product.images.length > 0 && product.images[0].alt) {
                return product.images[0].alt;
            }
            return product.name;
        }

        // Parse product meta data
        function parseMetaData(metaData) {
            if (!metaData) return null;
            try {
                return typeof metaData === 'string' ? JSON.parse(metaData) : metaData;
            } catch (e) {
                return null;
            }
        }

        // Render products
        function renderProducts(products) {
            const container = document.getElementById('products-container');
            container.innerHTML = '';

            if (products.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhum produto encontrado</h3>
                        <p class="text-gray-500">Tente ajustar os filtros ou fazer uma nova busca.</p>
                    </div>
                `;
                showProductsState();
                return;
            }

            products.forEach(product => {
                const metaData = parseMetaData(product.meta_data);
                const hasSale = product.price && product.price.sale !== null;
                const isFeatured = product.featured;
                const salePercentage = hasSale ?
                    Math.round((1 - product.price.effective / product.price.regular) * 100) : 0;

                const productCard = document.createElement('div');
                productCard.className = 'bg-white rounded-lg overflow-hidden shadow-md product-card transition-all duration-300 relative';

                productCard.innerHTML = `
                    ${isFeatured ? `
                        <div class="featured-badge absolute top-2 left-2 text-white text-xs font-bold px-2 py-1 rounded-full z-10">
                            <i class="fas fa-star mr-1"></i> Destaque
                        </div>
                    ` : ''}

                    ${hasSale ? `
                        <div class="sale-badge absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full z-10">
                            ${salePercentage}% OFF
                        </div>
                    ` : ''}

                    <div class="product-image-container bg-gray-100 h-48 flex items-center justify-center relative cursor-pointer"
                         onclick="openImageModal('${getProductImage(product)}', '${getProductImageAlt(product)}')">
                        <div class="product-image-loading">
                            <div class="loading-spinner"></div>
                        </div>
                        <img src="${getProductImage(product)}"
                             alt="${getProductImageAlt(product)}"
                             class="w-full h-full object-cover absolute inset-0"
                             onload="this.previousElementSibling.style.display='none'"
                             onerror="this.style.display='none'; this.previousElementSibling.innerHTML='<i class=\\'fas fa-image text-4xl text-gray-400\\'></i>';">
                        <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-search-plus text-white text-xl opacity-0 hover:opacity-100 transition-opacity duration-300"></i>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-gray-800 mb-1 line-clamp-2">${product.name}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">${product.description || 'Sem descrição'}</p>

                        ${metaData ? renderMetaData(metaData) : ''}

                        <div class="flex justify-between items-center mt-4">
                            <div>
                                ${hasSale ? `
                                    <span class="price-cut text-gray-400 text-sm mr-2">${product.price.formatted.regular}</span>
                                ` : ''}
                                <span class="font-bold text-lg text-indigo-600">
                                    ${product.price ? product.price.formatted.effective : 'Consulte'}
                                </span>
                            </div>
                            <button onclick="addToCart(${product.id})" class="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>

                        <div class="mt-3 text-center">
                            <button onclick="viewProduct(${product.id})" class="w-full bg-gray-100 text-gray-700 py-2 rounded-md hover:bg-gray-200 transition-colors text-sm">
                                <i class="fas fa-eye mr-1"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                `;

                container.appendChild(productCard);
            });

            showProductsState();
        }

        // Render meta data attributes
        function renderMetaData(metaData) {
            if (!metaData) return '';

            let html = '<div class="mb-3"><div class="flex flex-wrap gap-1">';

            // Sizes
            if (metaData.sizes && Array.isArray(metaData.sizes)) {
                metaData.sizes.forEach(size => {
                    html += `<span class="text-xs bg-gray-100 px-2 py-1 rounded">${size}</span>`;
                });
            }

            // Colors
            if (metaData.colors && Array.isArray(metaData.colors)) {
                metaData.colors.forEach(color => {
                    html += `<span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">${color}</span>`;
                });
            }

            // Brand
            if (metaData.brand) {
                html += `<span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">${metaData.brand}</span>`;
            }

            // Processor
            if (metaData.processor) {
                html += `<span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">${metaData.processor}</span>`;
            }

            // Author (for books)
            if (metaData.author) {
                html += `<span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">${metaData.author}</span>`;
            }

            html += '</div></div>';
            return html;
        }

        // Render pagination
        function renderPagination(meta) {
            const container = document.getElementById('pagination-container');

            if (meta.last_page <= 1) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');

            let html = '<nav class="flex items-center space-x-2">';

            // Previous button
            if (meta.current_page > 1) {
                html += `
                    <button onclick="changePage(${meta.current_page - 1})"
                            class="px-3 py-1 rounded border text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                `;
            }

            // Page numbers
            for (let i = Math.max(1, meta.current_page - 2); i <= Math.min(meta.last_page, meta.current_page + 2); i++) {
                if (i === meta.current_page) {
                    html += `<button class="px-3 py-1 rounded border bg-indigo-600 text-white">${i}</button>`;
                } else {
                    html += `<button onclick="changePage(${i})" class="px-3 py-1 rounded border text-gray-600 hover:bg-gray-100">${i}</button>`;
                }
            }

            // Next button
            if (meta.current_page < meta.last_page) {
                html += `
                    <button onclick="changePage(${meta.current_page + 1})"
                            class="px-3 py-1 rounded border text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;
            }

            html += '</nav>';
            container.innerHTML = html;
        }

        // Update statistics
        function updateStats(data) {
            const products = data.data;
            const featuredCount = products.filter(p => p.featured).length;
            const saleCount = products.filter(p => p.price && p.price.sale !== null).length;
            const avgPrice = products.length > 0 ?
                products.reduce((sum, p) => sum + (p.price ? p.price.effective : 0), 0) / products.length : 0;

            document.getElementById('total-products').textContent = data.meta.total;
            document.getElementById('featured-products').textContent = featuredCount;
            document.getElementById('sale-products').textContent = saleCount;
            document.getElementById('avg-price').textContent = `R$ ${avgPrice.toFixed(0)}`;
        }

        // Set active filter
        function setActiveFilter(activeBtn) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('text-indigo-600', 'font-medium');
                btn.classList.add('text-gray-600');
            });
            activeBtn.classList.remove('text-gray-600');
            activeBtn.classList.add('text-indigo-600', 'font-medium');
        }

        // Set filter
        function setFilter(filter) {
            currentFilter = filter;
            currentPage = 1;
            loadProducts();
        }

        // Change page
        function changePage(page) {
            currentPage = page;
            loadProducts();
        }

        // Refresh products
        function refreshProducts() {
            loadProducts();
        }

        // Add to cart
        function addToCart(productId) {
            const product = currentProducts.find(p => p.id === productId);
            if (product) {
                cart.push(product);
                updateCartCount();
                showNotification(`${product.name} adicionado ao carrinho!`, 'success');
            }
        }

        // Update cart count
        function updateCartCount() {
            document.getElementById('cart-count').textContent = cart.length;
        }

        // View product details
        function viewProduct(productId) {
            const product = currentProducts.find(p => p.id === productId);
            if (product) {
                alert(`Produto: ${product.name}\nPreço: ${product.price ? product.price.formatted.effective : 'Consulte'}\nSKU: ${product.sku}\n\nEm um projeto real, isso abriria uma página de detalhes.`);
            }
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 p-4 rounded-md text-white ${type === 'success' ? 'bg-green-500' : 'bg-blue-500'}`;
            notification.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check' : 'fa-info'} mr-2"></i>
                ${message}
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Run API test
        async function runApiTest() {
            showNotification('Executando teste da API...', 'info');

            try {
                const response = await fetch(`${API_BASE}/health`);
                const data = await response.json();

                showNotification(`API Test: ${data.status} - ${data.database}`, 'success');
            } catch (error) {
                showNotification('Erro no teste da API', 'error');
            }
        }

        // Open search modal (placeholder)
        function openSearchModal() {
            const search = prompt('Digite o termo de busca:');
            if (search && search.trim()) {
                // In a real implementation, this would use the search API endpoint
                showNotification(`Busca por "${search}" (funcionalidade em desenvolvimento)`, 'info');
            }
        }

        // Open cart modal (placeholder)
        function openCartModal() {
            if (cart.length === 0) {
                showNotification('Carrinho vazio', 'info');
                return;
            }

            const cartItems = cart.map(item => `- ${item.name}`).join('\n');
            alert(`Itens no carrinho (${cart.length}):\n\n${cartItems}\n\nEm um projeto real, isso abriria o modal do carrinho.`);
        }
    </script>
</body>
</html>

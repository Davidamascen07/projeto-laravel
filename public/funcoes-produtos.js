/**
 * Funções auxiliares para produtos
 */

// Função para zoom da imagem do produto
function openImageModal(imageSrc, imageAlt) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
    modal.onclick = () => modal.remove();

    modal.innerHTML = `
        <div class="relative max-w-4xl max-h-full p-4">
            <button onclick="this.parentElement.parentElement.remove()"
                    class="absolute top-2 right-2 text-white text-2xl hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
            <img src="${imageSrc}" alt="${imageAlt}" class="max-w-full max-h-full object-contain">
        </div>
    `;

    document.body.appendChild(modal);
}

// Função para lazy loading das imagens
function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Função para otimizar carregamento de imagens
function optimizeImageLoading() {
    const images = document.querySelectorAll('.product-card img');

    images.forEach(img => {
        img.addEventListener('load', function() {
            this.style.opacity = '1';
            const loader = this.parentElement.querySelector('.product-image-loading');
            if (loader) loader.style.display = 'none';
        });

        img.addEventListener('error', function() {
            const loader = this.parentElement.querySelector('.product-image-loading');
            if (loader) {
                loader.innerHTML = '<i class="fas fa-image text-4xl text-gray-400"></i>';
            }
        });
    });
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    setupLazyLoading();
    optimizeImageLoading();
});

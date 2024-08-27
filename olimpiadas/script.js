document.addEventListener('DOMContentLoaded', () => {
    const addToCartLinks = document.querySelectorAll('.products a');

    addToCartLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const productId = this.getAttribute('href').split('=')[1];
            addToCart(productId);
        });
    });

    const removeFromCartLinks = document.querySelectorAll('.cart a');

    removeFromCartLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const productId = this.getAttribute('href').split('=')[1];
            removeFromCart(productId);
        });
    });

    function addToCart(productId) {
        console.log(`Producto ${productId} añadido al carrito`);

        alert('Producto añadido al carrito');
    }

    function removeFromCart(productId) {
        console.log(`Producto ${productId} eliminado del carrito`);

        alert('Producto eliminado del carrito');
    }

    const buttons = document.querySelectorAll('.login-register button, .products a');

    buttons.forEach(button => {
        button.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#e60000';
        });

        button.addEventListener('mouseout', function() {
            this.style.backgroundColor = '#0056b3';
        });
    });

    const cartIcon = document.querySelector('.cart-icon');
    const cartDetails = document.querySelector('.cart');

    if (cartIcon && cartDetails) {
        cartIcon.addEventListener('click', () => {
            cartDetails.classList.toggle('visible');
        });
    }

    const buyCartButton = document.querySelector('.buy-cart');
    if (buyCartButton) {
        buyCartButton.addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('¿Está seguro de que desea comprar estos artículos?')) {
                window.location.href = 'checkout.php';
            }
        });
    }
});

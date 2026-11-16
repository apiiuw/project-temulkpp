import './bootstrap';
import { initFlowbite } from 'flowbite';
import jsQR from 'jsqr';

window.jsQR = jsQR;

// Initialize Flowbite components
document.addEventListener('DOMContentLoaded', () => {
    initFlowbite();
});

// Also initialize on dynamic content loads
const observer = new MutationObserver(() => {
    initFlowbite();
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

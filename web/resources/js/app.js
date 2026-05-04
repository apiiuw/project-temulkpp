import './bootstrap';
import { initFlowbite } from 'flowbite';

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

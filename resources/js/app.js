import './bootstrap';
import './components/combobox';
import './components/character-counter';
import './components/toast';
import './components/favorites';
import './components/saved-searches';
import listingGallery from './components/listing-gallery';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.data('listingGallery', listingGallery);
Alpine.start();

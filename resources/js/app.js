import './bootstrap';

import Alpine from 'alpinejs';
import eoiEntryLocation from './eoi-entry-location';
import agreementProgress from './agreement-progress';

window.Alpine = Alpine;
Alpine.data('eoiEntryLocation', eoiEntryLocation);
Alpine.data('agreementProgress', agreementProgress);

Alpine.start();
